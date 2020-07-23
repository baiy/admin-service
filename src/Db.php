<?php

namespace Baiy\Cadmin;

use Closure;
use Medoo\Medoo;
use PDO;
use Swoole\Coroutine;

class Db extends Medoo
{
    private static $_pdo;
    /** @var Db[] */
    private static $instances = [];

    private static $tablePrefix = "";

    /**
     * @param  PDO|Closure  $pdo
     */
    public static function initialize($pod, $tablePrefix)
    {
        self::$_pdo        = $pod;
        self::$tablePrefix = $tablePrefix;
    }

    /**
     * @return Db
     */
    public static function instance()
    {
        $key = 0;
        if (extension_loaded('swoole') && Coroutine::getCid() > 0) {
            $key = Coroutine::getCid();
            Coroutine::defer(function () use ($key) {
                unset(self::$instances[$key]);
            });
        }
        if (!isset(self::$instances[$key])) {
            $pdo = self::$_pdo;
            if ($pdo instanceof Closure) {
                $pdo = call_user_func($pdo);
            }
            self::$instances[$key] = new Db(['database_type' => 'mysql', 'pdo' => $pdo]);
        }
        return self::$instances[$key];
    }

    public static function getTablePrefix(): string
    {
        return self::$tablePrefix;
    }
}