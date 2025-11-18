<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Form\TypeGuesser;

use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

final class TypeInfoTypeGuesser implements FormTypeGuesserInterface
{
    public function __construct(
        private readonly TypeResolverInterface $typeResolver,
    ) {}

    #[\Override]
    public function guessType(string $class, string $property): ?TypeGuess
    {
        /** @var class-string $class */
        if (null === $typeInfo = $this->getTypeInfo($class, $property)) {
            return null;
        }

        // FormTypes handling 'multiple' option
        if ($typeInfo->isIdentifiedBy(TypeIdentifier::ARRAY)) {
            /** @var TypeInfo\CollectionType $typeInfo */
            // @phpstan-ignore missingType.generics
            $collValueType = $typeInfo->getCollectionValueType();

            /** @var TypeInfo\ObjectType<mixed> $collValueType */
            return match (true) {
                $collValueType->isIdentifiedBy(\UnitEnum::class) => new TypeGuess(CoreType\EnumType::class, ['class' => $collValueType->getClassName(), 'multiple' => true], Guess::HIGH_CONFIDENCE),
                $collValueType->isIdentifiedBy(\DateTimeZone::class) => new TypeGuess(CoreType\TimezoneType::class, ['input' => 'datetimezone', 'multiple' => true], Guess::HIGH_CONFIDENCE),
                default => new TypeGuess(CoreType\TextType::class, [], Guess::LOW_CONFIDENCE)
            };
        }

        if ($typeInfo->isIdentifiedBy(TypeIdentifier::OBJECT)) {
            if ($typeInfo->isIdentifiedBy(\UnitEnum::class)) {
                /** @var TypeInfo\ObjectType<mixed> */
                $innerType = $typeInfo instanceof TypeInfo\NullableType ? $typeInfo->getWrappedType() : $typeInfo;

                return new TypeGuess(CoreType\EnumType::class, ['class' => $innerType->getClassName()], Guess::HIGH_CONFIDENCE);
            }

            return match (true) {
                $typeInfo->isIdentifiedBy(\DateTime::class) => new TypeGuess(CoreType\DateTimeType::class, [], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy(\DateTimeImmutable::class) => new TypeGuess(CoreType\DateTimeType::class, ['input' => 'datetime_immutable'], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy(\DateInterval::class) => new TypeGuess(CoreType\DateIntervalType::class, [], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy(\DateTimeZone::class) => new TypeGuess(CoreType\TimezoneType::class, ['input' => 'datetimezone'], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy('Symfony\Component\Uid\Ulid') => new TypeGuess(CoreType\UlidType::class, [], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy('Symfony\Component\Uid\Uuid') => new TypeGuess(CoreType\UuidType::class, [], Guess::HIGH_CONFIDENCE),
                $typeInfo->isIdentifiedBy('Symfony\Component\HttpFoundation\File\File') => new TypeGuess(CoreType\FileType::class, [], Guess::HIGH_CONFIDENCE),
                default => new TypeGuess(CoreType\TextType::class, [], Guess::LOW_CONFIDENCE)
            };
        }

        return match (true) {
            $typeInfo->isIdentifiedBy(TypeIdentifier::STRING) => new TypeGuess(CoreType\TextType::class, [], Guess::MEDIUM_CONFIDENCE),
            $typeInfo->isIdentifiedBy(TypeIdentifier::INT) => new TypeGuess(CoreType\IntegerType::class, [], Guess::MEDIUM_CONFIDENCE),
            $typeInfo->isIdentifiedBy(TypeIdentifier::FLOAT) => new TypeGuess(CoreType\NumberType::class, [], Guess::MEDIUM_CONFIDENCE),
            $typeInfo->isIdentifiedBy(TypeIdentifier::BOOL) => new TypeGuess(CoreType\CheckboxType::class, [], Guess::HIGH_CONFIDENCE),
            default => new TypeGuess(CoreType\TextType::class, [], Guess::LOW_CONFIDENCE)
        };
    }

    #[\Override]
    public function guessRequired(string $class, string $property): ?ValueGuess
    {
        /** @var class-string $class */
        if (null === $typeInfo = $this->getTypeInfo($class, $property)) {
            return null;
        }

        return new ValueGuess(!$typeInfo->isNullable(), Guess::MEDIUM_CONFIDENCE);
    }

    #[\Override]
    public function guessMaxLength(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    #[\Override]
    public function guessPattern(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    /**
     * @param class-string $class
     */
    private function getTypeInfo(string $class, string $property): ?TypeInfo
    {
        try {
            $refProperty = new \ReflectionProperty($class, $property);
        } catch (\ReflectionException $e) {
            return null;
        }

        try {
            return $this->typeResolver->resolve($refProperty);
        } catch (UnsupportedException $e) {
            return null;
        }
    }
}
