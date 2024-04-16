<?php

namespace App\Models;

class Message {
    public $number;
    public $textMessage;
    public $mediaMessage;
    public $audioMessage;
    public $options;

    public function __construct($number, $options = []) {
        $this->number = $number;
        $this->options = array_merge(
            [
                'delay' => 1200, // Default delay
                'presence' => 'composing' // Default presence
            ],
            $options
        );
    }

    public function setOption($key, $value) {
        $this->options[$key] = $value;
    }

    public function setDelay($delay) {
        $this->setOption('delay', $delay);
    }

    public function setPresence($presence) {
        $this->setOption('presence', $presence);
    }

    // Método para definir uma mensagem de texto
    public function setText($text) {
        $this->textMessage = ['text' => $text];
    }

    // Método para definir uma mensagem de mídia
    public function setMedia($mediaType, $mediaUrl, $caption = null, $fileName = null) {
        $this->mediaMessage = [
            'mediatype' => $mediaType,
            'media' => $mediaUrl,
            'caption' => $caption
        ];
        if ($fileName) {
            $this->mediaMessage['fileName'] = $fileName;
        }
    }

    // Método para definir uma mensagem de áudio
    public function setAudio($audioUrl, $isNarrated = false) {
        $this->audioMessage = ['audio' => $audioUrl];
        
        if ($isNarrated) {
            $this->setPresence('recording');
            $this->setOption('encoding', true);
        }
    }

    // Método para preparar a mensagem para envio
    public function preparePayload() {
        $payload = [
            'number' => $this->number,
            'options' => $this->options
        ];

        if (isset($this->textMessage)) {
            $payload['textMessage'] = $this->textMessage;
        }

        if (isset($this->mediaMessage)) {
            $payload['mediaMessage'] = $this->mediaMessage;
        }

        if (isset($this->audioMessage)) {
            $payload['audioMessage'] = $this->audioMessage;
        }

        return $payload;
    }

    // Método para converter a mensagem para JSON
    public function toJSON() {
        return json_encode($this->preparePayload(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
