<?php

namespace ArgentCrusade\Stronghold;

use ArgentCrusade\Stronghold\Contracts\OneTimeTokensRepositoryInterface;

class OneTimeTokensRepository implements OneTimeTokensRepositoryInterface
{
    /**
     * Create new one time token.
     *
     * @param array $attributes
     *
     * @return OneTimeToken
     */
    public function create(array $attributes)
    {
        return OneTimeToken::create($attributes);
    }

    /**
     * Get the first unused token for the given user & optional operation.
     *
     * @param int|string  $userId
     * @param string|null $operation
     *
     * @return OneTimeToken|null
     */
    public function firstUnusedFor($userId, string $operation = null)
    {
        return OneTimeToken::unusedFor($userId, $operation)->first();
    }

    /**
     * Invalidate unused tokens for the given user & optional operation.
     *
     * @param int|string  $userId
     * @param string|null $operation
     *
     * @return mixed
     */
    public function deleteUnusedFor($userId, string $operation = null)
    {
        return OneTimeToken::unusedFor($userId, $operation)->delete();
    }
}
