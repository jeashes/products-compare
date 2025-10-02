<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Resources\CategoryResource;
use App\Repository\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepository $repository) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ])['limit'] ?? 20;
        
        $categories = $this->repository->get($limit);

        return CategoryResource::collection($categories);
    }

    public function show(int $id): CategoryResource
    {
        $category = $this->repository->findById($id);
        return new CategoryResource();
    }

    public function store(CategoryStoreRequest $request): CategoryResource
    {
        $category = $this->repository->create($request->validated());

        return new CategoryResource($category);
    }

    public function update(int $id, CategoryStoreRequest $request): CategoryResource
    {
        $category = $this->repository->update($id, $request->validated());
        return new CategoryResource($category);
    }

    public function destroy(int $id): Response
    {
        $this->repository->delete($id);

        return response()->json(['success' => true]);
    }
}
