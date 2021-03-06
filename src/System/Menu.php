<?php

namespace Baiy\Cadmin\System;

use Baiy\Cadmin\Model\Menu as MenuModel;
use Exception;

class Menu extends Base
{
    public function lists()
    {
        return MenuModel::instance()->all();
    }

    public function sort($menus)
    {
        foreach ($menus as $menu) {
            $this->db->update(
                MenuModel::table(),
                ['sort' => $menu['sort']],
                ['id' => $menu['id']]
            );
        }
    }

    public function save($parent_id, $name, $url = "", $icon = "", $description = "", $id = 0)
    {
        if (empty($name)) {
            throw new Exception("菜单名称不能为空");
        }
        if (!empty($parent_id)) {
            $parent = $this->db->get(MenuModel::table(), "*", ['id' => $parent_id]);
            if (empty($parent)) {
                throw new Exception("父菜单不存在");
            }
            if (!empty($parent['url'])) {
                throw new Exception("父菜单不是目录类型菜单");
            }
        }
        if ($id) {
            $this->db->update(
                MenuModel::table(),
                compact('name', 'parent_id', 'url', 'icon', 'description'),
                compact('id')
            );
        } else {
            // 计算排序值
            $sort = $this->db->get(
                MenuModel::table(), 'sort', ['AND' => compact('parent_id'), 'ORDER' => ['sort' => 'DESC']]
            );
            $sort = $sort ? $sort + 1 : 0;
            $this->db->insert(MenuModel::table(), compact('name', 'parent_id', 'url', 'icon', 'description', 'sort'));
        }
    }

    public function remove($id)
    {
        if (empty($id)) {
            throw new Exception("参数错误");
        }
        MenuModel::instance()->delete($id);
    }
}
