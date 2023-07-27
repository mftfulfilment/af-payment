<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VoiceApiController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/**
 * Voice service
 */

 Route::post('v1/handle-callback',[VoiceApiController::class, 'handleCallBack']);
 Route::post('v1/handle-event',[VoiceApiController::class, 'handleEvent']);


 Route::post('v1/transfer-call',[VoiceApiController::class, 'transferCall']);
 Route::post('v1/dequeue-call',[VoiceApiController::class, 'dequeueCall']);
 Route::post('v1/generate-token',[VoiceApiController::class, 'generateToken']);


