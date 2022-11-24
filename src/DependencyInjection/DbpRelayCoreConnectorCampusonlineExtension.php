<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
use Dbp\Relay\CoreConnectorCampusonlineBundle\Service\AuthorizationDataProvider;
use Dbp\Relay\CoreConnectorCampusonlineBundle\Service\OrganizationDataProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayCoreConnectorCampusonlineExtension extends ConfigurableExtension
{
    use ExtensionTrait;

    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $organizationCache = $container->register('dbp_api.cache.core-campusonline-connector', FilesystemAdapter::class);
        $organizationCache->setArguments(['relay-core-campusonline-connector', 60, '%kernel.cache_dir%/dbp/relay-core-campusonline-connector']);
        $organizationCache->setPublic(true);
        $organizationCache->addTag('cache.pool');

        $orgaProv = $container->getDefinition(OrganizationDataProvider::class);
        $orgaProv->addMethodCall('setConfig', [$mergedConfig['campus_online'] ?? []]);
        $orgaProv->addMethodCall('setCache', [$organizationCache, 3600]);

        $authProv = $container->getDefinition(AuthorizationDataProvider::class);
        $authProv->addMethodCall('setConfig', [$mergedConfig]);
    }
}
