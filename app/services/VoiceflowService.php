<?php

namespace App\Services;

class VoiceflowService {

    private $apiKey;
    private $versionId = 'development';
    private $projectId = null;
    private $userId;
    private $headers = [];
    private $dmBaseUrl = 'https://general-runtime.voiceflow.com';
    private $session = 0;

    public function __construct($userId = null) {
        if($userId) {
            $this->userId = $userId;
        }

        $this->headers = [
            'Content-Type: application/json',
            'Authorization: ' . $this->apiKey
        ];
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getUserId() {
        return $this->getUserId();
    }

    private function rndID() {
        // Random Number Generator
        $randomNo = rand(1, 1000);
        // get Timestamp
        $timestamp = time();
        // get Day
        $weekday = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $day = $weekday[date('w')];
        return $randomNo . $day . $timestamp;
    }
      

    private function handleResponse($trace) {
        $type = $trace['type'];
        switch($type) {
            case 'text':
            case 'speak':
                return [
                    'message' => $trace['payload']['message'],
                    'type' => 'text'
                ];
            break;

            case 'image':
                return [
                    'image_url' => $trace['payload']['url'],
                    'type' => 'image'
                ];

            case 'end':
                return false;
        }
    }

    private function makeRequest($action = 'launch', $data) {
    
        $guzzle = new \GuzzleHttp\Client();
        $request = $guzzle->request('POST', "{$this->dmBaseUrl}/state/user/{$this->userId}/{$action}", [
            'headers' => $this->headers,
            'json' => $data,
            'versionID' => $this->versionId,
            'sessionID' => $this->session
        ]);
        
        return json_decode($request->getBody(), true);
    }

    public function launch($data) {
        return $this->makeRequest('launch', $data);
    }

    private function generateSession() {
        $this->session = $this->versionId . $this->rndId();
    }

    private function updateState($vars = []) {
        $guzzle = new \GuzzleHttp\Client();
        $request = $guzzle->request('PATH', "{$this->dmBaseUrl}/state/user/{$this->userId}/variables", [
            'headers' => $this->headers,
            'json' => $vars
        ]);
        return json_decode($request->getBody(), true);
    }

    public function interact($data, $phoneNumber, $userName) {

        if(!$this->session) {
            $this->generateSession();
        }

        $this->updateState(['user_id' => $this->userId, 'user_name' => $userName, 'mobile_phone' => $mobilePhone]);

        $response = $this->makeRequest('interact', $data);

        $data = [];

        foreach($response['data'] as $trace) {
            $data[] = $this->handleResponse($trace);
        }

        return $data;

    }

}