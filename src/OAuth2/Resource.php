<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\AuthorizationServer;
use \League\OAuth2\Server\Grant\PasswordGrant;
use \Pickles\App\Models\User;

class Resource extends \Pickles\Resource
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($this->config['oauth'][$_SERVER['__version']]))
        {
            throw new \Exception('Forbidden.', 403);
        }

        switch (substr($_REQUEST['request'], strlen($_SERVER['__version']) + 2))
        {
            case 'oauth/access_token':
                try
                {
                    $server = new AuthorizationServer;

                    $server->setSessionStorage(new SessionStorage);
                    $server->setAccessTokenStorage(new AccessTokenStorage);
                    $server->setClientStorage(new ClientStorage);
                    $server->setScopeStorage(new ScopeStorage);

                    switch ($_REQUEST['grant_type'])
                    {
                        case 'authorization_code':
                            throw new \Exception('Not Implemented', 501);
                            break;

                        case 'client_credentials':
                            throw new \Exception('Not Implemented', 501);
                            break;

                        case 'implicit':
                            throw new \Exception('Not Implemented', 501);
                            break;

                        case 'password':
                            $grant = new PasswordGrant;

                            $grant->setVerifyCredentialsCallback(function ($username, $password)
                            {
                                $user = new User(['email' => $username]);

                                return $user->count()
                                    && password_verify($password, $user->record['password']);
                            });

                            break;

                        case 'refresh_token':
                            throw new \Exception('Not Implemented', 501);
                            break;
                    }

                    $server->addGrantType($grant);

                    $response = $server->issueAccessToken();
                }
                catch (\Exception $e)
                {
                    throw new \Exception($e->getMessage(), $e->httpStatusCode);
                }

                break;

            default:
                throw new \Exception('Not Found.', 404);
                break;
        }
    }
}

