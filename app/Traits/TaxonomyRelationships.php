<?php

namespace App\Traits;

use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait TaxonomyRelationships
{
    /**
     * Get the pivot table name for the taxonomy relationship.
     */
    abstract protected function getTaxonomyPivotTable(): string;

    public function books(): MorphToMany
    {
        return $this->morphedByMany(Book::class, 'entity', $this->getTaxonomyPivotTable());
    }

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'entity', $this->getTaxonomyPivotTable());
    }

    public function audio(): MorphToMany
    {
        return $this->morphedByMany(Audio::class, 'entity', $this->getTaxonomyPivotTable());
    }

    public function manuscripts(): MorphToMany
    {
        return $this->morphedByMany(Manuscript::class, 'entity', $this->getTaxonomyPivotTable());
    }
}
