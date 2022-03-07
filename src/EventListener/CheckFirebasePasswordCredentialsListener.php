<?php

namespace Softspring\FirebaseAuthenticationBundle\EventListener;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Softspring\FirebaseAuthenticationBundle\Authenticator\Passport\Credentials\FirebasePasswordCredentials;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use function trigger_deprecation;

class CheckFirebasePasswordCredentialsListener implements EventSubscriberInterface
{
    protected Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport instanceof Passport || !$passport->hasBadge(FirebasePasswordCredentials::class)) {
            return;
        }

        // Use the password hasher to validate the credentials
        $user = $passport->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            trigger_deprecation('symfony/security-http', '5.3', 'Not implementing the "%s" interface in class "%s" while using password-based authentication is deprecated.', PasswordAuthenticatedUserInterface::class, get_debug_type($user));
        }

        /** @var FirebasePasswordCredentials $badge */
        $badge = $passport->getBadge(FirebasePasswordCredentials::class);

        if ($badge->isResolved()) {
            return;
        }

        $presentedPassword = $badge->getPassword();
        if ('' === $presentedPassword) {
            throw new BadCredentialsException('The presented password cannot be empty.');
        }

        if (null === $user->getPassword()) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        try {
            $response = $this->auth->signInWithEmailAndPassword($user->getUserIdentifier(), $presentedPassword);
            $passport->setAttribute('firebaseData', $response->data());
            $passport->setAttribute('firebaseJwt', $response->asTokenResponse());
        } catch (FailedToSignIn $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        } catch (InvalidArgumentException $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        $badge->markResolved();

        if (!$passport->hasBadge(PasswordUpgradeBadge::class)) {
            $passport->addBadge(new PasswordUpgradeBadge($presentedPassword));
        }
    }

    public function setJwtToken(LoginSuccessEvent $event)
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(FirebasePasswordCredentials::class)) {
            return;
        }

        $request = $event->getRequest();

//        // the ResponseListener configures the cookie saved in this attribute on the final response object
//        $request->attributes->set(ResponseListener::COOKIE_ATTR_NAME, new Cookie(
//            $this->options['name'],
//            $rememberMeDetails ? $rememberMeDetails->toString() : null,
//            $rememberMeDetails ? $rememberMeDetails->getExpires() : 1,
//            $this->options['path'],
//            $this->options['domain'],
//            $this->options['secure'] ?? $request->isSecure(),
//            $this->options['httponly'],
//            false,
//            $this->options['samesite']
//        ));
        $request->attributes->set('_security_firebase_cookie', new Cookie(
            '_pvet_token',
            $passport->getAttribute('firebaseJwt')['id_token'],
            time()+$passport->getAttribute('firebaseJwt')['expires_in'],
            '/',
            'pvet.qpv.local',
            true,
            true,
            false,
            'lax',
        ));
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->has('_security_firebase_cookie')) {
            $response->headers->setCookie($request->attributes->get('_security_firebase_cookie'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'checkPassport',
            LoginSuccessEvent::class => 'setJwtToken',
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }
}