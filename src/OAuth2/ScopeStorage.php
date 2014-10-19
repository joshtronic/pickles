<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends StorageAdapter implements ScopeInterface
{
    public function get($scope, $grant_type = null, $client_id = null)
    {
        $sql     = 'SELECT * FROM oauth_scopes WHERE id = ?;';
        $results = $this->db->fetch($sql, [$scope]);

        if (count($results) === 0)
        {
            return null;
        }

        return (new ScopeEntity($this->server))->hydrate([
            'id'          => $result[0]['id'],
            'description' => $result[0]['description'],
        ]);
    }
}

