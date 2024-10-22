<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\common\model\SystemAdminModel;
use app\common\model\SystemMenuModel;
use think\facade\View;


class SystemAdmin extends Base
{
    protected $modelClass;
    public function __construct(\think\App $app) {
        parent::__construct($app);
        $this->modelClass = new SystemAdminModel();
    }
    public function index()
    {
        View::assign('controller',request()->controller());
        return View::fetch();
    }
    public function getList(){
        $params = request()->param();
        $list = $this->modelClass->getInstance()->getPageList($params['page'],$params['limit'],[],['id'=>'asc']);
        exit(json_encode($list));
    }
    
    public function setinfo(){

        $params = request()->param();

        if(empty($params['id'])){
            $editArr = $this->modelClass->getInstance()->getAllField();
            $editArr['menu'] = [];
        }else{
            $editArr = $this->modelClass->getInstance()->getInfo($params['id'])->toArray();
            $salt = $editArr['salt'];
            $editArr['state'] = (string)$editArr['state'];
            $editArr['menu'] = explode(',',$editArr['menu']);
            foreach($editArr['menu'] as &$v){
                $v = (int)$v;
            }
            $editArr['pwd'] = '';
            unset($editArr['salt']);
        }
       
        if(request()->isPost()){
            if($editArr['menu'] == 'all'){
                showjson(1,'超级管理员不允许修改','');
            }
            $params['menu'] = implode(',',$params['menu']);
            unset($params['login_time']);
            unset($params['login_ip']);

            //验证账号是否已存在
            $admin = SystemAdminModel::where('name', '=', $params['name'])->find();

            if(empty($params['id'])){
                //添加
                if(!empty($admin)){
                    showjson(1,'账号重复','');
                }
                $params['salt'] = randomFromDev(5);
                $params['pwd'] = md5(md5($params['pwd']).$params['salt']);
                $this->modelClass->getInstance()->add($params);
                showjson(0,'操作成功','');
            }else{
                //修改
                if($admin['id'] != $params['id']){
                    showjson(1,'账号重复','');
                }
                if(empty($params['pwd'])){
                    unset($params['pwd']);
                }else{
                    $params['pwd'] = md5(md5($params['pwd']).$salt);
                }
                $this->modelClass->getInstance()->edit($params);
                showjson(0,'操作成功','');
            }
        }
        $menu_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',0],['is_show','=',1]],['sort'=>'desc']);
        foreach($menu_list as &$v){
            $children_list = SystemMenuModel::getInstance()->getSelectList([['pid','=',$v['id']],['is_show','=',1]],['sort'=>'desc']);
            $v['children'] = $children_list;
        }
        
        View::assign('menu',$menu_list);
        View::assign('editArr',$editArr);
        $form_url = $this->app_name.'/'.request()->controller().'/'.request()->action();
        View::assign('form_url',$form_url);
        return View::fetch();
    }
    public function delinfo(){
        $params = request()->param();

        $res = $this->modelClass->getInstance()->del($params['id']);
        if($res){
            showjson(0,'操作成功','');
        }else{
            showjson(1,'操作失败','');
        }
    }
    
}
