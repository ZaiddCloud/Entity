<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Version;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookManagerService
{
    protected EntityManagerService $entityManager;

    public function __construct(EntityManagerService $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create a new book with its initial version and authors.
     *
     * @param array $data
     * @return Book
     * @throws ValidationException
     */
    public function createBook(array $data): Book
    {
        $this->validateBookData($data);

        return DB::transaction(function () use ($data) {
            // 1. Create the base Book entity using the generic service
            // We strip out version-specific data for the Book creation
            $bookData = [
                'title' => $data['title'],
                'type' => 'book',
                'description' => $data['description'] ?? null,
                // Legacy fields (if needed for now, though we are moving away)
                // 'author' => ... we handle authors via relation now
            ];

            /** @var Book $book */
            $book = $this->entityManager->create($bookData);

            // 2. Attach Authors
            if (!empty($data['author_ids'])) {
                $book->authors()->sync($data['author_ids']);
            }

            // 3. Create the Initial Version
            $versionData = [
                'book_id' => $book->id,
                'file_path' => $data['file_path'] ?? null,
                'publisher_id' => $data['publisher_id'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'pages' => $data['pages'] ?? null,
                'published_year' => $data['published_year'] ?? null,
                'edition_number' => $data['edition_number'] ?? 1,
                'format' => $data['format'] ?? 'pdf',
                'file_size' => $data['file_size'] ?? 0,
            ];

            // We can create a VersionManagerService later if this gets complex,
            // but for now direct creation is fine.
            Version::create($versionData);

            // Refresh to load relations
            return $book->fresh(['authors', 'versions']);
        });
    }

    /**
     * Update an existing book and its related entities.
     *
     * @param Book $book
     * @param array $data
     * @return Book
     * @throws ValidationException
     */
    public function updateBook(Book $book, array $data): Book
    {
        $this->validateBookData($data);

        return DB::transaction(function () use ($book, $data) {
            // 1. Update basic Book details
            $bookData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ];

            $this->entityManager->update($book, $bookData);

            // 2. Sync Authors
            if (isset($data['author_ids'])) {
                $book->authors()->sync($data['author_ids']);
            }

            // 3. Update or Create the Primary Version
            // For simplicity in this phase, we assume we are editing the "primary" version (first one)
            // or creating one if it doesn't exist (legacy migration case)

            $versionData = [
                'publisher_id' => $data['publisher_id'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'pages' => $data['pages'] ?? null,
                'published_year' => $data['published_year'] ?? null,
                'edition_number' => $data['edition_number'] ?? 1,
            ];

            if (isset($data['file_path'])) {
                $versionData['file_path'] = $data['file_path'];
                $versionData['format'] = $data['format'] ?? 'pdf';
                $versionData['file_size'] = $data['file_size'] ?? 0;
            }

            // simple strategy: update the first version found, or create new
            $version = $book->versions()->first();

            if ($version) {
                $version->update($versionData);
            } else {
                // Should have file_path if creating new, but might be missing in edit if not re-uploaded
                // This is a partial edge case for legacy data without versions.
                if (isset($data['file_path'])) {
                    $versionData['book_id'] = $book->id;
                    Version::create($versionData);
                }
            }

            return $book->fresh(['authors', 'versions']);
        });
    }

    protected function validateBookData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'file_path' => 'nullable|string', // nullable on update
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'publisher_id' => 'nullable|uuid|exists:publishers,id',
            'isbn' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
