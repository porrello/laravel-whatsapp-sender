<?php

namespace Dogfromthemoon\LaravelWhatsappSender;

class LaravelWhatsappSender
{
    /**
     * Send a WhatsApp text message to a phone number
     *
     * @param string $phone The phone number of the recipient (in international format, e.g. "+1234567890")
     * @param string $message The message to send
     * @return bool Whether the message was sent successfully
     */
    public function sendTextMessage($phone, $message)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://graph.facebook.com/v14.0/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages',
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
                    'Authorization: Bearer ' . env('WHATSAPP_TOKEN')
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);

        $responseObj = json_decode($response);
        return $responseObj;
    }
}
