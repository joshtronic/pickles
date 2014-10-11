<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\SessionEntity;
use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends Adapter implements ClientInterface
{
    public function get($client_id, $client_secret = null, $redirect_uri = null, $grant_type = null)
    {

    }

    public function getBySession(SessionEntity $session)
    {

    }
}

