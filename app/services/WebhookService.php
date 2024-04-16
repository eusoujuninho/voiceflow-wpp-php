<?php

namespace App\Services;

use App\Models\Message; // Certifique-se de que o modelo Message esteja incluído corretamente.
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
            $noReply = false;

            error_log("Received data for upsertMessages. Phone number: {$phoneNumber}, Message: {$message}");

            // Enviar mensagem para o Voiceflow
            $this->voiceflowService->setUserIdFromMobilePhone($phoneNumber);

            $voiceflowResponse = $noReply ? $this->voiceflowService->noReply() : $this->voiceflowService->interactWithText($message);
            $vfResponse = $this->processVoiceflowResponse($voiceflowResponse); // Updated to use interactWithText

            error_log("Voiceflow response: " . print_r($voiceflowResponse, true));

            echo '<pre>';

            var_dump($vfResponse);
            exit;

            if (isset($voiceflowResponse['type']) && $voiceflowResponse['type'] === 'text') {
                $processedMessage = $voiceflowResponse['payload']['message'];
                error_log("Processing Voiceflow response: Message: {$processedMessage}");

                // Cria o objeto Message e prepara o payload
                $messageObject = new Message($phoneNumber);
                $messageObject->setText($processedMessage);
                $messagePayload = $messageObject->preparePayload();

                error_log("Prepared message payload: " . print_r($messagePayload, true));

                // Retorna o payload como resposta JSON
                return json_encode([
                    'success' => true,
                    'message' => 'Mensagem preparada com sucesso!',
                    'payload' => $messagePayload
                ]);

            } else {
                error_log('No valid response type from Voiceflow to process.');
                return json_encode(['message' => 'Tipo de resposta inválido do Voiceflow']);
            }

        } catch(\Exception $e) {
            error_log('Exception caught in upsertMessages: ' . $e->getMessage());
            return json_encode(['message' => $e->getMessage()], 500);
        }
    }

    public function handle($request) {
        $pathInfo = $request->getPathInfo();
        $eventParts = explode('/', $pathInfo);
        $event = end($eventParts);

        error_log("Webhook received for event: {$event}");

        if ($event === 'messages-upsert') {
            return $this->upsertMessages($request); // Asumindo que $request->all() irá fornecer os dados necessários.
        } else {
            error_log('No defined action for this event.');
            return json_encode(['message' => 'Nenhuma ação definida para este evento']);
        }
    }
}
