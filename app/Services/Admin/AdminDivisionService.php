<?php

namespace App\Services\Admin;

use App\Models\Division;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminDivisionService
{
    public function paginateForIndex(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $term = trim((string) ($filters['search'] ?? ''));

        $query = Division::query()
            ->with('leader')
            ->withCount('users')
            ->orderBy('name', 'asc');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhereHas('leader', function ($q2) use ($term) {
                        $q2->where('name', 'ilike', '%' . $term . '%');
                    });
            });
        }

        return $query->paginate($perPage);
    }
}
