<?php

namespace Softspring\FirebaseAuthenticationBundle;

use Softspring\FirebaseAuthenticationBundle\DependencyInjection\Security\Factory\FirebaseLoginFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsFirebaseAuthenticationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new FirebaseLoginFactory());
    }
}
