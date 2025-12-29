<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\BookChild;

class BookObserver
{
    /**
     * Handle the Book "deleted" event.
     */
    public function deleted(Book $book): void
    {
        // Cascade delete all MongoDB children when a Book is deleted from MySQL
        BookChild::where('book_id', $book->id)->delete();
    }
}
