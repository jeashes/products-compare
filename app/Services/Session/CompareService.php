<?php

namespace App\Services\Session;

use App\Repository\ProductRepository;
use Illuminate\Support\Collection;
use App\Managers\Session\CompareSessionManager;

class CompareService
{
    private const MAX_ITEMS = 3;

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly CompareSessionManager $sessionManager
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function add(int $productId): array
    {
        $ids = $this->getValidIds();

        if ($ids->contains($productId)) {
            return [
                'success' => true,
                'products' => $this->getProducts(),
            ];
        }

        if ($ids->count() >= self::MAX_ITEMS) {
            return [
                'success' => false,
                'message' => "Compare list limit reached (max " . self::MAX_ITEMS . ").",
            ];
        }

        if (!$this->productRepository->isProductExists($productId)) {
            return [
                'success' => false,
                'message' => 'Product not found.',
            ];
        }

        $ids->push($productId);
        $this->sessionManager->save($ids->unique()->take(self::MAX_ITEMS)->values());

        return [
            'success' => true,
            'products' => $this->getProducts(),
        ];
    }

    public function getProducts(): Collection
    {
        $ids = $this->getValidIds();
        
        if ($ids->isEmpty()) {
            return collect();
        }

        $products = $this->productRepository->getByIds($ids->all());

        return $products
            ->sortBy(fn($product) => $ids->search($product->id))
            ->values();
    }

    public function remove(int $productId): void
    {
        $ids = $this->sessionManager->get()
            ->reject(fn($id) => (int)$id === $productId)
            ->values();

        $this->sessionManager->save($ids);
    }

    public function clear(): void
    {
        $this->sessionManager->clear();
    }

    public function getCount(): int
    {
        return $this->getValidIds()->count();
    }

    public function hasProduct(int $productId): bool
    {
        return $this->getValidIds()->contains($productId);
    }

    private function getValidIds(): Collection
    {
        $ids = $this->sessionManager->get()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $existing = $this->productRepository->getIdsByIds($ids->all());

        if ($existing->count() !== $ids->count()) {
            $this->sessionManager->save($existing);
        }

        return $existing;
    }
}