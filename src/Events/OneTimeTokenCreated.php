<?php

namespace ArgentCrusade\Stronghold\Events;

use ArgentCrusade\Stronghold\OneTimeToken;
use Illuminate\Queue\SerializesModels;

class OneTimeTokenCreated
{
    use SerializesModels;

    /** @var OneTimeToken */
    public $token;

    /**
     * OneTimeTokenCreated constructor.
     *
     * @param OneTimeToken $token
     */
    public function __construct(OneTimeToken $token)
    {
        $this->token = $token;
    }
}
