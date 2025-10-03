<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Repository\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class CompareController extends Controller
{
    private const SESSION_KEY = 'compare';
    private const MAX_ITEMS = 3;

    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $ids = collect($request->session()->get(self::SESSION_KEY, []))
            ->unique()
            ->take(self::MAX_ITEMS)
            ->values();

        $products = $this->productRepository->getByIds($ids->all());

        $ordered = $products
            ->sortBy(function ($product) use ($ids) {
              return $ids->search($product->id);
            })
            ->values();

        return ProductResource::collection($ordered);
    }

    public function add(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $ids = collect($request->session()->get(self::SESSION_KEY, []));

        if ($ids->contains($data['product_id'])) {
            $request->session()->put(self::SESSION_KEY, $ids->values()->all());
            return $this->index($request);
        }

        if ($ids->count() >= self::MAX_ITEMS) {
            return response()->json([
                'message' => 'Compare list limit reached (max '.self::MAX_ITEMS.').',
            ], 422);
        }

        $ids->push($data['product_id']);

        $request->session()->put(self::SESSION_KEY, $ids->unique()->take(self::MAX_ITEMS)->values()->all());

        return $this->index($request);
    }

    public function remove(Request $request, int $id): Response
    {
        $ids = collect($request->session()->get(self::SESSION_KEY, []))
            ->reject(fn ($v) => (int) $v === $id)
            ->values();

        $request->session()->put(self::SESSION_KEY, $ids->all());

        return response()->noContent();
    }

    public function clear(Request $request): Response
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->noContent();
    }
}
