<?php

namespace cityfibre\auth0authtmfpackage\Services;

use cityfibre\auth0authtmfpackage\Exceptions\Auth0DataException;
use cityfibre\auth0authtmfpackage\Repositories\Auth0Repository;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use willfd\auth0middlewarepackage\Exceptions\AuthenticationException;

class Auth0Service
{
    public function __construct(protected LoggerInterface $logger, protected Auth0Repository $auth0Repository){
        //
    }

    /**
     * @throws Auth0DataException
     * @throws AuthenticationException
     */
    public function authenticateAgainstAuth0Models(Request $request): Request
    {
        $this->logger->debug("authenticateAgainstAuth0Models hit");

        // @Todo get buyer from either token or request if no buyerId as admin
        $buyerId = $request->attributes->get('tokenBuyerId');
        if( is_null($buyerId) ){
            // @Todo get from request
            $buyerId = $this->getBuyerFromRequest($request);
        }
        // @Todo get Auth0Model from buyerId
        $auth0Model = $this->auth0Repository->getbyBuyerid($buyerId);

        if( is_null($auth0Model) ){
            throw new Auth0DataException("No Auth0 Data for given BuyerId: ".$buyerId);
        }
        // @Todo check if auth0 enabled for model
        if( !$auth0Model->auth_0_enabled ){
            throw new AuthenticationException("Auth0 Disabled for given BuyerId: ".$buyerId);
        }
        // @todo check if model active
        if( !$auth0Model->is_active ){
            throw new AuthenticationException("BuyerId: ".$buyerId. " is not active");
        }
        return $request;
    }

    public function getBuyerFromRequest(Request $request): string
    {
        return "getFromRequest";
    }
}