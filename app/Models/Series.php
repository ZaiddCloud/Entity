<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $id
 * @property string $title
 * @property string|null $description
 * @property int $order_column
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Support\Collection $entities
 */
class Series extends Model
{
    use \App\Traits\HasPolymorphicRelations;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids, \App\Traits\TaxonomyRelationships;

    protected function getTaxonomyPivotTable(): string
    {
        return 'seriables';
    }

    protected $fillable = [
        'title',
        'description',
        'order_column',
    ];

    /**
     * Get all entities in this series.
     */
    public function getEntitiesAttribute()
    {
        return $this->getEntitiesFromSeries();
    }

    /**
     * Add entity to series
     */
    public function addEntity($entity, $position = null)
    {
        $currentMax = $this->entities->max(function ($entity) {
            return $entity->pivot_data['position'] ?? 0;
        }) ?? 0;

        return $this->attachToEntity($entity, 'series', [
            'position' => $position ?? ($currentMax + 1)
        ]);
    }
}
