<?php

namespace App\Services\Admin;

use App\Models\Period;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminPeriodService
{
    public function paginateForIndex(int $perPage = 20): LengthAwarePaginator
    {
        return Period::query()
            ->orderByDesc('is_active')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->paginate($perPage);
    }
}
