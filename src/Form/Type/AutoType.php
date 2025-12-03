<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Form\Type;

use A2lix\AutoFormBundle\Form\Builder\AutoTypeBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-import-type FormOptionsDefaults from AutoTypeBuilder
 *
 * @extends AbstractType<mixed>
 */
final class AutoType extends AbstractType
{
    /**
     * @param list<string> $globalExcludedChildren
     * @param list<string> $globalEmbeddedChildren
     */
    public function __construct(
        private readonly AutoTypeBuilder $autoTypeBuilder,
        private readonly array $globalExcludedChildren = [],
        private readonly array $globalEmbeddedChildren = [],
        private readonly bool $handleTranslationTypes = false,
    ) {}

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var FormOptionsDefaults $options */
        $this->autoTypeBuilder->buildChildren($builder, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'children' => [],
            'children_excluded_' => $this->globalExcludedChildren,
            'children_excluded' => null,
            'children_embedded_' => $this->globalEmbeddedChildren,
            'children_embedded' => null,
            'children_groups' => null,
            'builder' => null,
            'handle_translation_types' => $this->handleTranslationTypes,
            'gedmo_only' => false,
        ]);

        $resolver->setAllowedTypes('children_excluded', 'string[]|string|callable|null');
        $resolver->setInfo('children_excluded', 'An array of properties, the * wildcard, or a callable (mixed $previousValue): mixed');
        $resolver->setNormalizer('children_excluded', static function (Options $options, mixed $value): mixed {
            $defaultValue = $options['children_excluded_'];

            if (is_callable($value)) {
                return $value($defaultValue);
            }

            return $value ?? $defaultValue;
        });

        $resolver->setAllowedTypes('children_embedded', 'string[]|string|callable|null');
        $resolver->setInfo('children_embedded', 'An array of properties, the * wildcard, or a callable (mixed $previousValue): mixed');
        $resolver->setNormalizer('children_embedded', static function (Options $options, mixed $value): mixed {
            $defaultValue = $options['children_embedded_'];

            if (is_callable($value)) {
                return $value($defaultValue);
            }

            return $value ?? $defaultValue;
        });

        $resolver->setAllowedTypes('children_groups', 'string[]|null');
        $resolver->setAllowedTypes('builder', 'callable|null');
        $resolver->setInfo('builder', 'A callable (FormBuilderInterface $builder, string[] $classProperties): void');

        // Translation options (translation_form_bundle required)
        $resolver->setAllowedTypes('handle_translation_types', 'bool');
        $resolver->setAllowedTypes('gedmo_only', 'bool');
        // Others defaults FormType:class options
        $resolver->setNormalizer('data_class', static function (Options $options, ?string $value): string {
            if (null === $value) {
                throw new \RuntimeException('Missing "data_class" option of "AutoType".');
            }

            return $value;
        });
        $resolver->setDefault('validation_groups', static function (Options $options): ?array {
            /** @var list<string>|null */
            return $options['children_groups'];
        });
    }
}
