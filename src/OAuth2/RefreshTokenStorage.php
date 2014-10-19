<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\RefreshTokenEntity;
use \League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenStorage extends StorageAdapter implements RefreshTokenInterface
{
    public function get($token)
    {
        $sql = 'SELECT oauth_refresh_tokens.*'
             . ' FROM oauth_refresh_tokens'
             . ' WHERE refresh_token = ?'
             . ' AND expires_at >= ?;';

        $results = $this->db->fetch($sql, [$token, time()]);

        if (count($results) === 1)
        {
            return (new RefreshTokenEntity($this->server))
                ->setId($results[0]['refresh_token'])
                ->setExpireTime($results[0]['expires_at'])
                ->setAccessTokenId($results[0]['access_token_id']);
        }

        return null;
    }

    public function create($token, $expiration, $access_token)
    {
        $results = $this->mongo->oauth_access_tokens->findOne([
            'access_token' => $access_token,
        ]);

        $token_id = $results['_id']->{'$id'};

        return $this->mongo->oauth_refresh_tokens->insert([
            'refresh_token'   => $token,
            'access_token_id' => $token_id,
            'expires_at'      => $expiration,
            'client_id'       => $this->server->getRequest()->request->get('client_id', null),
        ]);
    }

    public function delete(RefreshTokenEntity $token)
    {
        $sql = 'DELETE FROM oauth_refresh_tokens WHERE refresh_token = ?;';

        $this->db->execute($sql, [$token->getId()]);
    }
}

