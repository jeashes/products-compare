<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Product
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $image
 * @property float $price
 * @property float $rating
 * @property array $pros
 * @property array $cons
 * @property array $key_features
 * @property int $trending_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Category $category
 */
class Product extends Model
{
    use HasFactory;

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'image',
        'price',
        'rating',
        'pros',
        'cons',
        'key_features',
        'trending_order',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'pros'          => 'array',
        'cons'          => 'array',
        'key_features'  => 'array',
    ];

    /**
     * @return BelongsTo<Category,Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
