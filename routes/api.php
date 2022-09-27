<?php

use App\Http\Controllers\Api\v1\UploadController;
use App\Http\Controllers\Api\v1\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResources([
    'desks' => DeskController::class
]);

Route::post('/uploadXLSX', [UploadController::class,'fileXLSX']);

Route::post('/uploadXML', [UploadController::class,'fileXML']);

Route::post('/excel', [UploadController::class,'genexcel']);

Route::post('/word', [UploadController::class,'genword']);

Route::post('/getsitedata',[UploadController::class,'getSiteData']);

Route::post('/mail', 'MailController@send');

//Route::post('/mail', [MailController::class,'send']);