<?php

namespace App\Repository;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function get(int $limit = 20): LengthAwarePaginator
    {
        return Category::query()->orderBy('id')->paginate($limit);
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
