<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface PaginationInterface
{
    /**
     * @return array<mixed>
     */
    public function items(): array;
    public function total(): int;
    public function isFirstPage(): bool;
    public function isLastPage(): bool;
    public function currentPage(): int;
    public function nextPage(): int|null;
    public function previousPage(): int|null;
    public function totalPages(): int;
    public function perPage(): int;
}
