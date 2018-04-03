<?php

declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class A2lixAutoFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('a2lix_form.xml');
        $loader->load('object_info.xml');

        $definition = $container->getDefinition('a2lix_auto_form.form.manipulator.doctrine_orm_manipulator');
        $definition->replaceArgument(1, $config['excluded_fields']);

        $container->setAlias('a2lix_auto_form.manipulator.default', 'a2lix_auto_form.form.manipulator.doctrine_orm_manipulator');
    }
}
