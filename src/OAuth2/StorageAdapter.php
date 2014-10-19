<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Storage\Adapter;
use \Pickles\Config;
use \Pickles\Mongo;

class StorageAdapter extends Adapter
{
    protected $config;
    protected $mongo;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->mongo  = Mongo::getInstance();
    }
}

