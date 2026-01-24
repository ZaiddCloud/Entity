<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id
 * @property string $entity_id
 * @property string $entity_type
 * @property string $type
 * @property string|null $title
 * @property string|null $slug
 * @property array|string|null $content
 * @property int|null $order
 * @property array|null $metadata
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class EntityContent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'entity_contents';

    /**
     * جعل النموذج مرناً بالكامل لاستقبال أي هيكلية بيانات
     * (Extreme Flexibility as requested)
     */
    protected $guarded = [];

    /**
     * العلاقة العكسية مع الكيان الأصلي (MySQL Entity)
     * ملاحظة: هذه علاقة يدوية لأننا نربط Mongo بـ MySQL
     */
    public function entity()
    {
        // هذه فقط للمساعدة، لكن العلاقة الأساسية تُدار من طرف Entity
        // بما أن entity_type متغير، لا يمكننا تعريف belongsTo واحد ثابت بسهولة
        // إلا إذا استخدمنا morphTo ولكن عبر قواعد بيانات مختلفة قد يكون معقداً
        // للأغراض الحالية، سنكتفي بتخزين ID و Type
    }
}
