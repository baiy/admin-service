<?php

namespace Baiy\Cadmin;

use Baiy\Cadmin\Model\Request as RequestMode;
use Baiy\Cadmin\Model\RequestRelate;
use Baiy\Cadmin\Model\Token;
use Baiy\Cadmin\Model\User;
use Baiy\Cadmin\Model\UserGroupRelate;
use Baiy\Cadmin\Model\UserRelate;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Context
{
    /** @var Admin */
    private $admin;
    /** @var Request */
    private $request;
    /** @var Response */
    private $response;

    // 请求配置信息
    private $requestConfig = [];
    // 当前用户信息
    private $user = [];

    public function __construct(ServerRequestInterface $request, Admin $admin)
    {
        $this->admin   = $admin;
        $this->request = new Request($request);
    }

    /**
     * 入口
     */
    public function run(): Response
    {
        try {
            $this->initRequest();

            $this->initUser();

            $this->checkAccess();

            $this->response = new Response('success', '操作成功', $this->dispatch());
        } catch (Throwable $e) {
            $this->response = new Response('error', $e->getMessage(), $e->getTrace());
        }

        return $this->response;
    }

    public function getUser(): array
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getRequestConfig(): array
    {
        return $this->requestConfig;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * 初始化请求数据
     * @throws Exception
     */
    private function initRequest()
    {
        $action = $this->getRequest()->input($this->admin->getInputActionName());
        if (empty($action)) {
            throw new Exception("action参数错误");
        }

        $request = RequestMode::instance()->getByAction($action);
        if (empty($request)) {
            throw new Exception("action 不存在");
        }
        $this->requestConfig = $request;
    }

    /**
     * 初始化请求数据
     * @throws Exception
     */
    private function initUser()
    {
        $token = $this->getRequest()->input($this->admin->getInputTokenName());
        if (empty($token)) {
            return;
        }

        $userId = Token::instance()->getUserId($token);
        if (empty($userId)) {
            return;
        }

        $user = User::instance()->getById($userId);
        if (!empty($user)) {
            // 移除密码字段
            unset($user['password']);
            $this->user = $user;
        }
    }

    /**
     * 检查权限
     * @throws Exception
     */
    private function checkAccess()
    {
        $requestId = $this->requestConfig['id'];
        if (in_array($requestId, $this->admin->getNoCheckLoginRequestIds())) {
            return;
        }

        if (empty($this->user)) {
            throw new Exception("未登录系统");
        }

        if (User::instance()->isDisabled($this->user)) {
            throw new Exception("用户已被禁用");
        }

        if (in_array($requestId, $this->admin->getOnlyLoginRequestIds())) {
            return;
        }

        $userGroupIds = UserRelate::instance()->groupIds($this->user['id']);
        if (empty($userGroupIds)) {
            throw new Exception("用户未分配用户组");
        }

        $authIds = RequestRelate::instance()->authIds($this->requestConfig['id']);
        if (empty($authIds)) {
            throw new Exception("请求未分配权限组");
        }

        if (!UserGroupRelate::instance()->check($userGroupIds, $authIds)) {
            throw new Exception("暂无权限");
        }
    }

    private function dispatch()
    {
        $dispatcher = $this->admin->getDispatcher($this->requestConfig['type']);
        return $dispatcher->execute($this);
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
