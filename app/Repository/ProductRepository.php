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
    public function getByCategory(?int $categoryId, ?int $limit = 20): Collection
    {
      return Product::query()
            ->when($categoryId, function (Builder $query) use ($categoryId) {
                $query->where('category_id', $categoryId);
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
}