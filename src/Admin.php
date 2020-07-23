<?php

namespace Baiy\Cadmin;

use Baiy\Cadmin\Dispatch\Dispatch;
use Baiy\Cadmin\Dispatch\Dispatcher;
use Baiy\Cadmin\Password\Password;
use Baiy\Cadmin\Password\PasswrodDefault;
use Closure;
use PDO;
use Psr\Http\Message\ServerRequestInterface;

class Admin
{
    private $inputActionName = "_action";
    private $inputTokenName = "_token";
    /** @var array 无需登录请求ID */
    private $noCheckLoginRequestIds = [1];
    /** @var array 仅需登录请求ID */
    private $onlyLoginRequestIds = [2, 3, 4];
    /** @var Dispatch[] 请求调度器 */
    private $dispatchers = [];
    /** @var Password 密码生成对象 */
    private $password;

    public function __construct()
    {
        // 注册系统默认调用器
        $this->registerDispatcher(new Dispatcher());
        // 注册系统默认密码生成器
        $this->registerPassword(new PasswrodDefault());
    }

    // 运行入口
    public function run(ServerRequestInterface $request)
    {
        return (new Context($request, $this))->run();
    }

    /**
     * 设置数据库对象
     * @param  PDO|Closure  $pdo
     * @param  string  $tablePrefix  内置数据表前缀
     */
    public function db($pdo, $tablePrefix = "")
    {
        Db::initialize($pdo, $tablePrefix ?: "admin_");
    }

    public function addNoCheckLoginRequestId(int $id): void
    {
        $this->noCheckLoginRequestIds[] = $id;
    }

    public function addOnlyLoginRequestId(int $id): void
    {
        $this->onlyLoginRequestIds[] = $id;
    }

    public function getNoCheckLoginRequestIds(): array
    {
        return $this->noCheckLoginRequestIds;
    }

    public function getOnlyLoginRequestIds(): array
    {
        return $this->onlyLoginRequestIds;
    }

    public function registerDispatcher(Dispatch $dispatcher)
    {
        $this->dispatchers[$dispatcher->key()] = $dispatcher;
    }

    public function getDispatcher($key): Dispatch
    {
        if (!isset($this->dispatchers[$key])) {
            throw new \Exception(sprintf("未找到请求类型(%s)对应的调度程序", $key));
        }
        return $this->dispatchers[$key];
    }

    /**
     * @return Dispatch[]
     */
    public function allDispatcher()
    {
        return $this->dispatchers;
    }

    public function registerPassword(Password $password)
    {
        $this->password = $password;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getInputActionName(): string
    {
        return $this->inputActionName;
    }

    /**
     * @param  string  $name
     */
    public function setInputActionName(string $name): void
    {
        $this->inputActionName = $name;
    }

    /**
     * @return string
     */
    public function getInputTokenName(): string
    {
        return $this->inputTokenName;
    }

    /**
     * @param  string  $name
     */
    public function setInputTokenName(string $name): void
    {
        $this->inputTokenName = $name;
    }
}