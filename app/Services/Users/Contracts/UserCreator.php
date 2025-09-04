<?php

namespace App\Services\Users\Contracts;

use App\Models\User;

interface UserCreator
{
    /**
     * Create a new user with role-specific defaults.
     *
     * @param  array{role:string,name:string,email:string,password:string|null,subjects?:array<string>}  $data
     * @return User
     */
    public function create(array $data): User;
}
