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
                        ->scalarNode('base_url')
                            ->info('The base URL of the CO instance')
                        ->end()
                        ->scalarNode('client_id')
                            ->info('The ID of the client (client credentials flow)')
                        ->end()
                        ->scalarNode('client_secret')
                            ->info('The client secret for the client referenced by client_id')
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
                            ->scalarNode('filter')
                                ->info('A filter expression. Gets the "org" object as input and should return false to skip organizations')
                                ->example('true')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
