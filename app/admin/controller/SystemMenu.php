<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\common\model\SystemMenuModel;
use think\facade\View;


class SystemMenu extends Base
{
    protected $modelClass;
    public function __construct(\think\App $app) {
        parent::__construct($app);
        $this->modelClass = new SystemMenuModel();
    }
    public function index()
    {
        View::assign('controller',request()->controller());
        return View::fetch();
    }
    public function getList(){
        $params = request()->param();
        $list = $this->modelClass->getInstance()->getPageList(1,1000,[['pid','=',0]],['sort'=>'desc']);
        $new_list = [];
        foreach($list['data'] as $k=>$v){
            $new_list[] = $v;
            $children = SystemMenuModel::getInstance()->getSelectList([['pid','=',$v['id']]],['sort'=>'desc']);
            foreach($children as $c_k => $c_v){
                $children[$c_k]['name'] = '┠'.$c_v['name'];
                $new_list[] = $children[$c_k];
            }
        }
        exit(json_encode($new_list));
    }
    
    public function setinfo(){

        $params = request()->param();

        if(empty($params['id'])){
            $editArr = $this->modelClass->getInstance()->getAllField();
        }else{
            $editArr = $this->modelClass->getInstance()->getInfo($params['id'])->toArray();
            $editArr['is_show'] = (string)$editArr['is_show'];
        }
       
        if(request()->isPost()){
            if(empty($params['id'])){
                //添加
                $this->modelClass->getInstance()->add($params);
                showjson(0,'操作成功','');
            }else{
                //修改
                $this->modelClass->getInstance()->edit($params);
                showjson(0,'操作成功','');
            }
        }
        $topList = $this->modelClass->getInstance()->getSelectList([['pid','=',0]],['sort'=>'desc']);
        View::assign('topList',$topList);

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
