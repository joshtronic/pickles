<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\AbstractTokenEntity;
use \League\OAuth2\Server\Entity\ScopeEntity;
use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends Adapter implements AccessTokenInterface
{
    public function get($token)
    {

    }

    public function getScopes(AbstractTokenEntity $token)
    {

    }

    public function create($token, $expiration, $session_id)
    {

    }

    public function associateScope(AbstractTokenEntity $token, ScopeEntity $scope)
    {

    }

    public function delete(AbstractTokenEntity $token)
    {

    }
}

