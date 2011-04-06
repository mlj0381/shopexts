<?php
include_once('objectPage.php');
class ctl_roles extends objectPage{

    var $name='管理员角色';
    var $workground ='setting';
    var $actionView = 'admin/roles_action.html';
    var $object = 'admin/adminroles';
    var $actions= array(
        'edit'=>'编辑',
    );

    function add(){
        $this->pagedata['actions'] = $this->model->getAllActions();
        $this->page('admin/roles_item.html');
    }
    function edit($role_id){
        $this->pagedata['actions'] = $this->model->getAllActions();
        $this->pagedata['role'] = $this->model->instance($role_id);
        $this->pagedata['role']['actions'] = array_flip($this->pagedata['role']['actions']);
        $this->page('admin/roles_item.html');
    }

}
?>
