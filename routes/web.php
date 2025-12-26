<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DeletionController;
use App\Http\Controllers\ManuscriptController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return Inertia\Inertia::render('Auth/Login');
})->name('login');

Route::middleware(['auth'])->group(function () {
    // Web Resource Routes
    Route::resource('books', BookController::class);
    Route::resource('audios', AudioController::class);
    Route::resource('videos', VideoController::class);
    Route::resource('manuscripts', ManuscriptController::class);

    // Taxonomy and Organization
    Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('categories', CategoryController::class);

    Route::post('tags/bulk-destroy', [TagController::class, 'bulkDestroy'])->name('tags.bulk-destroy');
    Route::resource('tags', TagController::class);

    Route::resource('collections', CollectionController::class);
    Route::resource('series', SeriesController::class);

    // Metadata and Logs
    Route::resource('activities', ActivityController::class)->only(['index', 'show']);
    Route::resource('comments', CommentController::class);
    Route::resource('notes', NoteController::class);
    Route::resource('deletions', DeletionController::class)->only(['index', 'show']);
});
