# Upgrade from 0.x to 1.x

Version 1.x is a complete rewrite of the bundle. It is not backward compatible.
The bundle is no longer tied to Doctrine and now uses Symfony's PropertyInfo component to guess form types from any PHP object.

## BC BREAK: Minimum Requirements

- **PHP:** `8.4` or higher is required.
- **Symfony:** `7.4` or higher is required.

## BC BREAK: Composer Dependencies

1.  **Update your `composer.json`** to require the new version:
    ```json
    {
        "require": {
            "a2lix/auto-form-bundle": "^1.0"
        }
    }
    ```
    Then run `composer update a2lix/auto-form-bundle --with-all-dependencies`.

2.  **Decoupled from Doctrine:** The bundle no longer requires `doctrine/persistence` or `symfony/doctrine-bridge`. If your project relies on them, you must now require them explicitly in your own `composer.json`.

## BC BREAK: Form Type Renaming

The main form type has been renamed.

- `A2lix\AutoFormBundle\Form\Type\AutoFormType` is **removed**.
- Use `A2lix\AutoFormBundle\Form\Type\AutoType` instead.

**Before:**
```php
use A2lix\AutoFormBundle\Form\Type\AutoFormType;
$this->createForm(AutoFormType::class, /* ... */);
```

**After:**
```php
use A2lix\AutoFormBundle\Form\Type\AutoType;
$this->createForm(AutoType::class, /* ... */);
```

## BC BREAK: Field Customization

The way to customize fields has completely changed. There are two methods: using PHP attributes (recommended) or using form options.

### Method 1: Using `#[AutoTypeCustom]` Attribute (Recommended)

Customization can be done using the `#[AutoTypeCustom]` PHP attribute directly on your data object's properties.

**Before:**
```php
// In your Controller
$this->createForm(AutoFormType::class, new Product(), [
    'fields' => [
        'description' => [
            'field_type' => TextareaType::class,
            'label' => 'Product Description',
        ],
    ],
    'excluded_fields' => ['createdAt'],
]);
```

**After:**
```php
// On your data object (Entity or DTO)
use A2lix\AutoFormBundle\Form\Attribute\AutoTypeCustom;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class Product
{
    #[AutoTypeCustom(
        type: TextareaType::class,
        options: ['label' => 'Product Description']
    )]
    public string $description;

    #[AutoTypeCustom(display: false)]
    public \DateTimeImmutable $createdAt;

    // ... other properties
}

// In your Controller, the configuration is now minimal
$this->createForm(AutoType::class, new Product())
```

### Method 2: Using Form Options

Customization is also still possible at the form level by passing options to `AutoType`. **This method will override any `#[AutoTypeCustom]` attributes set on the data object.**

The option keys have been renamed for clarity:

- The main configuration array `fields` is now `children`.
- Inside a child's configuration, `field_type` is now `child_type`.
- The `excluded_fields` option is now `children_excluded`.

**Before:**
```php
// In your Controller
$this->createForm(AutoFormType::class, new Product(), [
    'fields' => [
        'description' => [
            'field_type' => TextareaType::class,
            'label' => 'Product Description',
        ],
    ],
    'excluded_fields' => ['createdAt'],
]);
```

**After:**
```php
// In your Controller
$this->createForm(AutoType::class, new Product(), [
    'children' => [
        'description' => [
            'child_type' => TextareaType::class,
            'label' => 'Product Description', // Note: options are now merged at the top level
        ],
    ],
    'children_excluded' => ['createdAt'],
]);
```

## BC BREAK: Removed Classes and Concepts

The internal architecture was refactored. The following major classes and concepts have been **removed** without direct replacement:

- `A2lix\AutoFormBundle\Form\EventListener\AutoFormListener`
- `A2lix\AutoFormBundle\Form\Manipulator\DoctrineORMManipulator`
- `A2lix\AutoFormBundle\ObjectInfo\DoctrineORMInfo`

## BC BREAK: Bundle Configuration

The bundle is now zero-configuration for most use cases. You should **remove** your old configuration file at `config/packages/a2lix_auto_form.yaml`.
