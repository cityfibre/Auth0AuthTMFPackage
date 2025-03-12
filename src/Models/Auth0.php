<?php

namespace cityfibre\auth0authtmfpackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auth0 extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buyer_id',
        'auth_0_enabled',
        'is_active'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'buyer_id' => 'string',
        'auth_0_enabled' => 'boolean',
        'is_active' => 'boolean'
    ];
}