<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Entity\ClientEntity;
use \League\OAuth2\Server\Entity\SessionEntity;
use \League\OAuth2\Server\Storage\Adapter;
use \League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends StorageAdapter implements ClientInterface
{
    public function get($client_id, $client_secret = null, $redirect_uri = null, $grant_type = null)
    {
        $sql = 'SELECT oauth_clients.*';

        if ($redirect_uri)
        {
            $sql .= ', oauth_client_redirect_uris.*'
                 .  ' INNER JOIN oauth_client_redirect_uris'
                 .  ' ON oauth_clients.id = oauth_client_redirect_uris.client_id';
        }

        $sql .= ' FROM oauth_clients WHERE oauth_clients.id = ?';

        $parameters = [$client_id];

        if ($client_secret)
        {
            $sql          .= ' AND oauth_clients.secret = ?';
            $parameters[]  = $client_secret;
        }

        if ($redirect_uri)
        {
            $sql          .= 'AND oauth_client_redirect_uris.redirect_uri = ?';
            $parameters[]  = $redirect_uri;
        }

        $results = $this->db->fetch($sql, $parameters);

        if (count($results) === 1)
        {
            $client = new ClientEntity($this->server);

            $client->hydrate([
                'id'   => $results[0]['id'],
                'name' => $results[0]['name']
            ]);

            return $client;
        }

        return null;
    }

    public function getBySession(SessionEntity $session)
    {
        /*
        $result = Capsule::table('oauth_clients')
                            ->select(['oauth_clients.id', 'oauth_clients.name'])
                            ->join('oauth_sessions', 'oauth_clients.id', '=', 'oauth_sessions.client_id')
                            ->where('oauth_sessions.id', $session->getId())
                            ->get();

        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result[0]['id'],
                'name'  =>  $result[0]['name']
            ]);

            return $client;
        }

        return null;
        */
    }
}

