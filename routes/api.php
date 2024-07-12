<?php

use App\Http\Controllers\CertificadoDigitalController;
use App\Http\Controllers\Nfe\NFeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Routes NF-e
 */
Route::group(['prefix'=> 'nfe'], function () {
Route::post('transmitir', [NFeController::class, 'transmitir']);
});


/**
 * Routes NFC-e
 */

/**
 * Routes Certificado Digital
 */
Route::post('certificado_digital', [CertificadoDigitalController::class, 'salvarCertificado']);
