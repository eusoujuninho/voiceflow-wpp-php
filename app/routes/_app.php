<?php

use App\Services\WebhookService;
use App\Services\ZapService;
use App\Services\VoiceflowService;

app()->get('/', function () {
    response()->json(['message' => 'Congrats!! You\'re on Leaf API']);
});

app()->all('/webhook', function() {
    response()->json(['message' => 'Webhook is working!']);
});

app()->all('/webhook/{event}', function($event) {
    $webhookService = new WebhookService(new VoiceflowService());
    $request = request();
    return $webhookService->handle($request);
});