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

        return $treeBuilder;
    }
}
