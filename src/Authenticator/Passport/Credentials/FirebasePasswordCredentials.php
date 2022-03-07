<?php

namespace Softspring\FirebaseAuthenticationBundle\Authenticator\Passport\Credentials;

use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CredentialsInterface;

class FirebasePasswordCredentials implements CredentialsInterface
{
    private $password;
    private $resolved = false;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        if (null === $this->password) {
            throw new LogicException('The credentials are erased as another listener already verified these credentials.');
        }

        return $this->password;
    }

    /**
     * @internal
     */
    public function markResolved(): void
    {
        $this->resolved = true;
        $this->password = null;
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }
}
