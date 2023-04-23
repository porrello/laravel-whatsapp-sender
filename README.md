# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dogfromthemoon/laravel-whatsapp-sender.svg?style=flat-square)](https://packagist.org/packages/dogfromthemoon/laravel-whatsapp-sender)
[![Total Downloads](https://img.shields.io/packagist/dt/dogfromthemoon/laravel-whatsapp-sender.svg?style=flat-square)](https://packagist.org/packages/dogfromthemoon/laravel-whatsapp-sender)
![GitHub Actions](https://github.com/dogfromthemoon/laravel-whatsapp-sender/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require dogfromthemoon/laravel-whatsapp-sender
```

## Usage

```php
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;

$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$message = 'Hello, this is a test message!';

$whatsappSender->sendTextMessage($phone, $message);


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hello@dogfromthemoon.com instead of using the issue tracker.

## Credits

-   [Dog From The Moon](https://github.com/dogfromthemoon)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
