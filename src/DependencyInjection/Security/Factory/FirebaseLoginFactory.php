<?php

namespace Softspring\FirebaseAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FirebaseLoginFactory creates services for form login authentication with firebase.
 *
 * @internal
 */
class FirebaseLoginFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();
        $this->addOption('token_cookie', '_pvet_token');
    }

    public function getKey(): string
    {
        return 'firebase-login';
    }

    protected function createAuthProvider(ContainerBuilder $container, string $id, array $config, string $userProviderId): string
    {
        if ($config['enable_csrf'] ?? false) {
            throw new InvalidConfigurationException('The "enable_csrf" option of "firebase_login" is only available when "security.enable_authenticator_manager" is set to "true", use "csrf_token_generator" instead.');
        }

        $provider = 'security.authentication.provider.firebase.'.$id;
        $container
            ->setDefinition($provider, new ChildDefinition('security.authentication.provider.firebase'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, new Reference('security.user_checker.'.$id))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        if (isset($config['csrf_token_generator'])) {
            throw new InvalidConfigurationException('The "csrf_token_generator" option of "firebase_login" is only available when "security.enable_authenticator_manager" is set to "false", use "enable_csrf" instead.');
        }

        $authenticatorId = 'security.authenticator.firebase_login.'.$firewallName;
        $options = array_intersect_key($config, $this->options);
        $authenticator = $container
            ->setDefinition($authenticatorId, new ChildDefinition('security.authenticator.firebase_login'))
            ->replaceArgument(1, new Reference($userProviderId))
            ->replaceArgument(2, new Reference($this->createAuthenticationSuccessHandler($container, $firewallName, $config)))
            ->replaceArgument(3, new Reference($this->createAuthenticationFailureHandler($container, $firewallName, $config)))
            ->replaceArgument(4, $options);

        if ($options['use_forward'] ?? false) {
            $authenticator->addMethodCall('setHttpKernel', [new Reference('http_kernel')]);
        }

        return $authenticatorId;
    }
}