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
        $criteria = ['_id' => new \MongoId($client_id)];

        if ($redirect_uri)
        {
            // @todo join / query oauth_client_redirect_uris
        }

        if ($client_secret)
        {
            $criteria['secret'] = $client_secret; 
        }

        $results = $this->mongo->oauth_clients->findOne($criteria);

        if ($results)
        {
            $client = new ClientEntity($this->server);

            $client->hydrate([
                'id'   => $results['_id']->{'$id'},
                'name' => $results['name']
            ]);

            return $client;
        }

        return null;
    }

    public function getBySession(SessionEntity $session)
    {
        $sql = 'SELECT oauth_clients.id, oauth_clients.name'
             . ' FROM oauth_clients'
             . ' INNER JOIN oauth_sessions'
             . ' ON oauth_clients.id = oauth_sessions.client_id'
             . ' WHERE oauth_sessions.id = ?';

        $results = $this->db->fetch($sql, [$session->getId()]);

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
}

