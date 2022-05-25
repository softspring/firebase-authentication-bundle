<?php

namespace Softspring\FirebaseAuthenticationBundle\Provider;

use App\Entity\User;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Softspring\UserBundle\Manager\UserManagerInterface;
use Softspring\UserBundle\Model\UserInterface as SfsUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FirebaseUserProvider // implements UserProviderInterface
{
//    /**
//     * @var UserManagerInterface
//     */
//    protected $userManager;
//
//    /**
//     * @var Auth
//     */
//    protected $firebaseAuth;
//
//    /**
//     * FirebaseUserProvider constructor.
//     *
//     * @param UserManagerInterface $userManager
//     * @param Auth                 $firebaseAuth
//     */
//    public function __construct(UserManagerInterface $userManager, Auth $firebaseAuth)
//    {
//        $this->userManager = $userManager;
//        $this->firebaseAuth = $firebaseAuth;
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function loadUserByUsername($email)
//    {
//        try {
//            /** @var User $dbUser */
//            $dbUser = $this->userManager->findUserByEmail($email);
//            $firebaseUser = $this->firebaseAuth->getUserByEmail($email);
//
//            if (!$dbUser) {
//                // create in database firebase user
//                /** @var User $dbUser */
//                $dbUser = $this->userManager->createEntity();
//                $dbUser->setId($firebaseUser->uid);
//                $dbUser->setEmail($email);
//                $dbUser->setPlainPassword('');
//                $dbUser->setPassword('-external-in-firebase-');
//            }
//
//            if ($firebaseUser->phoneNumber) {
//                $dbUser->setPhone(new Phone($firebaseUser->phoneNumber));
//            }
//            $dbUser->setEmail($firebaseUser->email);
//
//            if (!$dbUser->isConfirmed() && $firebaseUser->emailVerified) {
//                $dbUser->setConfirmedAt(new \DateTime('now'));
//            }
//
//            $this->userManager->saveEntity($dbUser);
//        } catch (UserNotFound $e) {
//            // create in firebase
//            // $e->getCode();
//        }
//
//        if (!$dbUser) {
//            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $email));
//        }
//
//        return $dbUser;
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function refreshUser(SymfonyUserInterface $user)
//    {
//        if (!$user instanceof SfsUserInterface) {
//            throw new UnsupportedUserException(sprintf('Expected an instance of Softspring\UserBundle\Model\UserInterface, but got "%s".', get_class($user)));
//        }
//
//        if (!$this->supportsClass(get_class($user))) {
//            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getEntityClass(), get_class($user)));
//        }
//
//        if (null === $reloadedUser = $this->userManager->findUserBy(['id' => $user->getId()])) {
//            throw new UsernameNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
//        }
//
//        return $reloadedUser;
//    }
//
//    /**
//     * @inheritdoc
//     */
//    public function supportsClass($class)
//    {
//        return $this->userManager->getEntityClass() === $class || is_subclass_of($class, $this->userManager->getEntityClass());
//    }
}
