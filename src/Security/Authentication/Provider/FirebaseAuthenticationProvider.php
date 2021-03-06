<?php

namespace Softspring\FirebaseAuthenticationBundle\Security\Authentication\Provider;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FirebaseAuthenticationProvider extends UserAuthenticationProvider
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var bool
     */
    private $hideUserNotFoundExceptions;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var Auth
     */
    private $firebaseAuth;

    /**
     * FirebaseAuthenticationProvider constructor.
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, string $providerKey, Auth $firebaseAuth, bool $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->firebaseAuth = $firebaseAuth;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ('' === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            if (null === $user->getPassword()) {
                throw new BadCredentialsException('The presented password is invalid.');
            }

            try {
                $response = $this->firebaseAuth->signInWithEmailAndPassword($user->getUsername(), $presentedPassword);

                $accessToken = $response->accessToken();
                $idToken = $response->idToken();
                $firebaseUserId = $response->firebaseUserId();
                $refreshToken = $response->refreshToken();
                $ttl = $response->ttl();
                $data = $response->data();
            } catch (FailedToSignIn $e) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            return $user;
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }

            return $user;
        } catch (UsernameNotFoundException $e) {
            $e->setUsername($username);
            throw $e;
        } catch (\Exception $e) {
            $e = new AuthenticationServiceException($e->getMessage(), 0, $e);
            $e->setToken($token);
            throw $e;
        }
    }
}
