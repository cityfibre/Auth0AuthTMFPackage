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
        return $this->model->where('buyer_id', $buyerId)->first();
    }
}