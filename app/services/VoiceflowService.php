<?php

namespace App\Services;

class VoiceflowService {

    private $apiKey;
    private $versionId = 'development';
    private $projectId = null;
    private $userId;
    private $headers = [];
    private $dmBaseurl = 'https://general-runtime.voiceflow.com';
    private $session = 0;
    private $state = ['variables' => []];
    private $config = [
        'tts' => false,
        'stripSSML' => true,
        'stopAll' => true,
        'excludeTypes' => [
            'block',
            'debug',
            'flow'
        ]
        ];

    public function __construct($userId = null) {
        if($userId) {
            $this->userId = $userId;
        }

        $this->apiKey = getenv('VOICEFLOW_DM_API_KEY');
        $this->projectId = getenv('VOICEFLOW_PROJECT_ID');
        $this->versionId = getenv('VOICEFLOW_VERSION_ID');
        
        $this->headers = [
            'Authorization' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'versionID' => $this->versionId
        ];

        // var_dump($this->headers);
        // exit;
    }

    public function setConfig($config) {
        $this->config = array_merge($this->config, $config);
    }

    public function setState($state) {
        $this->state = array_merge($this->state, $state);
    }

    private function generateSession() {
        $this->session = $this->versionId . $this->rndId();
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setUserIdFromMobilePhone($mobilePhone) {
        $this->userId = preg_replace('/\D/', '', $mobilePhone);
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

    private function makeRequest($method, $action, $data = []) {
        try {

            
            $endpoint = !empty($action) ? 'interact' : '';
            
            $data['config'] = $data['config'] ?? $this->config;
            $data['state'] = $data['state'] ?? $this->state;    

            if($action != 'launch') {
                $data['action']['type'] = $data['launch'] ?? 'launch';
            }

            $url = "{$this->dmBaseurl}/state/user/{$this->userId}/{$endpoint}";

            //var_dump($method, $url);
            // exit;

            $guzzle = new \GuzzleHttp\Client();
            $request = $guzzle->request($method, $url, [
                'headers' => $this->headers,
                'json' => $data
            ]);

            $response = json_decode($request->getBody(), true);

            return $response;

        } catch(\Exception $e) {
            echo '<pre>';
            var_dump($e);
            exit;
            throw new \Exception($e->getMessage());
        }
    }

    public function launch() {
        return $this->makeRequest('POST', 'launch', []);
    }

    private function updateState($vars = []) {
        $state = $this->makeRequest('PATH', 'variables', $vars);
        return $state;
    }

    private function fetchState() {
        $state = $this->makeRequest('GET', '', []);
        $this->setState($state);
        return $this->state;
    }

    public function sendText($text) {
        $data = [
            'action' => [
                'type' => 'text',
                'payload' => $text,
            ]
        ];
        $interact = $this->interact($data);

        return $interact;
    }

    private function interact($data) {
        
        $this->generateSession();

        $state = $this->fetchState();
        if(count($state['variables']) == 0) {
            $this->launch();
        }

        $response = $this->makeRequest('POST', 'interact');
        
        return $response;
    }

}