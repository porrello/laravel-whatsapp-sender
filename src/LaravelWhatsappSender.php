<?php

namespace Dogfromthemoon\LaravelWhatsappSender;

use CURLFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class LaravelWhatsappSender
{

    protected $phoneNumberId;
    protected $token;

    public function __construct($phoneNumberId = null, $token = null)
    {
        // Prefer config values (Laravel best-practice) while keeping env fallbacks for direct usage.
        $this->phoneNumberId = $phoneNumberId ?: (function () {
            if (function_exists('config')) {
                return config('laravel-whatsapp-sender.phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');
            }

            return env('WHATSAPP_PHONE_NUMBER_ID');
        })();

        $this->token = $token ?: (function () {
            if (function_exists('config')) {
                return config('laravel-whatsapp-sender.token') ?: env('WHATSAPP_TOKEN');
            }

            return env('WHATSAPP_TOKEN');
        })();
    }

    public function downloadMedia($mediaInfo, $accessToken, $folderName = null)
    {
        $mediaUrl = $mediaInfo->url;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($mediaUrl);

        $mime_type = $mediaInfo->mime_type;
        $extension = explode('/', $mime_type)[1];
        $fileName = $mediaInfo->id . '.' . $extension;
        $folderPath = public_path($folderName);
        $filePath = $folderPath . '/' . $fileName;

        // Create the folder if it doesn't exist
        if (!File::isDirectory($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        file_put_contents($filePath, $response->body());
        $publicUrl = asset($folderName . '/' . $fileName);
        return $publicUrl;
    }

    public function getMediaInfo($mediaId, $accessToken)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v17.0/' . $mediaId . '/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $accessToken
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    /**
     * Send a WhatsApp text message to a phone number
     *
     * @param string $phone The phone number of the recipient (in international format, e.g. "+1234567890")
     * @param string $message The message to send
     * @return mixed The response object as returned by the WhatsApp API
     */
    public function sendTextMessage($phone, $message)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v14.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => ' {
                "messaging_product": "whatsapp",
                "preview_url": false,
                "recipient_type": "individual",
                "to": "' . $phone . '",
                "type": "text",
                "text": {
                    "body": "' . $message . '"
                }
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);

        $responseObj = json_decode($response);
        return $responseObj;
    }

    /**
     * Send an interactive list message to a WhatsApp recipient.
     *
     * @param string $phone The phone number of the recipient (in international format, e.g. "+1234567890")
     * @param string $message The message to include in the body of the message
     * @param array $sections An array of sections to include in the message. Each section should be an associative array with the following keys: "title" (string), "description" (string), "image" (string, URL), "action" (array with "type" (string) and "payload" (string) keys)
     * @param string $viewButtonlabel The label to use for the "View" button in the message (default: "VIEW")
     * @return object A JSON object representing the response from the WhatsApp API
     */
    public function sendInteractiveList($phone, $message, $sections, $viewButtonlabel = 'VIEW')
    {

        $options = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "header" => [
                    "type" => "text",
                    "text" => function_exists('config')
                        ? (config('laravel-whatsapp-sender.header_text') ?: config('app.name'))
                        : env('WHATSAPP_HEADER_TEXT', env('APP_NAME'))
                ],
                "body" => [
                    "text" => $message,
                ],
                "footer" => [
                    "text" => " "
                ],
                "action" => [
                    "button" => $viewButtonlabel,
                    "sections" => $sections
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v14.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }

    /**
     * Send a WhatsApp interactive message with buttons to a phone number
     *
     * @param string $phone The phone number of the recipient (in international format, e.g. "+1234567890")
     * @param string $message The message to send
     * @param array $buttons An array of button objects, where each object should have a `type` and `text` property.
     * @return object The response object from the API
     */

    public function sendButtonsMessage($phone, $message, $buttons)
    {

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v14.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "messaging_product": "whatsapp",
                "recipient_type": "individual",
                "to": "' . $phone . '",
                "type": "interactive",
                "interactive": {
                    "type": "button",
                    "body": {
                        "text": "' . $message . '"
                    },
                    "action": {
                        "buttons": ' . json_encode($buttons) . '
                    }
                }
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }



    public function sendMultiProductMessage($phone, $header, $body, $footer, $catalogId, $sections)
    {

        $options = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "product_list",
                "header" => [
                    "type" => "text",
                    "text" => $header
                ],
                "body" => [
                    "text" => $body,
                ],
                "footer" => [
                    "text" => $footer
                ],
                "action" => [
                    "catalog_id" => $catalogId,
                    "sections" => $sections
                ]
            ]
        ];


        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v14.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }

    public function sendProductMessage($phone, $catalogId, $productRetailerId, $body = null, $footer = null)
    {
        $options = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "product",
                "body" => [
                    "text" => $body,
                ],
                "footer" => [
                    "text" => $footer
                ],
                "action" => [
                    "catalog_id" => $catalogId,
                    "product_retailer_id" => $productRetailerId
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }


    public function sendReplyToMessage($phone, $messageId, $message)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "messaging_product": "whatsapp",
                    "context": {
                        "message_id": "' . $messageId . '"
                    },
                    "to": "' . $phone . '",
                    "type": "text",
                    "text": {
                        "preview_url": false,
                        "body": "' . $message . '"
                    }
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }

    public function sendReaction($phone, $messageId, $emoji)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://graph.facebook.com/v16.0/" . $this->phoneNumberId . "/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{
          "messaging_product": "whatsapp",
          "recipient_type": "individual",
          "to": "' . $phone . '",
          "type": "reaction",
          "reaction": {
            "message_id": "' . $messageId . '",
            "emoji": "' . $emoji . '"
          }
        }',
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseObj = json_decode($response);

        return $responseObj;
    }

    public function sendImageMessage($phone, $imageId)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "messaging_product": "whatsapp",
                "recipient_type": "individual",
                "to": "' . $phone . '",
                "type": "image",
                "image": {
                    "id": "' . $imageId . '"
                }
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);

        return $responseObj;
    }

    public function sendLocationMessage($phone, $longitude, $latitude, $locationName, $locationAddress)
    {
        $options = [
            "messaging_product" => "whatsapp",
            "to" => $phone,
            "type" => "location",
            "location" => [
                "longitude" => $longitude,
                "latitude" => $latitude,
                "name" => $locationName,
                "address" => $locationAddress
            ]
        ];

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseObj = json_decode($response);
        return $responseObj;
    }

    public function sendContactsMessage($phone, $contacts)
    {
        $options = [
            "messaging_product" => "whatsapp",
            "to" => $phone,
            "type" => "contacts",
            "contacts" => $contacts
        ];

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }



    public function sendTextTemplateMessage($phone, $templateName, $languageCode, $text)
    {
        // Ensure $text is an array
        if (!is_array($text)) {
            return (object) [
                'error' => [
                    'message' => 'Text parameter should be an array'
                ]
            ];
        }

        // Construct the parameters for the template components
        $parameters = [];
        foreach ($text as $value) {
            if (!empty(trim($value))) {  // Check if the value is not empty
                $parameters[] = [
                    "type" => "text",
                    "text" => $value
                ];
            }
        }

        $options = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $templateName,
                "language" => [
                    "code" => $languageCode
                ]
            ]
        ];

        // Add parameters to the components if it's not empty
        if (!empty($parameters)) {
            $options["template"]["components"] = [
                [
                    "type" => "body",
                    "parameters" => $parameters
                ]
            ];
        }


        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($options),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $responseObj = json_decode($response);
        return $responseObj;
    }



    /**
     * Uploads media to WhatsApp using the Graph API.
     *
     * @param string $phoneNumberId The phone number ID.
     * @param string $filePath The path to the file to be uploaded.
     * @param string $mimeType The MIME type of the file to be uploaded.
     * @return mixed The response from the API, parsed as a JSON object.
     */
    function uploadMedia($filePath, $mimeType)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v16.0/' . $this->phoneNumberId . '/media',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'file' => new CURLFile($filePath, $mimeType),
                    'type' => $mimeType,
                    'messaging_product' => 'whatsapp'
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}
