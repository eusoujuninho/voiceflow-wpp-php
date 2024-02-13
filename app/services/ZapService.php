<?php

namespace App\Services;

class ZapService {

    private static $baseUrl = "http://localhost:8080";
    private static $instance;
    private static $mobilePhone;

    public static function setInstance($instance) {
        self::$instance = $instance;
    }

    public static function setMobilePhone($mobilePhone) {
        self::$mobilePhone = $mobilePhone;
    }

    public static function sendImageMedia($imageUrl, $caption, $delay = 1200, $presence = 'composing') {
        $apiUrl = self::$baseUrl . "/message/sendImageMedia/" . self::$instance;
        $data = [
            'number' => self::$mobilePhone,
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

    public static function sendText($text, $delay = 1200, $presence = 'composing') {
        $apiUrl = self::$baseUrl . "/message/sendText/" . self::$instance;
        $data = [
            'number' => self::$mobilePhone,
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
