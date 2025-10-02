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

namespace A2lix\AutoFormBundle\Form\Type;

use A2lix\AutoFormBundle\Form\Builder\AutoTypeBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @psalm-type childOptions = array{
 *    child_type?: class-string,
 *    child_name?: string,
 *    child_excluded?: bool,
 *    child_embedded?: bool,
 *    ...<string, mixed>
 * }
 * @psalm-type childBuilderCallable = callable(FormBuilderInterface $builder, array $propAttributeOptions): FormBuilderInterface
 * @psalm-type formBuilderCallable = callable(FormBuilderInterface $builder, string[] $classProperties): void
 * @psalm-type formOptionsDefaults = array{
 *    children: array<string, childOptions|childBuilderCallable>|[],
 *    children_excluded: list<string>|"*",
 *    children_embedded: list<string>|"*",
 *    builder: formBuilderCallable|null,
 * }
 */
class AutoType extends AbstractType
{
    public function __construct(
        private readonly AutoTypeBuilder $autoTypeBuilder,
        private readonly array $globalExcludedChildren = [],
    ) {}

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $this->autoTypeBuilder->buildChildren($builder, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'children' => [],
            'children_excluded' => $this->globalExcludedChildren,
            'children_embedded' => [],
            'builder' => null,
        ]);
        $resolver->setAllowedTypes('builder', ['null', 'callable']);
        $resolver->setInfo('builder', 'A callable that accepts two arguments (FormBuilderInterface $builder, string[] $classProperties). It should not return anything.');

        $resolver->setNormalizer('data_class', static function (Options $options, string $value): string {
            if (empty($value)) {
                throw new \RuntimeException('Missing "data_class" option of "AutoType".');
            }

            return $value;
        });
    }
}
