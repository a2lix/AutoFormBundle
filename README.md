# A2lix AutoForm Bundle

[![Latest Stable Version](https://poser.pugx.org/a2lix/auto-form-bundle/v/stable)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Latest Unstable Version](https://poser.pugx.org/a2lix/auto-form-bundle/v/unstable)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Total Downloads](https://poser.pugx.org/a2lix/auto-form-bundle/downloads)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![License](https://poser.pugx.org/a2lix/auto-form-bundle/license)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Build Status](https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml)

Stop writing boilerplate form code. This bundle provides a single, powerful `AutoType` form type that automatically generates a complete Symfony form from any PHP class.

## Installation

Use Composer to install the bundle:

```bash
composer require a2lix/auto-form-bundle
```

## Basic Usage

The simplest way to use `AutoType` is directly in your controller. It will generate a form based on the properties of the entity or DTO you pass it.

```php
// ...

class TaskController extends AbstractController
{
    public function new(Request $request): Response
    {
        $task = new Task(); // Any entity or DTO
        $form = $this->createForm(AutoType::class, $task)
            ->add('save', SubmitType::class)
            ->handleRequest($request)
        ;

        // ...
    }
}
```

## How It Works

`AutoType` reads the properties of the class you provide in the `data_class` option. For each property, it intelligently configures a corresponding form field. This gives you a solid foundation that you can then customize in two main ways:

1.  **Form Options:** Pass a configuration array directly when you create the form.
2.  **PHP Attributes:** Add `#[AutoTypeCustom]` attributes directly to the properties of your entity or DTO.

Options passed directly to the form will always take precedence over attributes.

## Customization via Form Options

This is the most flexible way to configure your form. Here is a comprehensive example:

```php
// ...

class TaskController extends AbstractController
{
    public function new(Request $request, FormFactoryInterface $formFactory): Response
    {
        $product = new Product(); // Any entity or DTO
        $form = $formFactory->createNamed('product', AutoType::class, $product, [
            // 1. Optional define which properties should be excluded from the form.
            // Use '*' for an "exclude-by-default" strategy.
            'children_excluded' => ['id', 'internalRef'],

            // 2. Optional define which properties should be rendered as embedded forms.
            // Use '*' to embed all relational properties.
            'children_embedded' => ['category', 'tags'],

            // 3. Optional customize, override, or add fields.
            'children' => [
                // Override an existing property with new options
                'description' => [
                    'child_type' => TextareaType::class, // Force a specific form type
                    'label' => 'Product Description', // Standard form options
                    'priority' => 10, 
                ],

                // Add a field that does not exist on the DTO/entity
                'terms_and_conditions' => [
                    'child_type' => CheckboxType::class,
                    'mapped' => false,
                    'priority' => -100,
                ],

                // Completely replace a field's builder with a callable
                'price' => function(FormBuilderInterface $builder, array $propAttributeOptions): FormBuilderInterface {
                    // The callable receives the main builder and any options from a potential attribute.
                    // It must return a new FormBuilderInterface instance.
                    return $builder->create('price', MoneyType::class, ['currency' => 'EUR']);
                },

                // Add a new field to the form
                'save' => [
                    'child_type' => SubmitType::class,
                ],
            ],

            // 4. Optional final modifications on the complete form builder.
            'builder' => function(FormBuilderInterface $builder, array $classProperties): void {
                // This callable runs after all children have been added.
                if (isset($classProperties['code'])) {
                    $builder->remove('code');
                }
            },
        ])->handleRequest($request);

        // ...
    }
}

```

## Customization via `#[AutoTypeCustom]` Attribute

For a more declarative approach, you can place the configuration directly on the properties of your DTO or entity. This keeps the form configuration co-located with your data model.

```php
use A2lix\AutoFormBundle\Form\Attribute\AutoTypeCustom;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class Product
{
    #[AutoTypeCustom(excluded: true)]
    public int $id;

    public ?string $name = null;

    #[AutoTypeCustom(type: TextareaType::class, options: ['attr' => ['rows' => 5]])]
    public ?string $description = null;

    #[AutoTypeCustom(embedded: true)]
    public Category $category;
}
```

## Advanced Recipes

### Creating a Compound Field with `inherit_data`

You can use a callable in the `children` option to create complex fields that map to the parent object, which is useful for things like date ranges.

```php
'children' => [
    '_' => function (FormBuilderInterface $builder): FormBuilderInterface {
        return $builder
            ->create('validity_range', FormType::class, ['inherit_data' => true])
                ->add('startsAt', DateType::class, [/* ... */])
                ->add('endsAt', DateType::class, [/* ... */]);
    },
]
```

## Global Configuration

While not required, you can configure the bundle globally. For example, you can define a list of properties to always exclude.

Create a configuration file in `config/packages/a2lix_auto_form.yaml`:

```yaml
a2lix_auto_form:
    # Exclude 'id' and 'createdAt' properties from all AutoType forms by default
    children_excluded: [id, createdAt]
```

## License

This package is available under the [MIT license](LICENSE).