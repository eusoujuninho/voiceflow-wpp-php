<?php

namespace App\Services;

use App\Services\ZapService;
use App\Services\VoiceflowService;

class WebhookService {

    private $zapService;
    private $voiceflowService;

    public function __construct(ZapService $zapService, VoiceflowService $voiceflowService) {
        $this->zapService = $zapService;
        $this->voiceflowService = $voiceflowService;
    }

    public function upsertMessages($data) {
        // Agora você pode acessar $this->zapService aqui, pois não estamos mais em um contexto estático
        // Supondo que $data é usado de alguma forma aqui. Exemplo:

        try {
            $phoneNumber = $data->get('phone_number') ?? '5511999999999';
        $message = $data->get('message') ?? 'Olá!';

        // Enviar mensagem para o voiceflow
        $this->voiceflowService->setUserIdFromMobilePhone($phoneNumber);

        $send = $this->voiceflowService->sendText($message);
            
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        // Aqui você poderia usar $this->zapService para enviar a mensagem
        // Exemplo: $this->zapService->sendText($phoneNumber, $message);

        // Retorno simplificado para este exemplo
        return json_encode([
            'success' => true,
            'message' => 'Mensagens atualizadas com sucesso!',
            'event' => 'messages-upsert'
        ]);
    }

    public function handle($request) {
        $pathInfo = $request->getPathInfo();
        $eventParts = explode('/', $pathInfo);
        $event = end($eventParts); // Pega o último elemento do array

        // Simplicidade: Assumindo que o evento é sempre 'messages-upsert'
        if ($event === 'messages-upsert') {
            return $this->upsertMessages($request); // Supondo que $request->all() traz os dados necessários
        } else {
            // Retorno simplificado para outros casos
            return json_encode(['message' => 'Nenhuma ação definida para este evento']);
        }
    }

}