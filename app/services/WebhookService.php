<?php

namespace App\Services;

use App\Services\ZapService;
use App\Services\VoiceflowService;

class WebhookService {

    private $voiceflowService;

    public function __construct(VoiceflowService $voiceflowService) {
        $this->voiceflowService = $voiceflowService;
        error_log('WebhookService instantiated. VoiceflowService has been injected.');
    }

    public function upsertMessages($data) {
        error_log('Starting the upsertMessages process.');

        try {
            $phoneNumber = $data->get('phone_number') ?? '1234';
            $message = $data->get('message') ?? 'Olá!';
            error_log("Received data for upsertMessages. Phone number: {$phoneNumber}, Message: {$message}");

            // Enviar mensagem para o voiceflow
            $this->voiceflowService->setUserIdFromMobilePhone($phoneNumber);
            $voiceflowResponse = $this->voiceflowService->sendText($message);
            error_log('Request sent to VoiceflowService.');

            foreach($voiceflowResponse as $step) {
                $type = $step['type'];
                $payload = $step['payload'] ?? [];
                $message = $payload['message'] ?? '';
                $delay = $payload['delay'] ?? 1000;

                error_log("Processing Voiceflow response step: Type: {$type}, Delay: {$delay}, Message: {$message}");

                if($delay === 1000) {
                    ZapService::init();
                    error_log('ZapService initialized for message sending.');
                    ZapService::sendText($phoneNumber, $message);
                    error_log("Message sent to {$phoneNumber} via ZapService.");
                }
            }

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
