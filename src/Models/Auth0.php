<?php

namespace cityfibre\auth0authtmfpackage\Models;

use Database\Factories\Auth0Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auth0 extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auth0';

    protected $primaryKey = 'buyer_id';
    public $incrementing = false;

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

    static function newFactory(): Auth0Factory
    {
        return new Auth0Factory();
    }

    public function ipAddresses(): HasMany
    {
        return $this->hasMany(Auth0IP::class,'buyer_id','buyer_id');
    }
}