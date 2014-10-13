<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\AccessTokenEntity;
use \League\OAuth2\Server\Entity\AuthCodeEntity;
use \League\OAuth2\Server\Entity\ScopeEntity;
use \League\OAuth2\Server\Entity\SessionEntity;
use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\SessionInterface;

class SessionStorage extends StorageAdapter implements SessionInterface
{
    public function getByAccessToken(AccessTokenEntity $access_token)
    {

    }

    public function getByAuthCode(AuthCodeEntity $auth_code)
    {

    }

    public function getScopes(SessionEntity $session)
    {

    }

    public function create($owner_type, $owner_id, $client_id, $client_redirect_uri = null)
    {

    }

    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {

    }
}

