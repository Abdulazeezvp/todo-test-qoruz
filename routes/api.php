<?php

use App\Http\Controllers\Todo\TaskController;
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
Route::controller(TaskController::class, function () {

    Route::get('get/parent-tasks', 'create');
    Route::post('tasks/store', 'store');

    Route::delete('tasks/{id}/destroy', 'destroy');

    //completed url
    Route::put('tasks/{id}/completed', 'completed');
});
