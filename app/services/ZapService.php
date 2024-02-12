<?php

namespace App\Services;

class ZapService {

    private $baseUrl = "http://localhost:8080";
    private $instance;
    private $mobilePhone;

    public function __construct($instance = null) {
        if($instance) {
            $this->instance = $instance;
        }       
    }

    public function setInstance() {
        $this->instance = $instance;
        return $this;
    }

    public function setMobilePhone($mobilePhone) {
        $this->mobilePhone = $mobilePhone;
        return $this;
    }

    public function sendImageMedia($imageUrl, $caption, $delay = 1200, $presence = 'composing') {
        $apiUrl = "{$this->baseUrl}/message/sendImageMedia/{$this->instance}";
        $data = [
            'number' => $this->mobilePhone,
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

    public function sendText($text, $delay = 1200, $presence = 'composing') {
        $apiUrl = "{$this->baseUrl}/message/sendText/{$this->instance}";
        $data = [
            'number' => $this->mobilePhone,
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