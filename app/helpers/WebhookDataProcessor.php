<?php

namespace App\Helpers;

trait WebhookDataProcessor {

    public function filterVoiceflowResponse($voiceflowResponse) {
        
        $response = [];

        foreach ($voiceflowResponse as $step) {
            $type = $step['type'];
            $payload = $step['payload'] ?? [];
            $message = $payload['message'] ?? '';
            $delay = $payload['delay'] ?? 1000;

            error_log("Processing Voiceflow response step: Type: {$type}, Delay: {$delay}, Message: {$message}");

            $response[] = [
                'type' => $type,
                'payload' => $payload,
                'message' => $message,
                'delay' => $delay
            ];
        }

        return $response;
    }

    public function processEvolutionApiRequest($request) {
        // Inicializa a resposta
        $response = [];

        $data = $request->get('data');
        
        if ($data['messageType'] === 'conversation') {
            // Trata mensagens de texto
            $mobilePhone = explode('@', $data['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $message = $data['message']['conversation'];
    
            $response = [
                'mobilePhone' => $mobilePhone,
                'senderName' => $senderName,
                'message' => $message,
            ];
        } elseif ($data['messageType'] === 'audioMessage') {
            // Trata mensagens de áudio
            $mobilePhone = explode('@', $data['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $audioUrl = $data['message']['audioMessage']['url'];
    
            $response = [
                'mobilePhone' => $mobilePhone,
                'senderName' => $senderName,
                'message' => 'Audio message received.',
                'audioUrl' => $audioUrl,
            ];
        } elseif ($data['messageType'] === 'imageMessage') {
            // Trata mensagens de imagem
            $mobilePhone = explode('@', $data['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $imageUrl = $data['message']['imageMessage']['url'];
            $imageThumbnail = base64_encode($data['message']['imageMessage']['jpegThumbnail']); // Codifica a miniatura em base64
    
            $response = [
                'mobilePhone' => $mobilePhone,
                'senderName' => $senderName,
                'message' => 'Image message received.',
                'imageUrl' => $imageUrl,
                'imageThumbnailBase64' => $imageThumbnail,
            ];
        } else {
            // Retorna vazio para outros tipos de mensagens não suportados
            $response = [];
        }
    
        return $response;
    }
    

}
