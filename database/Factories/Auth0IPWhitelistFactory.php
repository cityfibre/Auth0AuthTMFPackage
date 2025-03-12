<?php

namespace Database\Factories;

use cityfibre\auth0authtmfpackage\Models\Auth0;
use cityfibre\auth0authtmfpackage\Models\Auth0IP;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auth0>
 */
class Auth0IPWhitelistFactory extends Factory
{
    protected $model = Auth0IP::class;

    public function definition(): array
    {
        return [
            'buyer_id' => $this->faker->string,
            'ip_address' => $this->faker->ipv4
        ];
    }
}