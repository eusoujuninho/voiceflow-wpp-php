<?php

namespace App\Services;

class ZapService {

    private static $baseUrl;
    private static $instance;

    public static function init() {
        self::$baseUrl = getenv('EVOLUTION_API_URL');
        self::$instance = getenv('EVOLUTION_API_INSTANCE_NAME');
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
        $request = $guzzle->request('POST', $apiUrl, [
            'json' => $data
        ]);

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
        $request = $guzzle->request('POST', $apiUrl, [
            'json' => $data
        ]);

        $response = json_decode($request->getBody(), true);

        return $response;
    }
}