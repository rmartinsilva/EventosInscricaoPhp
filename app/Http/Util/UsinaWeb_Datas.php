<?php
namespace App\Http\Util;

use Carbon\Carbon;

class UsinaWeb_Datas {


    public const formatoDataSimples = 'Y-m-d';       // Ex: 31/05/2025
    public const formatoDataHora = 'Y-m-d H:i:s';    // Ex: 31/05/2025 10:01:01


    /**
     * Interpreta uma string de data/hora e a formata para um novo padrão.
     * Pode converter para um fuso horário específico.
     *
     * @param string|null $dateString A string da data/hora original (ex: "2025-05-31" ou "2025-05-15T10:01:01.000000Z").
     * @param string $outputFormat O formato de saída desejado (ex: 'd/m/Y', 'Y-m-d H:i:s').
     * @param string|null $targetTimezone O fuso horário de destino (ex: 'America/Sao_Paulo'). Se null, mantém o fuso horário original da string (ou UTC para strings com 'Z').
     * @return string|null A data formatada ou null se a entrada for null ou inválida.
     */
    public function formatDateString(?string $dateString, string $outputFormat, ?string $targetTimezone = null): ?string
    {
        if ($dateString === null) {
            return null;
        }

        try {
            // Interpreta a string (seja "Y-m-d" ou ISO 8601 com 'Z') para um objeto Carbon.
            $carbonInstance = Carbon::parse($dateString);

            // Converte para o fuso horário desejado, se especificado.
            if ($targetTimezone) {
                $carbonInstance->setTimezone($targetTimezone);
            }

            // Formata o objeto Carbon para a string de saída.
            return $carbonInstance->format($outputFormat);
        } catch (\Exception $e) {
            // Em caso de erro na interpretação da string.
            // error_log("Erro ao formatar data string: " . $dateString . " - " . $e->getMessage()); // Opcional: logar o erro
            return null; // Retorna null para indicar falha na formatação.
        }
    }
}