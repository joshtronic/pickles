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
        $sql = 'SELECT oauth_sessions.id, oauth_sessions.owner_type,'
             . ' oauth_sessions.owner_id, oauth_sessions.client_id,'
             . ' oauth_sessions.client_redirect_uri'
             . ' FROM oauth_sessions'
             . ' INNER JOIN oauth_access_tokens'
             . ' ON oauth_access_tokens.session_id = oauth_sessions.id'
             . ' WHERE oauth_access_tokens.access_token = ?;';

        $results = $this->db->fetch($sql, [$access_token->getId()]);

        if (count($results) === 1)
        {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }

        return null;
    }

    public function getByAuthCode(AuthCodeEntity $auth_code)
    {
        $sql = 'SELECT oauth_sessions.id, oauth_sessions.owner_type,'
             . ' oauth_sessions.owner_id, oauth_sessions.client_id,'
             . ' oauth_sessions.client_redirect_uri'
             . ' FROM oauth_sessions'
             . ' INNER JOIN oauth_authorization_codes'
             . ' ON oauth_authorization_codes.session_id = oauth_sessions.id'
             . ' WHERE oauth_authorization_codes.authorization_code = ?;';

        $results = $this->db->fetch($sql, [$auth_code->getId()]);

        if (count($results) === 1)
        {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }

        return null;
    }

    public function getScopes(SessionEntity $session)
    {
        /*
        // @todo
        // INNER JOIN oauth_access_token_scopes
        // ON oauth_sessions.id = oauth_access_token_scopes.access_token_id
        // INNER JOIN oauth_scopes
        // ON oauth_scopes.id = oauth_access_token_scopes.scope_id

        $results = $this->mongo->oauth_sessions->findOne([
            '_id' => new \MongoId($session->getId())
        ]);

        $scopes = [];

        foreach ($results as $scope)
        {
            $scopes[] = (new ScopeEntity($this->server))->hydrate([
                'id'          => $scope['id'],
                'description' => $scope['description'],
            ]);
        }

        return $scopes;
        */

        return [];
    }

    public function create($owner_type, $owner_id, $client_id, $client_redirect_uri = null)
    {
        return $this->mongo->oauth_sessions->insert([
            'owner_type' => $owner_type,
            'owner_id'   => $owner_id,
            'client_id'  => $client_id,
        ]);
    }

    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $sql = 'INSERT INTO oauth_access_token_scopes'
             . ' (access_token_id, scope_id)'
             . ' VALUES'
             . ' (?, ?);';

        $this->db->execute($sql, [$session->getId(), $scope->getId()]);
    }
}

