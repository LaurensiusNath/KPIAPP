<?php

namespace App\Services;

use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DivisionService
{
    public function getAllDivisions()
    {
        return Division::with('leader')->get();
    }

    public function findDivisionById(int $id): Division
    {
        return Division::with('leader')->findOrFail($id);
    }

    public function createDivision(array $divisionData): void
    {
        DB::transaction(function () use ($divisionData) {
            // Create division first
            $division = Division::create([
                'name' => $divisionData['name'],
                'leader_id' => $divisionData['leader_id'],
            ]);

            // Promote leader and ensure membership
            User::whereKey($divisionData['leader_id'])->update([
                'division_id' => $division->id,
                'role' => 'team-leader',
            ]);
        });
    }


    public function deleteDivision(int $id): void
    {
        DB::transaction(function () use ($id) {
            $division = Division::findOrFail($id);

            // Demote current leader to 'user' if they were a team-leader
            if ($division->leader_id) {
                User::whereKey($division->leader_id)
                    ->where('role', 'team-leader')
                    ->update(['role' => 'user']);
            }

            // Set all users in this division to null division_id
            // Note: FK is nullOnDelete, but we do it explicitly for clarity and to trigger model events if any
            User::where('division_id', $division->id)->update(['division_id' => null]);

            // Finally, delete the division
            $division->delete();
        });
    }

    /**
     * Change division leader
     */
    public function changeLeader(int $divisionId, int $newLeaderId): void
    {
        DB::transaction(function () use ($divisionId, $newLeaderId) {
            $division = Division::findOrFail($divisionId);
            $oldLeaderId = $division->leader_id;

            // Demote old leader to user
            if ($oldLeaderId && $oldLeaderId !== $newLeaderId) {
                User::whereKey($oldLeaderId)->update(['role' => 'user']);
            }

            // Promote new leader
            User::whereKey($newLeaderId)->update([
                'division_id' => $divisionId,
                'role' => 'team-leader',
            ]);

            // Update division
            $division->update(['leader_id' => $newLeaderId]);
        });
    }
}
