<?php

namespace Softspring\FirebaseAuthenticationBundle\Authenticator;

use Softspring\FirebaseAuthenticationBundle\Authenticator\Passport\Credentials\FirebasePasswordCredentials;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class FirebaseLoginAuthenticator extends FormLoginAuthenticator
{
    public function authenticate(Request $request): Passport
    {
        $passport = parent::authenticate($request);
        $badges = $passport->getBadges();

        /** @var UserBadge $user */
        $user = array_shift($badges);

        /** @var PasswordCredentials $credentials */
        $credentials = array_shift($badges);

        // rewrite credentials badge to force check against firebase
        $credentials = new FirebasePasswordCredentials($credentials->getPassword());

        $newPassport = new Passport($user, $credentials, $badges);
        foreach ($passport->getAttributes() as $attribute => $value) {
            $newPassport->setAttribute($attribute, $value);
        }

        return $newPassport;
    }
}
