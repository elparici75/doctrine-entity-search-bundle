<?php

/*
 * This file is part of the Safepass website.
 *
 * (c) HC Conseil <contact@hcconseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elparici\EntitySearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('entity_search');
        $rootNode
            ->children()
                ->enumNode('pager')
                    ->values(['pager_fanta', 'knp_paginator'])
                ->end()
                ->arrayNode('entities_mapping')
                 //->prototype('array')
                 ->prototype('array')
                 ->prototype('variable')->end()
                 ->ignoreExtraKeys()->end()
                 ->end()

                 ->arrayNode('table_header')
                 ->prototype('array')
                 ->ignoreExtraKeys()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
