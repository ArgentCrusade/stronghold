<?php

namespace ArgentCrusade\Stronghold;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OneTimeToken
 *
 * @property int $id
 * @property int|string $user_id
 * @property string $operation
 * @property string $identifier
 * @property int $code
 * @property string|null $payload
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $used_at
 * @property-read \Carbon\Carbon $created_at
 * @property-read \Carbon\Carbon $updated_at
 */
class OneTimeToken extends Model
{
    protected $fillable = [
        'user_id', 'operation', 'identifier', 'code',
        'payload', 'expires_at', 'used_at',
    ];

    protected $casts = [
        'code' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Determines whether the given code identified by given identifier matches.
     *
     * @param string $identifier
     * @param int    $code
     *
     * @return bool
     */
    public static function codeMatches(string $identifier, int $code)
    {
        $token = static::identifiedBy($identifier);

        if (is_null($token)) {
            return false;
        }

        return $token->code === $code;
    }

    /**
     * Get the one time token identified by given identifier.
     *
     * @param string $identifier
     *
     * @return OneTimeToken|null
     */
    public static function identifiedBy(string $identifier)
    {
        return static::where('identifier', $identifier)->first();
    }

    /**
     * Get first unused token for the given user ID.
     *
     * @param int|string $userId
     * @param string     $operation = null
     *
     * @return Builder
     */
    public static function unusedFor($userId, string $operation = null)
    {
        return static::where('user_id', $userId)
            ->when(!is_null($operation), function (Builder $query) use ($operation) {
                $query->where('operation', $operation);
            })
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('used_at');
    }
}
