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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class A2lixAutoFormBundle extends AbstractBundle implements CompilerPassInterface
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
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

    #[\Override]
    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        if (!$container->hasExtension('a2lix_translation_form')) {
            return;
        }

        $config = $container->getExtensionConfig($this->extensionAlias)[0];

        if (null === ($config['children_excluded'] ?? null)) {
            $container->prependExtensionConfig($this->extensionAlias, [
                'children_excluded' => [
                    'id',
                    'newTranslations',
                    'translatable',
                    'locale',
                    'currentLocale',
                    'defaultLocale',
                ],
            ]);
        }
    }

    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('a2lix_auto_form.form.type.auto_type')
            ->arg('$globalExcludedChildren', $config['children_excluded'])
        ;
    }

    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('a2lix_translation_form')) {
            return;
        }

        $container->getDefinition('a2lix_auto_form.form.type.auto_type')
            ->setArgument('$handleTranslationTypes', true)
        ;

        $config = $container->getExtensionConfig($this->extensionAlias)[0];
        $container->getDefinition('a2lix_translation_form.form.type.translations_type')
            ->setArguments([
                '$globalExcludedChildren' => $config['children_excluded'] ?? [],
                '$globalEmbeddedChildren' => $config['children_embedded'] ?? [],
            ])
        ;
    }
}
