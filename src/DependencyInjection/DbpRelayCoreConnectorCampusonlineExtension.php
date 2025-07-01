<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
use Dbp\Relay\CoreConnectorCampusonlineBundle\Service\OrganizationDataProvider;
use Dbp\Relay\CoreConnectorCampusonlineBundle\Service\UserAttributeProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayCoreConnectorCampusonlineExtension extends ConfigurableExtension
{
    use ExtensionTrait;

    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $orgaProv = $container->getDefinition(OrganizationDataProvider::class);
        $orgaProv->addMethodCall('setConfig', [$mergedConfig['campus_online'] ?? []]);

        $authProv = $container->getDefinition(UserAttributeProvider::class);
        $authProv->addMethodCall('setConfig', [$mergedConfig]);
    }
}
