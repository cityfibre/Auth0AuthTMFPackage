<?php

namespace Database\Factories;

use cityfibre\auth0authtmfpackage\Models\Auth0;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auth0>
 */
class Auth0Factory extends Factory
{
    protected $model = Auth0::class;

    public function definition(): array
    {
        return [
            'buyer_id' => $this->faker->string,
            'auth_0_enabled' => $this->faker->boolean,
            'is_active' => $this->faker->boolean
        ];
    }
}