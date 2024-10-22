<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\controller\ExportExcel;
use app\common\model\ArticleModel;
use app\common\model\ArticleClassifyModel;
use think\facade\View;


class Article extends Base
{
    protected $modelClass;
    public function __construct(\think\App $app) {
        parent::__construct($app);
        $this->modelClass = new ArticleModel();
        
        $article_classify_list = ArticleClassifyModel::getInstance()->getSelectList([],['create_time'=>'desc']);
        View::assign('article_classify_list',$article_classify_list);
    }
    
    public function index(){
        View::assign('controller',request()->controller());
        return View::fetch();
    }
    
    public function getList(){
        $params = request()->param();
        $where = [];
        if(!empty($params['title'])){
            $where[] = ['title','like','%'.$params['title'].'%'];
        }
        if(!empty($params['ac_id'])){
            $where[] = ['ac_id','=',$params['ac_id']];
        }
       
        $appends = empty($this->modelClass->appends)?[]:$this->modelClass->appends;
        $this_sql = $this->modelClass->with('article_classify');
        if(!empty($params['article_classify'])){
            $article_classify_where = [];
            if(!empty($params['article_classify']['name'])){
                $article_classify_where[] = ['name','like','%'.$params['article_classify']['name'].'%'];
            }
            if(!empty($article_classify_where)){
                $this_sql->hasWhere('articleClassify', $article_classify_where);
            }
        }
        if(!empty($this->modelClass->delete_field_exist)){
            $where[] = ['is_delete','=',0];
        }
        $this_sql->where($where)
            ->order(['sort'=>'desc'])
            ->append($appends);
        if(empty($params['export'])){
            //页面展示
            $list = $this_sql->paginate([
                'list_rows'=> $params['limit'],
                'page' => $params['page'],
            ])->toArray();
            exit(json_encode($list));
        }else{
            //导出
            $list = $this_sql->select()->toArray();
            $th = [
                ['field'=>'title','text'=>'标题','width'=>'20'],
                ['field'=>'article_classify.name','text'=>'分类名称','width'=>'20'],
                ['field'=>'is_show','text'=>'是否显示','width'=>'20'],
                ['field'=>'sort','text'=>'排序','width'=>'20'],
                ['field'=>'create_time','text'=>'创建时间','width'=>'20'],
            ];
            ExportExcel::export($th,$list,'分类');
        }
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
        $res = false;
        if(!empty($params['id'])){
            $res = $this->modelClass->getInstance()->del($params['id']);
        }else if(!empty($params['pl'])){
            foreach ($params['pl'] as $k =>$v){
                $res = $this->modelClass->getInstance()->del($v[$this->modelClass->pk]);
            }
        }
        
        if($res){
            showjson(0,'操作成功','');
        }else{
            showjson(1,'操作失败','');
        }
    }
    
}
