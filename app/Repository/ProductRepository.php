<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * @return Collection<int,Product>
     */
    public function getByCategory(?string $categorySlug = null, ?int $limit): Collection
    {
        return Product::query()
            ->when($categorySlug, function (Builder $query) use ($categorySlug) {
                $query->whereHas('category', function (Builder $c) use ($categorySlug) {
                    $c->where('slug', $categorySlug);
                });
            })
            ->when($limit, function (Builder $query) use ($limit) {
                $query->limit($limit);
            })
            ->get();
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
     * @return Collection<int,Product>
     */
    public function getTop(int $limit = 10): Collection
    {
        return Product::query()
            ->whereNotNull('trending_order')
            ->orderBy('trending_order')
            ->limit($limit)
            ->get();
    }

    public function findById(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    /**
     * @param array<int,int> $ids
     * @return Collection<int,Product>
     */
    public function getByIds(array $ids): Collection
    {
        return Product::query()
            ->whereIn('id', $ids)
            ->get();
    }
}