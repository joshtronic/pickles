<?php

namespace Pickles\OAuth2;

use \OAuth2\GrantType\UserCredentials;
use \OAuth2\Request;
use \OAuth2\Response;
use \OAuth2\Server;
use \Pickles\Config;

class Resource extends \Pickles\Resource
{
    public function POST()
    {
        if (!isset($this->config['oauth'][$_SERVER['__version']]))
        {
            throw new \Exception('Forbidden.', 403);
        }
        elseif (!isset($_REQUEST['grant_type']))
        {
            throw new \Exception('Bad Request.', 400);
        }

        $config = $this->config['oauth'][$_SERVER['__version']];

        switch (substr($_REQUEST['request'], strlen($_SERVER['__version']) + 2))
        {
            case 'oauth2/token':
                try
                {
                    $grant_type = $_REQUEST['grant_type'];
                    $grants     = ['password'];

                    if (isset($config['grants']))
                    {
                        $grants = array_unique(array_merge($grants, $config['grants']));
                    }

                    if (!in_array($grant_type, $grants))
                    {
                        throw new \Exception('Unsupported grant type.', 403);
                    }

                    // @todo Defaults TTLs to 1 day and 1 week respectively
                    $token_ttl   = 3600;
                    $refresh_ttl = 604800;

                    if (isset($config['ttl']['access_token']))
                    {
                        $token_ttl = $config['ttl']['access_token'];
                    }

                    switch ($grant_type)
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
                            $storage = new Storage($this->mongo, ['user_table' => 'user']);
                            $server  = new Server($storage);

                            $server->addGrantType(new UserCredentials($storage));

                            $request  = Request::createFromGlobals();
                            $response = new Response;
                            $response = $server->handleTokenRequest($request, $response);
                            $body     = json_decode($response->getResponseBody(), true);

                            if (isset($body['error']))
                            {
                                $parameters = $response->getParameters();

                                throw new \Exception(
                                    $parameters['error_description'],
                                    $response->getStatusCode()
                                );
                            }

                            $response = $body;
                            break;

                        case 'refresh_token':
                            throw new \Exception('Not Implemented', 501);
                            break;
                    }

                    return $response;
                }
                catch (OAuthException $e)
                {
                    throw new \Exception($e->getMessage(), $e->httpStatusCode);
                }
                catch (\Exception $e)
                {
                    throw new \Exception($e->getMessage(), $e->getCode());
                }

                break;

            default:
                throw new \Exception('Not Found.', 404);
                break;
        }
    }
}

