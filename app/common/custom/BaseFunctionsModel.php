<?php

namespace app\common\custom;
use think\facade\Db;

trait BaseFunctionsModel
{
    //获取单条记录
    //$res = CarouselModel::getInstance()->getInfo(3,[],'carousel_imgurl');
    //获取多条记录-无分页
    //$res = CarouselModel::getInstance()->getSelectList();
    //获取多条记录-分页
    //$res = CarouselModel::getInstance()->getPageList(2,20);
    //添加
    // $res = $this->modelClass->getInstance()->add($params)->toArray();
    //修改
    //$res = $this->modelClass->getInstance()->edit($params);
    //删除
    //$res = $this->modelClass->getInstance()->del(33);
    //获取全部字段
    //$res = $this->modelClass->getInstance()->getAllField();

    
    
    public function deleteWhere()
    {
        if ($this->is_delete==0 && $this->isExistField($this->delete_field)) {
            return $this->where($this->delete_field, 0);
        }
        return $this;
    }

    /**
     * 根据表主键key 查询一条数据
     * 2022年3月18日10:27:55
     */
    public function getInfo($id,$where=[],$filed='*'){
        if(!empty($id)){
            $where[] = [$this->pk,'=',$id];
        }
        if($filed != '*'){
            $appends = '';
        }else{
            $appends = empty($this->appends)?'':$this->appends;
        }
        $res = $this->Where($where)->field($filed)->find();
        //如果是空返回
        if(empty($res)){
            return '';
        }
        //有扩展字段 获取
        if(!empty($appends)){
            $res->append($appends);
        }

        return $res;
    }
    /**
     * 获取多条记录
     * 2022-3-18 11:15:42
     */
    public function getSelectList($where = [], $sort = ['create_time'=>'desc'], $filed = '*')
    {
        $appends = empty($this->appends)?[]:$this->appends;
        if($filed != '*'){
            $appends = [];
        }else{
            $appends = empty($this->appends)?[]:$this->appends;
        }
        return $this->deleteWhere()
                    ->where($where)
                    ->field($filed)
                    ->order($sort)
                    ->append($appends)
                    ->select()->toArray();
    }

    /**
     * 分页列表
     * 2022-3-18 12:05:27
     */
    public function getPageList($page = 1, $limit = 20, $where = [], $sort = ['create_time'=>'desc'], $filed = '*')
    {
        if($filed != '*'){
            $appends = [];
        }else{
            $appends = empty($this->appends)?[]:$this->appends;
        }
        $res = $this->deleteWhere()
            ->where($where)
            ->field($filed)
            ->order($sort)
            ->append($appends)
            ->paginate([
                'list_rows'=> $limit,
                'page' => $page,
            ])->toArray();
       
        /**
         * "total" => 24    //总条数
            "per_page" => 20    //每页显示数量
            "current_page" => 1 //当前页数
            "last_page" => 2    //最后一页数
            "data" =>           //列表
         */
        return $res;
    }

    
    /**
     * 添加
     */
    public function add(array $params = [])
    {
        $params['create_time'] = time();
        $model = $this->strict(false)->insertGetId($params);
        return $model;
    }
    /**
     * 修改 
     * $params包含主键不需要传$where
     * 2022-3-18 14:19:20
     */
    public function edit(array $params = [], array $where = [])
    {
        if(!empty($params['id'])){
            $params[$this->pk] = $params['id'];
        }
        
        if(empty($params[$this->pk]) && empty($where)){
            return false;
        }
        if(isset($params['create_time'])){
            unset($params['create_time']);
        }
        $res = $this->where($where)->update($this->setParams($params));
        return $res;
    }
    /**
     * 删除
     */
    public function del($id, $where=[]){
        if(!empty($id)){
            $where[] = [$this->pk,'=',$id];
        }
        if($this->is_delete==0 && $this->isExistField($this->delete_field) ){
            $res = $this->where($where)->update([$this->delete_field => time()]);
        }else{
            $res = $this->where($where)->delete();
        }
        return $res;
    }

    /**
     * 过滤录入数据表时，移除非表字段的数据
     *
     * @param $params
     *
     * @return array
     */
    protected function setParams(array $params) : array
    {
        $fields = $this->db(false)
                       ->getConnection()
                       ->getTableFields($this->getTable());
        foreach ($params as $key => $param) {
            if(!in_array($key, $fields)) {
                unset($params[$key]);
            }
            
        }
        if (in_array('update_time', $fields)) {
            $params['update_time'] = time();
        }
        return $params;
    }

    /**
     * 判断是否字符串是否是表中字段
     */
    protected function isExistField($field)
    {
        $fields = $this->db(false)
                       ->getConnection()
                       ->getTableFields($this->getTable());
        if(in_array($field,$fields)){
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * 获取表字段
     */
    public function getAllField($time_show=false){
        $field = Db::query("show COLUMNS FROM ".$this->getTable());
        $res = [];
        foreach($field as $k=>$v){
            if(!$time_show){
                if($v['Field']=='create_time' || $v['Field']=='update_time'){
                    continue;
                }
            }
            if($v['Key']=='PRI'){
                $res[$v['Field']] = 0;
            }elseif($v['Default']!=null){
                $res[$v['Field']] = $v['Default'];
            }else{
                $res[$v['Field']] = '';
            }
        }
        return $res;
    }

    
}
