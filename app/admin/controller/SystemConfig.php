<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\common\model\SystemConfigModel;
use app\common\model\SystemConfigTypeModel;
use think\facade\View;


class SystemConfig extends Base
{
    protected $modelClass;
    public function __construct(\think\App $app) {
        parent::__construct($app);
        $this->modelClass = new SystemConfigModel();
    }
    
    public function index(){

        $params = request()->param();
       
       
        if(request()->isPost()){
            foreach($params as $k => $v){
                $date = [
                    'id'=>$v['id'],
                    'value'=>$v['value'],
                ];
                $this->modelClass->getInstance()->edit($v);
            }
            showjson(0,'操作成功','');
        }
        $type_arr = SystemConfigTypeModel::getInstance()->getSelectList([['is_show','=',1]],['sort'=>'desc']);
        $type_ids = SystemConfigTypeModel::where([['is_show','=',1]])->column('id');
        $editArr = $this->modelClass->getInstance()->getSelectList([['is_show','=',1],['type_id','in',$type_ids]],['sort'=>'desc']);
        
        View::assign('type_arr',$type_arr);
        View::assign('editArr',$editArr);
        $form_url = $this->app_name.'/'.request()->controller().'/'.request()->action();
        View::assign('form_url',$form_url);
        return View::fetch();
    }

    
    
}
