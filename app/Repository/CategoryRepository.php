<?php

namespace App\Repository;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * @return Collection<int,Category>
     */
    public function get(?int $limit = null): Collection
    {
        return Category::query()
            ->when($limit, function (Builder $query) use ($limit) {
                $query->limit($limit);
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Category
    {
        $category = Category::query()->findOrFail($id);
        $category->update($data);

        return $category->fresh();
    }

    public function delete(int $id): bool
    {
        return Category::query()->findOrFail($id)->delete();
    }

    public function findById(int $id): Category
    {
        return Category::query()->findOrFail($id);
    }
}
