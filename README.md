# A2lix Auto Form Bundle

Automate form building.

[![Latest Stable Version](https://poser.pugx.org/a2lix/auto-form-bundle/v/stable)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Latest Unstable Version](https://poser.pugx.org/a2lix/auto-form-bundle/v/unstable)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![License](https://poser.pugx.org/a2lix/auto-form-bundle/license)](https://packagist.org/packages/a2lix/auto-form-bundle)

[![Total Downloads](https://poser.pugx.org/a2lix/auto-form-bundle/downloads)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Monthly Downloads](https://poser.pugx.org/a2lix/auto-form-bundle/d/monthly)](https://packagist.org/packages/a2lix/auto-form-bundle)
[![Daily Downloads](https://poser.pugx.org/a2lix/auto-form-bundle/d/daily)](https://packagist.org/packages/a2lix/auto-form-bundle)

| Branch | Tools |
| --- | --- |
| master | [![Build Status][ci_badge]][ci_link] [![Coverage Status][coverage_badge]][coverage_link] |

## Installation

Use composer:

```bash
composer require a2lix/auto-form-bundle
```

After the successful installation, add/check the bundle registration:

```php
//  bundles.php is automatically updated if flex is installed.
// ...
A2lix\AutoFormBundle\A2lixAutoFormBundle::class => ['all' => true],
// ...
```

## Configuration

There is no minimal configuration, so this part is optional. Full list:

```yaml
# Create a dedicated a2lix.yaml in config/packages with:

a2lix_auto_form:
    excluded_fields: [id, locale, translatable]       # [1]
```

1. Optional.

## Usage

### In a classic formType

```php
use A2lix\AutoFormBundle\Form\Type\AutoFormType;
...
$builder->add('medias', AutoFormType::class);
```

### Advanced examples

```php
use A2lix\AutoFormBundle\Form\Type\AutoFormType;
...
$builder->add('medias', AutoFormType::class, [
    'fields' => [                               // [2]
        'description' => [                         // [3.a]
            'field_type' => 'textarea',                // [4]
            'label' => 'descript.',                    // [4]
            'locale_options' => [                  // [3.b]
                'es' => ['label' => 'descripción']     // [4]
                'fr' => ['display' => false]           // [4]
            ]
        ]
    ],
    'excluded_fields' => ['details']            // [2]
]);
```

2. Optional. If set, override the default value from config.yml
3. Optional. If set, override the auto configuration of fields
   - [3.a] Optional. - For a field, applied to all locales
   - [3.b] Optional. - For a specific locale of a field
4. Optional. Common options of symfony forms (max_length, required, trim, read_only, constraints, ...), which was added 'field_type' and 'display'

## Additional

### Example

See [Demo Bundle](https://github.com/a2lix/Demo) for more examples.

## Contribution help

```
docker run --rm --interactive --tty --volume $PWD:/app --user $(id -u):$(id -g) composer install --ignore-platform-reqs
docker run --rm --interactive --tty --volume $PWD:/app --user $(id -u):$(id -g) composer run-script phpunit
docker run --rm --interactive --tty --volume $PWD:/app --user $(id -u):$(id -g) composer run-script cs-fixer
```

## License

This package is available under the [MIT license](LICENSE).

[ci_badge]: https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml/badge.svg
[ci_link]: https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml
[coverage_badge]: https://codecov.io/gh/a2lix/AutoFormBundle/branch/master/graph/badge.svg
[coverage_link]: https://codecov.io/gh/a2lix/AutoFormBundle/branch/master
