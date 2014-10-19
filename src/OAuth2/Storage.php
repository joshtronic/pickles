<?php

namespace Pickles\OAuth2;

use \OAuth2\Storage\Mongo;

class Storage extends Mongo
{
    private $mongo;

    public function __construct($connection, $config = [])
    {
        parent::__construct($connection, $config);

        $this->mongo = \Pickles\Mongo::getInstance();
    }

    public function getUser($email)
    {
        return $this->mongo->user->findOne(['email' => $email]);
    }

    public function getUserDetails($email)
    {
        if ($user = $this->getUser($email))
        {
            $user['user_id'] = $user['_id']->{'$id'};
        }

        return $user;
    }

    protected function checkPassword($user, $password)
    {
        return $user && password_verify($password, $user['password']);
    }
}

