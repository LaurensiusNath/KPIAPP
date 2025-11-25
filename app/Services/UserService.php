<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;

class UserService
{

    public function createUser(array $userData): void
    {
        User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? 'user',
            'password' => Crypt::encryptString($userData['password']),
        ]);
    }

    public function updateUser(int $userId, array $userData): void
    {
        $user = User::findOrFail($userId);

        // Prepare update data
        $updateData = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role'],
        ];

        // Only encrypt and update password if provided
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updateData['password'] = Crypt::encryptString($userData['password']);
        }

        $user->update($updateData);
    }

    public function findUserById(int $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * Get available users for division leader assignment
     * If divisionId is provided: users with role 'user' who have no division OR are in the same division
     * If divisionId is null: only users with role 'user' who have no division
     */
    public function getAvailableLeaders(?int $divisionId = null): Collection
    {
        $query = User::where('role', 'user');

        if ($divisionId !== null) {
            // Include users with no division OR users in the same division
            $query->where(function ($q) use ($divisionId) {
                $q->whereNull('division_id')
                    ->orWhere('division_id', $divisionId);
            });
        } else {
            // Only users with no division
            $query->whereNull('division_id');
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Get all users in a specific division with pagination
     */
    public function getUsersByDivision(int $divisionId): LengthAwarePaginator
    {
        return User::where('division_id', $divisionId)
            ->where('role', '=', 'user')
            ->orderBy('name', 'asc')
            ->paginate(25)
            ->withQueryString();
    }

    public function getLeaderByDivision(int $divisionId): ?User
    {
        return User::where('division_id', $divisionId)
            ->where('role', 'team-leader')
            ->first();
    }

    /**
     * Get available users (no division assigned yet)
     */
    public function getAvailableUsers(): Collection
    {
        return User::where('role', 'user')
            ->whereNull('division_id')
            ->orderBy('name', 'asc')
            ->get();
    }


    /**
     * Assign user to division
     */
    public function assignUserToDivision(int $userId, int $divisionId): void
    {
        User::whereKey($userId)->update(['division_id' => $divisionId]);
    }

    /**
     * Remove user from division
     */
    public function removeUserFromDivision(int $userId): void
    {
        User::whereKey($userId)->update(['division_id' => null]);
    }
}
