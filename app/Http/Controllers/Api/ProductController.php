<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Repository\ProductRepository;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ProductStoreRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $categoryId = $request->validate([
              'category_id' => 'nullable|integer|exists:categories,id'
        ])['category_id'] ?? null;

        $limit = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ])['limit'] ?? 25;

        $products = $this->repository->getByCategory($categoryId, $limit);

        return ProductResource::collection($products);
    }
    
    public function show(int $id): ProductResource
    {
        $product = $this->repository->findById($id);
        return new ProductResource($product);
    }

    public function store(ProductStoreRequest $request): ProductResource
    {
        $product = $this->repository->create($request->validated());
        
        return new ProductResource($product);
    }

    public function update(int $id, ProductStoreRequest $request): ProductResource
    {
        $product = $this->repository->update($id, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json(['success' => true]);
    }

    public function top10(Request $request): AnonymousResourceCollection
    {
        $top10Products = $this->repository->getTop();

        return ProductResource::collection($top10Products);
    }
}