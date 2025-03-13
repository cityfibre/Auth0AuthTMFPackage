# Auth0AuthTMFPackage

## Package Implementation

### Add Package to app
#### Add package to composer.json

```json
"repositories": [
        {
            "url": "https://github.com/cityfibre/Auth0AuthTMFPackage",
            "type": "git"
        }
    ],
```
Add package requirement
```json
"require-dev": {
  "cityfibre/auth0authtmfpackage": "dev-main"
},
```

### App Config
#### create config file and migrations for package
run this command, and search "cityfibre" to add both the middleware config and the migrations from the package. The migrations shall be refactored with the timestamp this command is executed.
```bash
php artisan vendor:publish
```

#### set env variables
```text
AUTH0_DOMAIN=          STRING                   ie https://domain-staging.uk.auth0.com
AUTH0_CLIENT_ID=       STRING                   ie ABcD1eFgHiJkL23Mn4opQ5rSTuVwXyzA
AUTH0_AUDIENCE=        STRING                   ie tmf-api
AUTH0_REQUIRED_SCOPES= STRING COMMA SEPERATED   ie write:app-example,read:app-example
AUTH0_ADMIN_SCOPES=    STRING COMMA SEPERATED   ie admin:app-example
```

#### Run migrations
```bash
php artisan migrate
```

### Add Provider
In boostrap/providers.php, add the packages provider class
```php
use cityfibre\auth0authtmfpackage\Auth0AuthTMFPackageServiceProvider;
...
return [
    Auth0AuthTMFPackageServiceProvider::class
];
```

#### Set Middleware Alias (optional)
In bootstrap/app.php add below. The alias can be called anything, the alias shall be used to reference the middleware within the routes
```php
$middleware->alias([
    'package' => Auth0AuthTMFPackageServiceProvider::class,
]);
```

#### route setup
Add middleware to desired routes. "package" is the middlewares alias, "write:example-scope" is the required scope any routes within the middleware route.
```php
Route::middleware('package:write:example-scope')
```

### Link Auth0 Model to Relevant other account table in app
#### Create a pivot table linking other account to auth0 table
```php
php artisan make:migration create_{{"other account table name"}}_auth0_table
```
#### Create a model for the pivot

example model below. Model must have buyer_id as a key for the auth0 model key. Must have a HasOne for the other account model and the auth0 package model.
```php
<?php

namespace App\Models;

use cityfibre\auth0authtmfpackage\Models\Auth0;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountAuth0 extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts_auth0';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'buyer_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'account_id' => 'integer',
        'buyer_id' => 'string'
    ];

    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'account_id', );
    }

    public function auth0(): HasOne
    {
        return $this->hasOne(Auth0::class, 'buyer_id', 'buyer_id' );
    }
}
```

#### Update other account model to link auth0
The second variable for the hasManyThrough needs to be the model for the pivot. 'account_id', 'id' can be changed according to use case. 
```php
public function auth0(): HasManyThrough
{
return $this->hasManyThrough(Auth0::class,  AccountAuth0::class, 'account_id', 'buyer_id', 'id', 'buyer_id');
}
```

### Add Logic to populate the Auth0 Data
use createUpdateAuth0Model from the package to populate the Auth0Model passing it all required fields.
```php
$this->auth0Service->createUpdateAuth0Model($buyerId, $body['Oauth2_enabled'] ?? false, $body['active'], $body['ip_addresses']);
```
if linking auth0 model to other model create a function to populate link between the two.
```php
$this->accountService->addBuyerIdAgainstAccount($account, $buyerId);
```

## Details
If admin scopes are provided within the env. required scopes can be ignored if an admin scope is provided in the env with a matching name ie if the required scope is read:example-app then admin:example-app would bypass the required scope.

## Local Dev

### Docker
Spin up container using below (first time also include --build)
```bash
docker-compose up
```


### tests
```bash
vendor/bin/phpunit tests/
```
