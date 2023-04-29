<?php

namespace Dogfromthemoon\LaravelWhatsappSender;

use CURLFile;

class LaravelWhatsappSender
{

    protected $phoneNumberId;
    protected $token;

    public function __construct($phoneNumberId = null, $token = null)
    {
        $this->phoneNumberId = $phoneNumberId ?: env('WHATSAPP_PHONE_NUMBER_ID');
        $this->token = $token ?: env('WHATSAPP_TOKEN');
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
                    "text" => env('APP_NAME')
                ],
                "body" => [
                    "text" => $message,
                ],
                "footer" => [
                    "text" => "Venco Tickets"
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
        $options = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $templateName,
                "language" => [
                    "code" => $languageCode
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $text
                            ]
                        ]
                    ]
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
