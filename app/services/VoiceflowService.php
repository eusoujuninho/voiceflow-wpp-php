<?php

namespace App\Services;

class VoiceflowService {
    private $apiKey;
    private $versionId;
    private $projectId;
    private $userId;
    private $headers;
    private $dmBaseurl = 'https://general-runtime.voiceflow.com';
    private $session = 0;
    private $state = ['variables' => ['user_flow_step' => 'abandoned_cart']];
    private $config = [
        'tts' => false,
        'stripSSML' => true,
        'stopAll' => true,
        'excludeTypes' => []
    ];

    public function __construct($userId = null) {
        $this->userId = $userId;
        $this->apiKey = getenv('VOICEFLOW_DM_API_KEY');
        $this->projectId = getenv('VOICEFLOW_PROJECT_ID');
        $this->versionId = getenv('VOICEFLOW_VERSION_ID') ?: 'development';
        
        $this->headers = [
            'Authorization' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'versionID' => $this->versionId
        ];
    }

    public function setConfig(array $config) {
        $this->config = array_merge($this->config, $config);
    }

    public function setState(array $state) {
        $this->state = array_merge($this->state, $state);
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setUserIdFromMobilePhone($mobilePhone) {
        $this->userId = preg_replace('/\D/', '', $mobilePhone);
    }

    public function getUserId() {
        return $this->userId;
    }

    private function generateSession() {
        $this->session = $this->versionId . $this->generateRandomId();
    }

    private function generateRandomId() {
        return rand(1, 1000) . ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][date('w')] . time();
    }

    private function makeHttpRequest($method, $endpoint, array $data = []) {
        try {
            $url = "{$this->dmBaseurl}/state/user/{$this->userId}/{$endpoint}";
            $client = new \GuzzleHttp\Client();

            $response = $client->request($method, $url, [
                'headers' => $this->headers,
                'json' => array_merge(['config' => $this->config, 'state' => $this->state], $data)
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception("HTTP request failed: " . $e->getMessage());
        }
    }

    public function interactWithText($text) {
        if ($text === '/reset') {
            return $this->resetConversation();
        } elseif (strpos($text, '/setvars:') === 0) {
            return $this->setVariablesFromString(substr($text, 9));
        }

        return $this->makeHttpRequest('POST', 'interact', [
            'action' => ['type' => 'text', 'payload' => $text]
        ]);
    }

    private function setVariablesFromString($varsString) {
        $varsArray = [];
        $variables = explode(';', $varsString);
        foreach ($variables as $var) {
            list($key, $value) = explode('=', $var);
            $varsArray[$key] = $value;
        }
        $this->updateVariables($varsArray);
        return $this->state['variables']; // Return updated variables as response
    }

    public function noReply() {
        // Envie a ação de 'no-reply' para o Voiceflow
        $response = $this->makeHttpRequest('POST', 'interact', [
            'action' => [
                'type' => 'no-reply'
            ],
            // Adicione estado, configuração e outros metadados conforme necessário
        ]);

        return $response;
    }

    public function resetConversation() {
        $this->makeHttpRequest('DELETE', '');
        return [['type' => 'text', 'payload' => ['type' => 'message', 'message' => 'Resetting the conversation.']]];
    }

    public function updateVariables($variables) {
        return $this->makeHttpRequest('PATCH', '', ['variables' => $variables]);
    }

    public function retrieveState() {
        $state = $this->makeHttpRequest('GET', '');
        $this->setState($state);
        return $this->state;
    }
}
