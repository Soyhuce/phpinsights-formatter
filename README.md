![alt text](https://soyhuce.fr/wp-content/uploads/2020/06/logo-soyhuce-dark-1.png "Soyhuce")

# PhpInsights formatters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/soyhuce/phpinsights-formatter.svg?style=flat-square)](https://packagist.org/packages/soyhuce/phpinsights-formatter)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/soyhuce/phpinsights-formatter/run-tests?label=tests)](https://github.com/soyhuce/phpinsights-formatter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/soyhuce/phpinsights-formatter/Check%20&%20fix%20styling?label=code%20style)](https://github.com/soyhuce/phpinsights-formatter/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![GitHub PHPStan Action Status](https://img.shields.io/github/workflow/status/soyhuce/phpinsights-formatter/PHPStan?label=phpstan)](https://github.com/soyhuce/phpinsights-formatter/actions?query=workflow%3APHPStan+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/soyhuce/phpinsights-formatter.svg?style=flat-square)](https://packagist.org/packages/soyhuce/phpinsights-formatter)

Ce package permet l'ajout de formatters pour [PhpInsights](https://phpinsights.com/)

## Installation

You can install the package via composer:

```bash
composer require soyhuce/phpinsights-formatter
```

### Text Formatter

Utilisez le formatter via
```bash
php artisan insights --format=\\Soyhuce\\PhpInsights\\TextFormatter > insights.txt
```

### Markdown Formatter

Utilisez le formatter via
```bash
php artisan insights --format=\\Soyhuce\\PhpInsights\\MarkdownFormatter
```

Le résultat sera stocké sous `insights.md`

### Full Markdown Formatter

Utilisez le formatter via
```bash
php artisan insights --format=\\Soyhuce\\PhpInsights\\FullMarkdownFormatter
```

Le résultat sera stocké sous `insights-full.md`

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Bastien Philippe](https://github.com/Soyhuce)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
