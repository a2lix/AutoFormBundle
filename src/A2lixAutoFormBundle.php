<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class A2lixAutoFormBundle extends AbstractBundle
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        // @phpstan-ignore method.notFound
        $definition->rootNode()
            ->children()
            ->arrayNode('children_excluded')
            ->scalarPrototype()->end()
            ->defaultValue(['id'])
            ->info('Class properties to exclude from autoType children. (Default: id)')
            ->end()
            ->end()
        ;
    }

    /**
     * @param array{children_excluded: list<string>} $config
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('a2lix_auto_form.form.type.auto_type')
            ->arg('$globalExcludedChildren', $config['children_excluded'])
        ;
    }
}
