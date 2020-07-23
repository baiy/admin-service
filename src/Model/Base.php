<?php

namespace Baiy\Cadmin\Model;

use Baiy\Cadmin\Db;

abstract class Base
{
    public function db()
    {
        return Db::instance();
    }
}