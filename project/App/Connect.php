<?php

namespace App;

use Core\MokeDB;
use Core\IDB;

class Connect
{
    public IDB $db;
    public function __construct()
    {
        $db = new MokeDB();
        $this->db = $db;
    }
}