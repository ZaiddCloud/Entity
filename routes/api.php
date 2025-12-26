<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DeletionController;
use App\Http\Controllers\Api\ManuscriptController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->name('api.')->group(function () {
    // Entity Resource Routes
    Route::apiResource('books', BookController::class);
    Route::apiResource('audios', AudioController::class);
    Route::apiResource('videos', VideoController::class);
    Route::apiResource('manuscripts', ManuscriptController::class);

    // Taxonomy and Organization
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('collections', CollectionController::class);
    Route::apiResource('series', SeriesController::class);

    // Metadata and Logs
    Route::apiResource('activities', ActivityController::class)->only(['index', 'show']);
    Route::apiResource('comments', CommentController::class);
    Route::apiResource('notes', NoteController::class);
    Route::apiResource('deletions', DeletionController::class)->only(['index', 'show']);
});
