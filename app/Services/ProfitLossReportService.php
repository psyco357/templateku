<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class ProfitLossReportService
{
    public function __construct(protected AccountingReportService $accountingReportService) {}

    public function build(int $koperasiId, Carbon $startDate, Carbon $endDate): array
    {
        return $this->accountingReportService->buildProfitLoss($koperasiId, $startDate, $endDate);
    }
}
