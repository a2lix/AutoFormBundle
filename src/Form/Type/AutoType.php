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
     */
    public function __construct(
        private readonly AutoTypeBuilder $autoTypeBuilder,
        private readonly array $globalExcludedChildren = [],
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
            'children_excluded' => $this->globalExcludedChildren,
            'children_embedded' => [],
            'children_groups' => null,
            'builder' => null,
        ]);

        $resolver->setAllowedTypes('children_excluded', 'string[]|string');
        $resolver->setAllowedTypes('children_embedded', 'string[]|string');
        $resolver->setAllowedTypes('children_groups', 'string[]|null');
        $resolver->setAllowedTypes('builder', 'callable|null');
        $resolver->setInfo('builder', 'A callable that accepts two arguments (FormBuilderInterface $builder, string[] $classProperties). It should not return anything.');

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
