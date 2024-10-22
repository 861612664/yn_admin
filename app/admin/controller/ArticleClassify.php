<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\common\model\ArticleClassifyModel;
use think\facade\View;


class ArticleClassify extends Base
{
    protected $modelClass;
    public function __construct(\think\App $app) {
        parent::__construct($app);
        $this->modelClass = new ArticleClassifyModel();
    }
    public function index()
    {
        View::assign('controller',request()->controller());
        return View::fetch();
    }
    public function getList(){
        $params = request()->param();
        $list = $this->modelClass->getInstance()->getPageList($params['page'],$params['limit'],[],['sort'=>'desc']);
        exit(json_encode($list));
    }
    
    public function setinfo(){
        $params = request()->param();
        
        if(request()->isPost()){
            if(empty($params[$this->modelClass->pk])){
                //添加
                $this->modelClass->getInstance()->add($params);
                showjson(0,'操作成功','');
            }else{
                //修改
                $this->modelClass->getInstance()->edit($params);
                showjson(0,'操作成功','');
            }
        }
        if(empty($params['id'])){
            $editArr = $this->modelClass->getInstance()->getAllField();
        }else{
            $editArr = $this->modelClass->getInstance()->getInfo($params['id'])->toArray();
            $editArr['is_show'] = (string)$editArr['is_show'];
        }
       
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
