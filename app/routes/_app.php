<?php

app()->get('/', function () {
    response()->json(['message' => 'Congrats!! You\'re on Leaf API']);
});

app()->get('/webhook', function() {
    response()->json(['message' => 'Webhook is working!']);
});

app()->get('/webhook/messages-upsert', function() {
    response()->json(['success' => true]);
});