<?php

namespace ArgentCrusade\Stronghold\Contracts;

use ArgentCrusade\Stronghold\OneTimeToken;

interface OneTimeTokensRepositoryInterface
{
    /**
     * Create new one time token.
     *
     * @param array $attributes
     *
     * @return OneTimeToken
     */
    public function create(array $attributes);

    /**
     * Get the first unused token for the given user & optional operation.
     *
     * @param int|string $userId
     * @param string|null $operation
     *
     * @return OneTimeToken|null
     */
    public function firstUnusedFor($userId, string $operation = null);

    /**
     * Invalidate unused tokens for the given user & optional operation.
     *
     * @param int|string $userId
     * @param string|null $operation
     *
     * @return mixed
     */
    public function deleteUnusedFor($userId, string $operation = null);
}
