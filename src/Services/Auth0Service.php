<?php

namespace cityfibre\auth0authtmfpackage\Services;

use cityfibre\auth0authtmfpackage\Exceptions\Auth0DataException;
use cityfibre\auth0authtmfpackage\Models\Auth0;
use cityfibre\auth0authtmfpackage\Models\Auth0IP;
use cityfibre\auth0authtmfpackage\Repositories\Auth0IPRepository;
use cityfibre\auth0authtmfpackage\Repositories\Auth0Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use willfd\auth0middlewarepackage\Exceptions\AuthenticationException;

class Auth0Service
{
    public function __construct(
        protected LoggerInterface $logger,
        protected Auth0Repository $auth0Repository,
        protected Auth0IPRepository $auth0IPRepository,
        protected bool $ipAddressWhitelistingEnabled = false
    ){
        //
    }

    /**
     * @throws Auth0DataException
     * @throws AuthenticationException
     * @throws ValidationException
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
        $auth0Model = $this->auth0Repository->getByBuyerId($buyerId);

        if( is_null($auth0Model) ){
            $this->logger->debug("No Auth0 Data for given BuyerId: ".$buyerId);
            throw new Auth0DataException("No Auth0 Data for given BuyerId: ".$buyerId);
        }
        // @Todo check if auth0 enabled for model
        if( !$auth0Model->auth_0_enabled ){
            $this->logger->debug("Auth0 Disabled for given BuyerId: ".$buyerId);
            throw new AuthenticationException("Auth0 Disabled for given BuyerId: ".$buyerId);
        }
        // @todo check if model active
        if( !$auth0Model->is_active ){
            $this->logger->debug("BuyerId: ".$buyerId. " is not active");
            throw new AuthenticationException("BuyerId: ".$buyerId. " is not active");
        }

        // ip address validation
        if( $this->ipAddressWhitelistingEnabled ) {
            $forwardedIp = request()->header('X-Forwarded-For') ?? "";
            $this->logger->debug("Forwarded IP: " . $forwardedIp);
            $requestIp = $request->ip() ?? "";
            $this->logger->debug("Request->IP: " . $requestIp);
            $requestersIp = request()->header('X-Forwarded-For') ?? $request->ip();
            $validIpAddresses = $auth0Model->ipAddresses->pluck('ip_address')->all();
            if (!in_array($requestersIp, $validIpAddresses)) {
                $this->logger->debug("Auth Failed request from invalid ip: " . $requestersIp . " Ip not in whitelist for BuyerId: " . $buyerId, $validIpAddresses);
                throw new AuthenticationException("Auth Failed request from invalid ip: " . $requestersIp);
            }
        }

        return $request;
    }

    /**
     * @param string $buyerId
     * @param bool $auth0Enabled
     * @param bool $isActive
     * @param array<string> $ipAddresses
     * @return Auth0
     */

    public function createUpdateAuth0Model(string $buyerId, bool $auth0Enabled, bool $isActive, array $ipAddresses = null): Auth0
    {
        $auth0Model = $this->auth0Repository->createUpdate(
            ['buyer_id' => $buyerId],
            [
                'buyer_id' => $buyerId,
                'auth_0_enabled' => $auth0Enabled,
                'is_active' => $isActive,
            ]
        );

        if( $this->ipAddressWhitelistingEnabled){
            // remove unwanted ip addresses
            $currentIpAddresses = $auth0Model->ipAddresses;
            $currentIpAddresses->each(function(Auth0IP $ipAddress) use ($ipAddresses) {
                if( !in_array($ipAddress->ip_address, $ipAddresses) ){
                    $ipAddress->delete();
                }
            });

            // add missing ip addresses
            foreach($ipAddresses as $newIPAddress){
                if( !in_array($newIPAddress, $currentIpAddresses->pluck('ip_address')->all())){
                    $this->auth0IPRepository->create($buyerId, $newIPAddress);
                }
            }
        }

        return $auth0Model->refresh();
    }

    /**
     * @throws ValidationException
     */
    public function getBuyerFromRequest(Request $request): string
    {
        $this->logger->debug("getBuyerFromRequest");
        $relatedParties = $request->input('relatedParty', []);
        $buyer = collect($relatedParties)->firstWhere('role', 'buyer');
        Validator::make(
            $buyer,
            [
                'partyOrPartyRole' => 'required|array',
                'partyOrPartyRole.name' => 'required|string',
            ]
        )->validate();
        $buyerId = $buyer['partyOrPartyRole']['name'];
        $this->logger->debug("getBuyerFromRequest first buyer is ".$buyerId);
        return $buyerId;
    }
}