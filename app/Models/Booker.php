<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booker extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * الإعمال التي ساهم فيها هذا الشخص (كتب، فيديوهات، الخ)
     */
    public function bookables()
    {
        return $this->morphedByMany(Book::class, 'bookable');
    }

    /**
     * يمكننا إضافة علاقات أخرى هنا مثل videos(), manuscripts() لاحقاً
     * أو استخدام دالة عامة
     */
}
