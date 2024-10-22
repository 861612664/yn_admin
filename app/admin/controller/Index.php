<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\common\model\SystemMenuModel;
use app\common\model\SystemAdminModel;
use think\facade\View;
use think\facade\Session;


class Index extends Base
{
    public function index()
    {
        if($this->admin['menu']=='all'){
            $menu_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',0],['is_show','=',1]],['sort'=>'desc']);
        }else{
            $menu_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',0],['is_show','=',1],['id','in',$this->admin['menu']]],['sort'=>'desc']);
        }
        foreach($menu_list as &$v){
            $v['href'] = $v['paths'];
            $v['title'] = $v['name'];
            if($this->admin['menu']=='all'){
                $children_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',$v['id']],['is_show','=',1]],['sort'=>'desc']);
            }else{
                $children_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',$v['id']],['is_show','=',1],['id','in',$this->admin['menu']]],['sort'=>'desc']);
            }
            
            foreach($children_list as $ck=> $cv){
                $children_list[$ck]['href'] = $cv['paths'];
                $children_list[$ck]['title'] = $cv['name'];
                $children_list[$ck]['breadcrumb'] = [['text'=>$v['name']],['text'=>$cv['name']]];
            }
            $v['have_children'] = empty($children_list)?0:1;
            $v['children'] = $children_list;
            $v['breadcrumb'] = [['text'=>$v['name']]];
        }
        
        View::assign('menu',$menu_list);
        return View::fetch();
    }
    public function welcome(){
        return View::fetch();
    }
    /**
     * 修改密码
     */
    public function editPwd(){
        $params = request()->param();

        if(request()->isPost()){
            // 验证原密码是否正确
            $admin = SystemAdminModel::getInstance()->getInfo($this->admin_id);
            if(md5(md5($params['yuan_pwd']).$admin['salt']) != $admin['pwd']){
                showjson(1,'密码错误');
            }
            $data = [
                'id'=>$this->admin_id,
                'pwd'=>md5(md5($params['new_pwd']).$admin['salt'])
            ];
            SystemAdminModel::getInstance()->edit($data);
            showjson(0,'操作成功','');
        }
        return View::fetch();
    }
    /**
     * 退出
     */
    public function quit(){
        Session::delete('admin');
    }


   
}
