<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    private const TOP_PRODUCTS_COUNT = 10;
    
    public function getByCategory(int $limit, ?string $categorySlug = null): LengthAwarePaginator
    {
        return Product::query()
            ->when($categorySlug, function (Builder $query) use ($categorySlug) {
                $query->whereRelation('category', 'slug', $categorySlug);
            })
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Product
    {
        $product = Product::query()->findOrFail($id);
        $product->update($data);
        
        return $product->fresh();
    }
    
    public function delete(int $id): bool
    {
        return Product::query()->findOrFail($id)->delete();
    }

    /**
     * @return EloquentCollection<int,Product>
     */
    public function getTop(): EloquentCollection
    {
        return Product::query()
            ->whereNotNull('trending_order')
            ->orderBy('trending_order')
            ->limit(self::TOP_PRODUCTS_COUNT)
            ->get();
    }

    public function findById(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    /**
     * @param array<int,int> $ids
     * @return EloquentCollection<int,Product>
     */
    public function getByIds(array $ids): EloquentCollection
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @param array<int,int> $ids
     * @return Collection<int,int>
     */
    public function getIdsByIds(array $ids): Collection
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->values();
    }

    public function isProductExists(int $id): bool
    {
        return Product::query()->where('id', $id)->exists();
    }
}