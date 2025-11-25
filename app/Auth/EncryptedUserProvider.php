<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Crypt;

class EncryptedUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        // Try to decrypt the stored password
        try {
            $decrypted = Crypt::decryptString($user->getAuthPassword());
            return $plain === $decrypted;
        } catch (\Throwable $e) {
            // If decryption fails, fall back to bcrypt check
            return $this->hasher->check($plain, $user->getAuthPassword());
        }
    }
}
