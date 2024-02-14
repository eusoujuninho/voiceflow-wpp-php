<?php

namespace App\Services;

use App\Services\ZapService;
use App\Services\VoiceflowService;
use App\Helpers\WebhookDataProcessor;

class WebhookService {

    use WebhookDataProcessor;

    private $voiceflowService;

    public function __construct(VoiceflowService $voiceflowService) {
        $this->voiceflowService = $voiceflowService;
        error_log('WebhookService instantiated. VoiceflowService has been injected.');
    }

    public function upsertMessages($data) {
        error_log('Starting the upsertMessages process.');

        try {
            $data = $this->processEvolutionApiRequest($data);

            $phoneNumber = $data['mobilePhone'];
            $message = $data['message'];

            error_log("Received data for upsertMessages. Phone number: {$phoneNumber}, Message: {$message}");

            // Enviar mensagem para o voiceflow
            $this->voiceflowService->setUserIdFromMobilePhone($phoneNumber);
            $voiceflowResponse = $this->voiceflowService->sendText($message);

            $voiceflowData = $this->processVoiceflowResponse($voiceflowResponse);

            var_dump('voiceflow response', $voiceflowData);

            $type = $voiceflowData['type'];
            $payload = $voiceflowData['payload'];
            $message = $voiceflowData['message'];
            $delay = $voiceflowData['delay'];

            error_log("Processing Voiceflow response step: Type: {$type}, Delay: {$delay}, Message: {$message}");

            ZapService::init();
            error_log('ZapService initialized for message sending.');
            ZapService::sendText($phoneNumber, $message);
            error_log("Message sent to {$phoneNumber} via ZapService.");

        } catch(\Exception $e) {
            error_log('Exception caught in upsertMessages: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }

        error_log('upsertMessages process completed successfully.');
        return json_encode([
            'success' => true,
            'message' => 'Mensagens atualizadas com sucesso!',
            'event' => 'messages-upsert'
        ]);
    }

    public function handle($request) {
        $pathInfo = $request->getPathInfo();
        $eventParts = explode('/', $pathInfo);
        $event = end($eventParts);

        error_log("Webhook received for event: {$event}");

        if ($event === 'messages-upsert') {
            return $this->upsertMessages($request);
        } else {
            error_log('No defined action for this event.');
            return json_encode(['message' => 'Nenhuma ação definida para este evento']);
        }
    }

}
