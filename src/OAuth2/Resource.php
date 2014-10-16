<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Exception\OAuthException;
use \League\OAuth2\Server\AuthorizationServer;
use \League\OAuth2\Server\Grant\PasswordGrant;
use \League\OAuth2\Server\Grant\RefreshTokenGrant;
use \Pickles\App\Models\User;
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
            case 'oauth/access_token':
                try
                {
                    $server = new AuthorizationServer;

                    $server->setSessionStorage(new SessionStorage);
                    $server->setAccessTokenStorage(new AccessTokenStorage);
                    $server->setClientStorage(new ClientStorage);
                    $server->setScopeStorage(new ScopeStorage);
                    $server->setRefreshTokenStorage(new RefreshTokenStorage);

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

                    // Defaults TTLs to 1 day and 1 week respectively
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
                            $grant = new PasswordGrant;
                            $grant->setAccessTokenTTL($token_ttl);

                            $grant->setVerifyCredentialsCallback(function ($username, $password)
                            {
                                $user = new User([
                                    'conditions' => [
                                        'email' => $username,
                                    ],
                                ]);

                                return $user->count()
                                    && password_verify($password, $user->record['password']);
                            });

                            break;

                        case 'refresh_token':
                            throw new \Exception('Not Implemented', 501);

                            // @todo Need to work through this, appears lib is busted
                            $grant = new RefreshTokenGrant;
                            //$grant->setAccessTokenTTL($refresh_ttl);
                            $server->addGrantType($grant);
                            break;
                    }

                    $server->addGrantType($grant);

                    // Adds the refresh token grant if enabled
                    if ($grant_type != 'refresh_token'
                        && in_array('refresh_token', $grants))
                    {
                        if (isset($config['ttl']['refresh_token']))
                        {
                            $refresh_ttl = $config['ttl']['refresh_token'];
                        }

                        $grant = new RefreshTokenGrant;
                        $grant->setAccessTokenTTL($refresh_ttl);
                        $server->addGrantType($grant);
                    }

                    $response = $server->issueAccessToken();

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

