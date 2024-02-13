<?php

namespace App\Helpers;

trait WebhookDataProcessor {

    public function processVoiceflowResponse($voiceflowResponse, $phoneNumber) {
        foreach ($voiceflowResponse as $step) {
            $type = $step['type'];
            $payload = $step['payload'] ?? [];
            $message = $payload['message'] ?? '';
            $delay = $payload['delay'] ?? 1000;

            error_log("Processing Voiceflow response step: Type: {$type}, Delay: {$delay}, Message: {$message}");

            return [
                'type' => $type,
                'payload' => $payload,
                'message' => $message,
                'delay' => $delay
            ];
        }
    }

    public function processEvolutionApiRequest($data) {
        // Inicializa a resposta
        $response = [];
        
        if ($data['data']['messageType'] === 'conversation') {
            // Trata mensagens de texto
            $mobilePhone = explode('@', $data['data']['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $message = $data['data']['message']['conversation'];
    
            $response = [
                'mobilePhone' => $mobilePhone,
                'senderName' => $senderName,
                'message' => $message,
            ];
        } elseif ($data['data']['messageType'] === 'audioMessage') {
            // Trata mensagens de áudio
            $mobilePhone = explode('@', $data['data']['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $audioUrl = $data['data']['message']['audioMessage']['url'];
    
            $response = [
                'mobilePhone' => $mobilePhone,
                'senderName' => $senderName,
                'message' => 'Audio message received.',
                'audioUrl' => $audioUrl,
            ];
        } elseif ($data['data']['messageType'] === 'imageMessage') {
            // Trata mensagens de imagem
            $mobilePhone = explode('@', $data['data']['key']['remoteJid'])[0];
            $senderName = $data['pushName'];
            $imageUrl = $data['data']['message']['imageMessage']['url'];
            $imageThumbnail = base64_encode($data['data']['message']['imageMessage']['jpegThumbnail']); // Codifica a miniatura em base64
    
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
