<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'Task Manager API']));

// Task routes
Route::prefix('tasks')->group(function () {
    // IMPORTANT: /report must come before /{id} to avoid route conflicts
    Route::get('/report', [TaskController::class, 'report']);   // GET /api/tasks/report?date=YYYY-MM-DD

    Route::get('/',        [TaskController::class, 'index']);   // GET    /api/tasks
    Route::post('/',       [TaskController::class, 'store']);   // POST   /api/tasks

    Route::patch('/{id}/status', [TaskController::class, 'updateStatus']); // PATCH  /api/tasks/{id}/status
    Route::delete('/{id}',       [TaskController::class, 'destroy']);      // DELETE /api/tasks/{id}
});
