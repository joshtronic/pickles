<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends Adapter implements ScopeInterface
{
    public function get($scope, $grant_type = null, $client_id = null)
    {

    }
}

