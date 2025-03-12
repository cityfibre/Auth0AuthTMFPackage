<?php

namespace cityfibre\auth0authtmfpackage\Repositories;

use cityfibre\auth0authtmfpackage\Models\Auth0;

class Auth0Repository
{
    public function __construct(protected Auth0 $model)
    {
        //
    }

    public function getByBuyerId(string $buyerId): ?Auth0
    {
        return $this->model->query()->where('buyer_id', $buyerId)->first();
    }

    public function createUpdate(array $attributes, array $values): Auth0
    {
        return $this->model->query()->updateOrCreate($attributes, $values);
    }
}