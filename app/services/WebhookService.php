<?php

namespace App\Services;

use App\Models\Message;
use App\Services\VoiceflowService;
use App\Helpers\WebhookDataProcessor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WebhookService {
    use WebhookDataProcessor;

    private $voiceflowService;
    private $httpClient;

    public function __construct(VoiceflowService $voiceflowService) {
        $this->voiceflowService = $voiceflowService;
        $this->httpClient = new Client();
        error_log('WebhookService instantiated. VoiceflowService has been injected.');
    }

    private function sendToQStash($vfResponseJson) {
        $url = getenv('QSTASH_URL') . getenv('VOICEFLOW_RESPONSE_PROCESSOR_URL');
        $token = getenv('QSTASH_TOKEN');

        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Upstash-Delay' => '1000s',
                    'Content-Type' => 'application/json'
                ],
                'body' => $vfResponseJson
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            error_log('Guzzle HTTP client exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function upsertMessages($data) {
        error_log('Starting the upsertMessages process.');

        try {
            $data = $this->processEvolutionApiRequest($data);

            $phoneNumber = $data['mobilePhone'];
            $message = $data['message'];
            $noReply = false;

            error_log("Received data for upsertMessages. Phone number: {$phoneNumber}, Message: {$message}");

            $this->voiceflowService->setUserIdFromMobilePhone($phoneNumber);
            $voiceflowResponse = $noReply ? $this->voiceflowService->noReply() : $this->voiceflowService->interactWithText($message);
            $vfResponse = $this->filterVoiceflowResponse($voiceflowResponse);

            error_log("Voiceflow response: " . print_r($voiceflowResponse, true));

            $vfResponseJson = json_encode($vfResponse);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Falha ao codificar vfResponse em JSON');
            }

            return $this->sendToQStash($vfResponseJson);
        } catch (\Exception $e) {
            error_log('Exception caught in upsertMessages: ' . $e->getMessage());
            return json_encode(['error' => $e->getMessage()], 500);
        }
    }

    public function handle($request) {
        $pathInfo = $request->getPathInfo();
        $eventParts = explode('/', $pathInfo);
        $event = end($eventParts);

        error_log("Webhook received for event: {$event}");

        if ($event === 'messages-upsert') {
            return $this->upsertMessages($request->all());
        } else {
            error_log('No defined action for this event.');
            return json_encode(['message' => 'Nenhuma ação definida para este evento']);
        }
    }
}
