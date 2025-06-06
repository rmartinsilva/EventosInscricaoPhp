<?php

namespace App\Adapters;

use App\Repositories\Contracts\PaginationInterface;

class ApiAdapter
{
    /**
     * @param PaginationInterface $paginator
     * @return array<string, mixed>
     */
    public static function pagination(PaginationInterface $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'is_first_page' => $paginator->isFirstPage(),
            'is_last_page' => $paginator->isLastPage(),
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->nextPage(),
            'previous_page' => $paginator->previousPage(),
            'total_pages' => $paginator->totalPages(),
            'per_page' => $paginator->perPage(),
        ];
    }
}
