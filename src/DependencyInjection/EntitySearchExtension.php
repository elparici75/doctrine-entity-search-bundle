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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EntitySearchExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('entities_mapping', $config['entities_mapping']);

        $definition = $container->getDefinition('elparici_entity_search.utils.fake_repo');
        $definition->setArgument(4, $config['entities_mapping']);

        $definition = $container->getDefinition('elparici_entity_search.utils.pager');
        $definition->setArgument(1, $config['entities_mapping']);
        $definition->setArgument(2, $config['pager']);
    }
}
