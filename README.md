# Laravel WhatsApp Sender

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dogfromthemoon/laravel-whatsapp-sender.svg?style=flat-square)](https://packagist.org/packages/dogfromthemoon/laravel-whatsapp-sender)
[![Total Downloads](https://img.shields.io/packagist/dt/dogfromthemoon/laravel-whatsapp-sender.svg?style=flat-square)](https://packagist.org/packages/dogfromthemoon/laravel-whatsapp-sender)
![GitHub Actions](https://github.com/porrello/laravel-whatsapp-sender/actions/workflows/main.yml/badge.svg)

Laravel WhatsApp Sender is a package for sending WhatsApp messages from Laravel.

## Installation

You can install the package via composer:

```bash
composer require dogfromthemoon/laravel-whatsapp-sender
```

## Configuration

Before you can use Laravel WhatsApp Sender, you need to set your WhatsApp API credentials in your `.env` file:

```dotenv
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_TOKEN=
```

You can obtain your WhatsApp API credentials from the [Facebook Business Manager](https://developers.facebook.com/apps) website.

1. Create a new app.
2. Add the WhatsApp product to your app.
3. Add a phone number to your app, which will serve as your WhatsApp business account.
4. Generate a permanent token for your WhatsApp business account.

## Usage

### Sending Messages

```php
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;

// If the conversation was started by the user, you can send a message to the user.
// You will not be able to send a message to the user if the conversation was not started by the user.
$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$message = 'Hello, this is a test message!';

$whatsappSender->sendTextMessage($phone, $message);

```

### Sending Interactive Lists

```php
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;

$phone = '1234567890';
$message = 'Please select an item from the list';
$sections = [
    [
        "rows" => [
            [
                "title" => "Item 1",
                "description" => "Description of Item 1",
                "row_id" => "item1"
            ],
            [
                "title" => "Item 2",
                "description" => "Description of Item 2",
                "row_id" => "item2"
            ]
        ]
    ]
];

$response = LaravelWhatsappSender::sendInteractiveList($phone, $message, $sections, 'VIEW');
```

### Sending Buttons Lists

```php
use Dogfromthemoon\LaravelWhatsappSender\Facades\LaravelWhatsappSender;

$phone = '+1234567890';
$message = 'Please choose an option:';
$buttons = [
    [
        "type" => "postback",
        "title" => "Option 1",
        "payload" => "option1"
    ],
    [
        "type" => "postback",
        "title" => "Option 2",
        "payload" => "option2"
    ],
    [
        "type" => "postback",
        "title" => "Option 3",
        "payload" => "option3"
    ]
];

$response = LaravelWhatsappSender::sendButtonsMessage($phone, $message, $buttons);
```

### Sending Multi Product Messages

```php
$header = 'header-content';
$body = 'body-content';
$footer = 'footer-content';
$catalogId = 'CATALOG_ID';
$sections = [
    [
        'title' => 'section-title',
        'product_items' => [
            ['product_retailer_id' => 'product-SKU-in-catalog'],
            ['product_retailer_id' => 'product-SKU-in-catalog']
        ]
    ],
    [
        'title' => 'section-title',
        'product_items' => [
            ['product_retailer_id' => 'product-SKU-in-catalog'],
            ['product_retailer_id' => 'product-SKU-in-catalog']
        ]
    ]
];

$phone = '+1234567890';

$response = sendMultiProductMessage($phone, $header, $body, $footer, $catalogId, $sections);
```

### Sending Single Product Messages

```php

use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;

$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$catalogId = 'CATALOG_ID'; // The catalog ID for the product
$productId = 'PRODUCT_ID'; // The product retailer ID in the catalog
$bodyText = 'This is the body text of the product message';
$footerText = 'This is the footer text of the product message';

$whatsappSender->sendProductMessage($phone, $catalogId, $productId, $bodyText, $footerText);
```

### Reply to Messages

```php
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;

$whatsappSender = new LaravelWhatsappSender();

// The phone number of the recipient in E.164 format
$phone = '1234567890';

// The message ID of the message you're replying to
$messageId = '12345';

// The text of the reply message
$reply = 'This is my reply to your message!';

$response = $whatsappSender->replyToMessage($phone, $messageId, $reply);
```

### Reaction Messages

```php

$whatsappSender = new LaravelWhatsappSender();
$phone = '+14155552671';
$messageId = 'wamid.HBgLM...';
$emoji = '\uD83D\uDE00';
$response = $whatsappSender->sendReaction($phone, $messageId, $emoji);
```

### Media Messages

```php
$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$imageId = 'MEDIA-OBJECT-ID';

$whatsappSender->sendImageMessage($phone, $imageId);
```

### Location Messages

```php
$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$longitude = -122.431297;
$latitude = 37.773972;
$locationName = 'Golden Gate Bridge';
$locationAddress = 'Golden Gate Bridge, San Francisco, CA, USA';

$whatsappSender->sendLocationMessage($phone, $longitude, $latitude, $locationName, $locationAddress);
```

### Contact Messages

```php
$whatsappSender = new LaravelWhatsappSender();
$phone = '1234567890'; // Phone number in E.164 format
$contacts = [
    [
        "addresses" => [
            [
                "street" => "STREET",
                "city" => "CITY",
                "state" => "STATE",
                "zip" => "ZIP",
                "country" => "COUNTRY",
                "country_code" => "COUNTRY_CODE",
                "type" => "HOME"
            ],
            [
                // Additional addresses
            ]
        ],
        "birthday" => "YEAR_MONTH_DAY",
        "emails" => [
            [
                "email" => "EMAIL",
                "type" => "WORK"
            ],
            // Additional emails
        ],
        "name" => [
            "formatted_name" => "NAME",
            "first_name" => "FIRST_NAME",
            "last_name" => "LAST_NAME",
            "middle_name" => "MIDDLE_NAME",
            "suffix" => "SUFFIX",
            "prefix" => "PREFIX"
        ],
        "org" => [
            "company" => "COMPANY",
            "department" => "DEPARTMENT",
            "title" => "TITLE"
        ],
        "phones" => [
            [
                "phone" => "PHONE_NUMBER",
                "type" => "HOME"
            ],
            [
                "phone" => "PHONE_NUMBER",
                "type" => "WORK",
                "wa_id" => "PHONE_OR_WA_ID"
            ]
        ],
        "urls" => [
            [
                "url" => "URL",
                "type" => "WORK"
            ],
            // Additional urls
        ]
    ]
];

$whatsappSender->sendContactsMessage($phone, $contacts);
```

### Template Messages

```php
$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format
$templateName = 'TEMPLATE_NAME';
$languageCode = 'LANGUAGE_AND_LOCALE_CODE';
$components = [
    [
        "type" => "header",
        "parameters" => [
            [
                "type" => "image",
                "image" => [
                    "link" => "http(s)://URL"
                ]
            ]
        ]
    ],
    [
        "type" => "body",
        "parameters" => [
            [
                "type" => "text",
                "text" => "TEXT_STRING"
            ],
            [
                "type" => "currency",
                "currency" => [
                    "fallback_value" => "VALUE",
                    "code" => "USD",
                    "amount_1000" => NUMBER
                ]
            ],
            [
                "type" => "date_time",
                "date_time" => [
                    "fallback_value" => "MONTH DAY, YEAR"
                ]
            ]
        ]
    ],
    [
        "type" => "button",
        "sub_type" => "quick_reply",
        "index" => "0",
        "parameters" => [
            [
                "type" => "payload",
                "payload" => "PAYLOAD"
            ]
        ]
    ],
    [
        "type" => "button",
        "sub_type" => "quick_reply",
        "index" => "1",
        "parameters" => [
            [
                "type" => "payload",
                "payload" => "PAYLOAD"
            ]
        ]
    ]
];

$whatsappSender->sendTemplateMessage($phone, $templateName, $languageCode, $components);
```

### Upload Media

```php

$whatsappSender = new LaravelWhatsappSender();

$whatsappSender = new LaravelWhatsappSender();
$filePath = public_path('images/demo.jpg');
$mimeType = 'image/jpeg';
$response = $whatsappSender->uploadMedia($filePath, $mimeType);
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
