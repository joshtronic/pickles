<?php

namespace Pickles\OAuth2;

use \League\OAuth2\Server\Storage\Adapter;
use \Pickles\Config;
use \Pickles\Database;

class StorageAdapter extends Adapter
{
    protected $config;
    protected $db;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->db     = Database::getInstance();
    }
}

