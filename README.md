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

## Configuration

There is no minimal configuration, so this part is optional. Full list:

```yaml
# Create a dedicated a2lix.yaml in config/packages with:

a2lix_auto_form:
    children_excluded: [id]       # [1]
```

1. Optional.

## Usage

TODO

## Additional

### Example

See [Demo Bundle](https://github.com/a2lix/Demo) for more examples.

## License

This package is available under the [MIT license](LICENSE).

[ci_badge]: https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml/badge.svg
[ci_link]: https://github.com/a2lix/AutoFormBundle/actions/workflows/ci.yml
[coverage_badge]: https://codecov.io/gh/a2lix/AutoFormBundle/branch/master/graph/badge.svg
[coverage_link]: https://codecov.io/gh/a2lix/AutoFormBundle/branch/master
