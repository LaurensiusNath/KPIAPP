<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableLeader implements ValidationRule
{
    public function __construct(private readonly ?int $divisionId = null) {}

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            $fail('The selected leader is invalid.');
            return;
        }

        $userId = (int) $value;

        /** @var UserService $userService */
        $userService = app(UserService::class);

        if (!$userService->isAvailableLeaderId($userId, $this->divisionId)) {
            $fail('Leader must be a staff user that is not assigned to another division.');
        }
    }
}
