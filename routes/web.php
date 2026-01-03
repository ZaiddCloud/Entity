<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookContentController;
use App\Http\Controllers\BookerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DeletionController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ManuscriptController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'store']);

    Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'store']);
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [GlobalSearchController::class, 'index'])->name('search');
    
    // Editor Test Route
    Route::get('/editor-test', [App\Http\Controllers\EditorTestController::class, 'index'])->name('editor.test');


    // Web Resource Routes
    Route::resource('books', BookController::class);
    Route::resource('audios', AudioController::class);
    Route::resource('videos', VideoController::class);
    Route::resource('manuscripts', ManuscriptController::class);

    // Book Reader Routes
    Route::get('books/{book}/reader/{child?}', [BookContentController::class, 'show'])->name('books.reader');
    Route::get('book-contents/{child}', [BookContentController::class, 'getChildContent'])->name('book-contents.show');
// routes/web.php or routes/api.php

    Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
    // Taxonomy and Organization
    Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('categories', CategoryController::class);

    Route::post('tags/bulk-destroy', [TagController::class, 'bulkDestroy'])->name('tags.bulk-destroy');
    Route::resource('tags', TagController::class);

    Route::post('authors/bulk-destroy', [AuthorController::class, 'bulkDestroy'])->name('authors.bulk-destroy');
    Route::resource('authors', AuthorController::class);

    Route::post('publishers/bulk-destroy', [PublisherController::class, 'bulkDestroy'])->name('publishers.bulk-destroy');
    Route::resource('publishers', PublisherController::class);

    Route::post('bookers/bulk-destroy', [BookerController::class, 'bulkDestroy'])->name('bookers.bulk-destroy');
    Route::resource('bookers', BookerController::class);

    Route::post('topics/bulk-destroy', [TopicController::class, 'bulkDestroy'])->name('topics.bulk-destroy');
    Route::resource('topics', TopicController::class);

    Route::post('languages/bulk-destroy', [LanguageController::class, 'bulkDestroy'])->name('languages.bulk-destroy');
    Route::resource('languages', LanguageController::class);

    Route::post('shelves/bulk-destroy', [ShelfController::class, 'bulkDestroy'])->name('shelves.bulk-destroy');
    Route::resource('shelves', ShelfController::class);

    Route::resource('collections', CollectionController::class);
    Route::resource('series', SeriesController::class);

    // Metadata and Logs
    Route::resource('activities', ActivityController::class)->only(['index', 'show']);
    Route::resource('comments', CommentController::class);
    Route::resource('notes', NoteController::class);
    Route::resource('deletions', DeletionController::class)->only(['index', 'show']);
});
