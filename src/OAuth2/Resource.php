<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\AuthorizationServer;
use \League\OAuth2\Server\Grant\PasswordGrant;
use \Pickles\App\Model\User;

class Resource extends \Pickles\Resource
{
    public function __construct()
    {
        switch ($_REQUEST['request'])
        {
            case 'oauth/access_token':
                try
                {
                    $server = new AuthorizationServer;

                    $server->setSessionStorage(new SessionStorage);
                    $server->setAccessTokenStorage(new AccessTokenStorage);
                    $server->setClientStorage(new ClientStorage);
                    $server->setScopeStorage(new ScopeStorage);

                    $passwordGrant = new PasswordGrant;
                    $passwordGrant->setVerifyCredentialsCallback(function ($username, $password)
                    {
                        $user = new User(['email' => $username]);

                        return $user->count()
                            && password_verify($password, $user->record['password']);
                    });

                    $server->addGrantType($passwordGrant);

                    // @todo Add grant types listed in the config. Password is always added

                    $response = $server->issueAccessToken();
                }
                catch (\Exception $e)
                {
                    // @todo Set error code's accordingly.

                    throw new \Exception($e->getMessage(), $e->httpStatusCode);
                }

                break;

            default:
                throw new \Exception('Not Found.', 404);
                break;
        }
    }
}

