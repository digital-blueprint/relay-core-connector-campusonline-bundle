<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_core_connector_campusonline');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('campus_online')
                    ->children()
                        ->scalarNode('api_url')
                        ->end()
                        ->scalarNode('api_token')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('organization_ids')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->info('The attribute name this list will be stored in')
                                ->example('all_ids')
                            ->end()
                            ->scalarNode('root_id')
                                ->info('The root organization ID used to build the ID list. The ID is included in the result')
                                ->example('37')
                            ->end()
                            ->scalarNode('filter')
                                ->info('A filter expression. Gets the "org" object as input and should return false to skip organizations')
                                ->example('37')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
