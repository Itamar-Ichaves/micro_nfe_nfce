<?php

use App\Http\Controllers\CertificadoDigitalController;
use App\Http\Controllers\DanfeNfeController;
use App\Http\Controllers\Nfce\NFCeController;
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
Route::get('danfe', [DanfeNfeController::class, 'danfe']);
Route::get('simulardanfe', [DanfeNfeController::class, 'simulardanfe']);
Route::post('getNfesForCompany', [NFeController::class, 'getNfesForCompany']);
Route::post('/cancelar/nfe', [NfeController::class, 'cancelarNfe']);
});


/**
 * Routes NFC-e
 */
Route::group(['prefix'=> 'nfce'], function () {
    Route::post('transmitir', [NFCeController::class, '']);
});

/** 
 * Routes Certificado Digital
 */
Route::post('certificado', [CertificadoDigitalController::class, 'salvarCertificado']);
Route::post('certificado/get', [CertificadoDigitalController::class, 'consultarCertificado']);