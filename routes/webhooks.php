<?php

use App\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyWebhookSignature::class)->group(function () {
    Route::post('/absensi', [WebhookController::class, 'absensi']);
});
