<?php

namespace App\Repositories;

use App\Repositories\Contracts\PaginationInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationPresenter implements PaginationInterface
{
    /**
     * @var array<mixed>
     */
    private array $items;

    public function __construct(
        protected LengthAwarePaginator $paginator
    ) {
        $this->items = $this->paginator->items();
    }

    /**
     * @return array<mixed>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->paginator->total() ?? 0;
    }

    public function isFirstPage(): bool
    {
        return $this->paginator->onFirstPage();
    }

    public function isLastPage(): bool
    {
        return !$this->paginator->hasMorePages();
    }

    public function currentPage(): int
    {
        return $this->paginator->currentPage() ?? 1;
    }

    public function nextPage(): int|null
    {
        return $this->paginator->hasMorePages() ? ($this->currentPage() + 1) : null;
    }

    public function previousPage(): int|null
    {
        return !$this->isFirstPage() ? ($this->currentPage() - 1) : null;
    }

    public function totalPages(): int
    {
        return $this->paginator->lastPage() ?? 0;
    }

    public function perPage(): int
    {
        return $this->paginator->perPage() ?? 0;
    }
}
