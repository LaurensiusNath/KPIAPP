<?php

namespace App\Services\Admin;

use App\Models\Division;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminUserService
{
    public function paginateForIndex(array $filters, int $excludeUserId): LengthAwarePaginator
    {
        $usersQuery = User::query()
            ->with('division')
            ->whereIn('role', ['team-leader', 'user'])
            ->where('id', '!=', $excludeUserId);

        $this->applySort($usersQuery, (string) ($filters['sort_by'] ?? 'name_asc'));
        $this->applySearch($usersQuery, (string) ($filters['search'] ?? ''));
        $this->applyDivisionFilter($usersQuery, $filters['divisionFilter'] ?? null);

        return $usersQuery->paginate(20);
    }

    public function getDivisionsForFilter(): Collection
    {
        return Division::query()->orderBy('name')->get();
    }

    protected function applySort(Builder $query, string $sortBy): void
    {
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest();
        }
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $term = trim($search);
        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query
                ->where('name', 'ilike', '%' . $term . '%')
                ->orWhere('email', 'ilike', '%' . $term . '%');
        });
    }

    protected function applyDivisionFilter(Builder $query, mixed $divisionId): void
    {
        if (empty($divisionId)) {
            return;
        }

        $query->where('division_id', (int) $divisionId);
    }
}
