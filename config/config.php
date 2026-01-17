<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    /*
     * Your WhatsApp Cloud API Phone Number ID.
     */
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

    /*
     * Your WhatsApp Cloud API access token.
     */
    'token' => env('WHATSAPP_TOKEN'),

    /*
     * Default header text used in interactive list messages.
     */
    'header_text' => env('WHATSAPP_HEADER_TEXT', env('APP_NAME', 'Laravel')),
];