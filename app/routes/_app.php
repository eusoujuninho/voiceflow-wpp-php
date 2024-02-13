<?php

use App\Services\WebhookService;
use App\Services\ZapService;
use App\Services\VoiceflowService;

app()->get('/', function () {
    response()->json(['message' => 'Congrats!! You\'re on Leaf API']);
});

app()->get('/webhook', function() {
    response()->json(['message' => 'Webhook is working!']);
});

app()->get('/webhook/{event}', function($event) {
    $webhookService = new WebhookService(new ZapService(), new VoiceflowService());
    $request = request();
    return $webhookService->handle($request);
});