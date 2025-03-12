<?php

namespace cityfibre\auth0authtmfpackage\Repositories;

use cityfibre\auth0authtmfpackage\Models\Auth0IP;

class Auth0IPRepository
{
    public function __construct(protected Auth0IP $model)
    {
        //
    }

    public function create(string $buyerId, string $ipAddress): Auth0IP
    {
        return $this->model->query()->create(['buyer_id' => $buyerId, 'ip_address' => $ipAddress]);
    }
}