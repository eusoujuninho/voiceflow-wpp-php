<?php

namespace App\Services;

class ZapService {

    private static $baseUrl;
    private static $instance;
    private static $token;

    public static function init() {
        self::$baseUrl = 'https://apps-evolutionapi.s12n1h.easypanel.host';
        self::$instance = 'Oracao';
        self::$token = 'fYXmfeZRpc3DCqTs8HytjrzUlRX3UpiO';
    }

    private static function getHeaders() {
        return [
            'headers' => [
                'apikey' => self::$token
            ]
        ];
    }

    public static function sendImageMedia($mobilePhone, $imageUrl, $caption, $delay = 1200, $presence = 'composing') {
        $apiUrl = self::$baseUrl . "/message/sendImageMedia/" . self::$instance;
        $data = [
            'number' => $mobilePhone,
            'options' => [
                'delay' => $delay,
                'presence' => $presence
            ],
            'mediaMessage' => [
                'mediatype' => 'image',
                'caption' => $caption,
                'media' => $imageUrl
            ]
        ];

        $guzzle = new \GuzzleHttp\Client();
        $request = $guzzle->request('POST', $apiUrl, array_merge(self::getHeaders(), [
            'json' => $data
        ]));

        $response = json_decode($request->getBody(), true);

        return $response;
    }

    public static function sendText($mobilePhone, $text, $delay = 1200, $presence = 'composing') {
        $apiUrl = self::$baseUrl . "/message/sendText/" . self::$instance;
        $data = [
            'number' => $mobilePhone,
            'options' => [
                'delay' => $delay,
                'presence' => $presence
            ],
            'textMessage' => [
                'text' => $text
            ]
        ];

        $guzzle = new \GuzzleHttp\Client();
        $request = $guzzle->request('POST', $apiUrl, array_merge(self::getHeaders(), [
            'json' => $data
        ]));

        $response = json_decode($request->getBody(), true);

        return $response;
    }
}
