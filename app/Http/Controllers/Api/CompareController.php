<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Services\Session\CompareService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    public function __construct(
        private readonly CompareService $compareService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->compareService->getProducts();

        return ProductResource::collection($products);
    }

    public function add(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $productId = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ])['product_id'];

        $result = $this->compareService->add($productId);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return ProductResource::collection($result['products']);
    }

    public function remove(Request $request, int $id): Response
    {
        $this->compareService->remove($id);

        return response()->noContent();
    }

    public function clear(Request $request): Response
    {
        $this->compareService->clear();

        return response()->noContent();
    }
}
