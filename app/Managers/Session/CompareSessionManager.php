<?php

namespace App\Managers\Session;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use TKey;
use TValue;

class CompareSessionManager
{
    private const SESSION_KEY = 'compare';

    public function __construct(
        private readonly Session $session
    ) {
    }

    /**
     * @return Collection<TKey,TValue>
     */
    public function get(): Collection
    {
        return collect($this->session->get(self::SESSION_KEY, []));
    }

    /**
     * @param Collection<array-key,mixed> $ids
     */
    public function save(Collection $ids): void
    {
        $this->session->put(self::SESSION_KEY, $ids->all());
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    public function has(): bool
    {
        return $this->session->has(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->get()->isEmpty();
    }
}