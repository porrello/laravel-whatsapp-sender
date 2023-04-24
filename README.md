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
5. Add your webhook URL to your app.

### Here is a simple example of how to validate the webhook challenge code

```php
Route::get('/verification', function(Request $request) {
    $challenge = $request->input('hub_challenge');

    // Validate the challenge code you provided when you created the webhook
    // For example, you can compare it with a value stored in your configuration
    $isValid = ($challenge === 'super-secret-challenge-code');

    if ($isValid) {
        return $challenge;
    } else {
        abort(404);
    }
});
```


## Usage

### Remember to import the thing



```php
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;
```

### Sending Messages

```php
// If the conversation was started by the user, you can use this method to send messages.
// If you want to start a new conversation, you will need to use a template.

$whatsappSender = new LaravelWhatsappSender();

$phone = '1234567890'; // Phone number in E.164 format

$message = 'Hello, this is a test message!';

$response = $whatsappSender->sendTextMessage($phone, $message);
```

### Sending Interactive Lists

```php
$phone = '1234567890';
$message = 'Please select an item from the list';
$sections = [
    [
        "rows" => [
            [
                "title" => "Item 1",
                "description" => "Description of Item 1",
                "id" => "item1"
            ],
            [
                "title" => "Item 2",
                "description" => "Description of Item 2",
                "id" => "item2"
            ]
        ]
    ]
];

$whatsappSender = new LaravelWhatsappSender();
$response = $whatsappSender->sendInteractiveList($phone, $message, $sections, 'VIEW');
```

### Sending Buttons Lists

```php
$phone = '1234567890';
$message = 'Please choose an option:';
$buttons = [
    [
        'type' => 'reply',
        'reply' => [
            'id' => 'unique-id-paynow',
            'title' => 'button_pay_now',
        ]
    ],
    [
        'type' => 'reply',
        'reply' => [
            'id' => 'unique-id-moreoptions',
            'title' => 'button_more_options',
        ]
    ]
];

$response = $whatsappSender->sendButtonsMessage($phone, $message, $buttons);
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

$response = $whatsappSender->sendMultiProductMessage($phone, $header, $body, $footer, $catalogId, $sections);
```

### Sending Single Product Messages

```php
$phone = '1234567890'; // Phone number in E.164 format
$catalogId = 'CATALOG_ID'; // The catalog ID for the product
$productId = 'PRODUCT_ID'; // The product retailer ID in the catalog
$bodyText = 'This is the body text of the product message';
$footerText = 'This is the footer text of the product message';

$response = $whatsappSender->sendProductMessage($phone, $catalogId, $productId, $bodyText, $footerText);
```

### Reply to Messages

```php
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
$phone = '1234567890';
$messageId = 'wamid.HBgLM...';
$emoji = '\uD83D\uDE00';
$response = $whatsappSender->sendReaction($phone, $messageId, $emoji);
```

### Media Messages

```php
$phone = '1234567890'; // Phone number in E.164 format
$imageId = 'MEDIA-OBJECT-ID';

$response = $whatsappSender->sendImageMessage($phone, $imageId);
```

### Location Messages

```php
$phone = '1234567890'; // Phone number in E.164 format
$longitude = -122.431297;
$latitude = 37.773972;
$locationName = 'Golden Gate Bridge';
$locationAddress = 'Golden Gate Bridge, San Francisco, CA, USA';

$response = $whatsappSender->sendLocationMessage($phone, $longitude, $latitude, $locationName, $locationAddress);
```

### Contact Messages

```php
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
            "birthday" => "1980-01-01",
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

$response = $whatsappSender->sendContactsMessage($phone, $contacts);
```

### Template Messages

```php
$phone = '1234567890'; // Phone number in E.164 format
$templateName = 'sample_shipping_confirmation';
$languageCode = 'en_US';
$text = '7 to 15';
$response = $whatsappSender->sendTextTemplateMessage($phone, $templateName, $languageCode, $text);
```

### Upload Media

```php
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
