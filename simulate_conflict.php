<?php
// Script to simulate a background update by another user
use App\Models\Book;

$book = Book::first();
$book->update(['title' => $book->title . ' (Updated by User B)']);
echo "Updated Book {$book->id} to '{$book->title}'\n";
