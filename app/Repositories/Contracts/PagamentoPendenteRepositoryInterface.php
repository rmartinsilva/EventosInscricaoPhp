<?php

namespace App\Repositories\Contracts;

use stdClass;

interface PagamentoPendenteRepositoryInterface
{
    public function getById(int $idPagamento);
}
