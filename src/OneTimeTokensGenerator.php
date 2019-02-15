<?php

namespace ArgentCrusade\Stronghold;

use ArgentCrusade\Stronghold\Contracts\OneTimeTokensRepositoryInterface;
use ArgentCrusade\Stronghold\Events\OneTimeTokenCreated;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class OneTimeTokensGenerator
{
    /** @var OneTimeTokensRepositoryInterface */
    protected $repository;

    /** @var int */
    protected $min;

    /** @var int */
    protected $max;

    /**
     * OneTimeTokensGenerator constructor.
     *
     * @param OneTimeTokensRepositoryInterface $repository
     * @param int $min
     * @param int $max
     */
    public function __construct(OneTimeTokensRepositoryInterface $repository, int $min = 1000, int $max = 9999)
    {
        $this->repository = $repository;

        $this->using($min, $max);
    }

    /**
     * Refresh min & max values.
     *
     * @param int $min
     * @param int $max
     *
     * @return OneTimeTokensGenerator
     */
    public function using(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    /**
     * Generate one time token for the given user.
     *
     * @param Authenticatable|Model|mixed $user
     * @param string                      $operation
     * @param string                      $payload   = null
     *
     * @return OneTimeToken|null
     */
    public function generate($user, string $operation, string $payload = null)
    {
        $token = $this->findUnusedTokenFor($user, $operation);

        if (!is_null($token)) {
            return $token;
        }

        return $this->createToken($user, $operation, $payload);
    }

    /**
     * Invalidate unused tokens for given user & optional operation.
     *
     * @param Authenticatable|Model|mixed $user
     * @param string|null                 $operation
     *
     * @return int
     */
    public function invalidateUnused($user, string $operation = null)
    {
        return $this->repository->deleteUnusedFor($this->getUserIdentifier($user), $operation);
    }

    /**
     * Create new one time token.
     *
     * @param Authenticatable|Model|mixed $user
     * @param string                      $operation
     * @param string                      $payload   = null
     *
     * @return OneTimeToken
     */
    protected function createToken($user, string $operation, string $payload = null)
    {
        $token = $this->repository->create([
            'user_id' => $this->getUserIdentifier($user),
            'operation' => $operation,
            'identifier' => str_random(32),
            'code' => mt_rand($this->min, $this->max),
            'payload' => $payload,
            'expires_at' => Carbon::now()->addMinutes(15),
            'used_at' => null,
        ]);

        event(new OneTimeTokenCreated($token));

        return $token;
    }

    /**
     * Find unused token for the given user.
     *
     * @param Authenticatable|Model|mixed $user
     * @param string                      $operation
     *
     * @return OneTimeToken|null
     */
    protected function findUnusedTokenFor($user, string $operation)
    {
        return $this->repository->firstUnusedFor($this->getUserIdentifier($user), $operation);
    }

    /**
     * Get user identifier from given argument.
     *
     * @param Authenticatable|Model|mixed $user
     *
     * @return mixed
     */
    protected function getUserIdentifier($user)
    {
        if ($user instanceof Authenticatable) {
            return $user->getAuthIdentifier();
        } elseif ($user instanceof Model) {
            return $user->getKey();
        } elseif (is_scalar($user)) {
            return $user;
        }

        throw new \UnexpectedValueException('User must be an instance of Authenticatable, Model or scalar value.');
    }
}
