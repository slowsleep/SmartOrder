<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class)
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'login' => $input['login'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'patronymic' => $input['patronymic'] ?: null,
            'birth_date' => $input['birth_date'],
            'role_id' => Role::where('name', 'admin')->first()->id,
            'password' => $input['password'],
        ]);
    }
}
