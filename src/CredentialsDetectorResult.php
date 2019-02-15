<?php

namespace ArgentCrusade\Stronghold;

class CredentialsDetectorResult
{
    const EMAIL = 'email';
    const PHONE = 'phone';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $username;

    /**
     * CredentialsDetectorResult constructor.
     *
     * @param string $type
     * @param string $username
     */
    public function __construct(string $type, string $username)
    {
        $this->type = $type;
        $this->username = $username;
    }

    /**
     * Get the credentials with given password.
     *
     * @param string $password
     * @param string $field
     *
     * @return array
     */
    public function credentials(string $password, string $field = 'password')
    {
        return [
            $this->type => $this->username,
            $field => $password,
        ];
    }
}
