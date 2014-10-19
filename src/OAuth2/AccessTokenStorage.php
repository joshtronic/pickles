<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\AbstractTokenEntity;
use \League\OAuth2\Server\Entity\AccessTokenEntity;
use \League\OAuth2\Server\Entity\ScopeEntity;
use \League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends StorageAdapter implements AccessTokenInterface
{
    public function get($token)
    {
        $sql = 'SELECT oauth_access_tokens.*'
             . ' FROM oauth_access_tokens'
             . ' WHERE access_token = ?'
             . ' AND expires_at >= ?;';

        $results = $this->db->fetch($sql, [$token, time()]);

        if (count($results) === 1)
        {
            return (new AccessTokenEntity($this->server))
                ->setId($results[0]['access_token'])
                ->setExpireTime($results[0]['expires_at']);
        }

        return null;
    }

    public function getScopes(AbstractTokenEntity $token)
    {
        $response = [];

        /*
        @todo Port to Mongo
        $sql = 'SELECT oauth_scopes.id, oauth_scopes.description'
             . ' FROM oauth_access_token_scopes'
             . ' INNER JOIN oauth_scopes'
             . ' ON oauth_access_token_scopes.scope_id = oauth_scopes.id'
             . ' WHERE oauth_access_token_scopes.access_token_id = ?;';

        $results  = $this->db->fetch($sql, [$token->getId()]);

        if (count($results) > 0)
        {
            foreach ($results as $row)
            {
                $response[] = (new ScopeEntity($this->server))->hydrate([
                    'id'          => $row['id'],
                    'description' => $row['description']
                ]);
            }
        }
        */

        return $response;
    }

    public function create($token, $expiration, $session_id)
    {
        return $this->mongo->oauth_access_tokens->insert([
            'access_token' => $token,
            'session_id'   => $session_id, // @todo Store as MongoId?
            'expires_at'   => $expiration,
        ]);
    }

    public function associateScope(AbstractTokenEntity $token, ScopeEntity $scope)
    {
        $sql = 'INSERT INTO oauth_access_token_scopes'
             . ' (access_token, scope)'
             . ' VALUES'
             . ' (?, ?);';

        $this->db->execute($sql, [$token->getId(), $scope->getId()]);
    }

    public function delete(AbstractTokenEntity $token)
    {
        $sql = 'DELETE FROM oauth_access_token_scopes'
             . ' WHERE access_token = ?;';

        $this->db->execute($sql, [$token->getId()]);
    }
}

