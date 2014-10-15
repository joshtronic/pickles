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
        $sql = 'SELECT oauth_session_access_tokens.*'
             . ' FROM oauth_session_access_tokens'
             . ' WHERE access_token = ?'
             . ' AND access_token_expires >= ?;';

        $results = $this->db->fetch($sql, [$token, time()]);

        if (count($results) === 1)
        {
            return (new AccessTokenEntity($this->server))
                ->setId($results[0]['access_token'])
                ->setExpireTime($results[0]['access_token_expires']);
        }

        return null;
    }

    public function getScopes(AbstractTokenEntity $token)
    {
        $sql = 'SELECT oauth_scopes.id, oauth_scopes.description'
             . ' FROM oauth_session_token_scopes'
             . ' INNER JOIN oauth_scopes'
             . ' ON oauth_session_token_scopes.scope_id = oauth_scopes.id'
             . ' WHERE oauth_session_token_scopes.session_access_token_id = ?;';

        $results  = $this->db->fetch($sql, [$token->getId()]);
        $response = [];

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

        return $response;
    }

    public function create($token, $expiration, $session_id)
    {
        $sql = 'INSERT INTO oauth_session_access_tokens'
             . ' (access_token, session_id, access_token_expires)'
             . ' VALUES'
             . ' (?, ?, ?);';

        $this->db->execute($sql, [$token, $session_id, $expiration]);
    }

    public function associateScope(AbstractTokenEntity $token, ScopeEntity $scope)
    {
        $sql = 'INSERT INTO oauth_session_token_scopes'
             . ' (access_token, scope)'
             . ' VALUES'
             . ' (?, ?);';

        $this->db->execute($sql, [$token->getId(), $scope->getId()]);
    }

    public function delete(AbstractTokenEntity $token)
    {
        $sql = 'DELETE FROM oauth_session_token_scopes'
             . ' WHERE access_token = ?;';

        $this->db->execute($sql, [$token->getId()]);
    }
}

