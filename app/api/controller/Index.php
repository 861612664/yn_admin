<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\controller\Base;
use app\common\model\AdvModel;
use app\common\model\BannerModel;
use app\common\model\GoodsModel;
use app\common\model\NavModel;
use app\common\model\StaffModel;
use app\common\model\SystemConfigModel;

use think\facade\Db;

class Index extends Base
{
    public function index()
    {
        // $params = request()->param();
        $banner = BannerModel::getInstance()->getSelectList([],['sort'=>'desc']);
        $nav = NavModel::getInstance()->getSelectList([],['sort'=>'desc']);
        $adv = AdvModel::getInstance()->getInfo('',[['position','=',1]]);
        $goods = GoodsModel::getInstance()->getSelectList([],['sort'=>'desc']);
        $res = [
            'banner'=>$banner,
            'nav'=>$nav,
            'adv'=>$adv,
            'goods'=>$goods,
        ];
        showjson(0,'成功',$res); 
    }
    /**
     * 首页推荐技师
     */
    public function staff_recom()
    {
        $params = request()->param();
        if(empty($params['latitude']) || empty($params['longitude'])){
            showjson(0,'缺少经纬度',[]); 
        }
        $q_staff_distance = SystemConfigModel::getSysInfo('q_distance');
        $table_name = config('database.connections.mysql.prefix').'staff';
        $res_nominate = Db::query("SELECT *,
                6371 * ACOS(
                    COS(RADIANS(".$params['latitude'].")) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(".$params['longitude'].")) +
                    SIN(RADIANS(".$params['latitude'].")) * SIN(RADIANS(latitude))
                ) AS distance
            FROM ".$table_name." where disable=0 and nominate=1 having distance<".(float)$q_staff_distance."
            ORDER BY distance ASC LIMIT 5");
        $limit = 5-count($res_nominate);
        $res_no_nominate = Db::query("SELECT *,
                6371 * ACOS(
                    COS(RADIANS(".$params['latitude'].")) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(".$params['longitude'].")) +
                    SIN(RADIANS(".$params['latitude'].")) * SIN(RADIANS(latitude))
                ) AS distance
            FROM ".$table_name."  where  disable=0 and nominate=0 having distance<".(float)$q_staff_distance."
            ORDER BY distance ASC LIMIT ".$limit);
        $res = array_merge($res_nominate,$res_no_nominate);
        $StaffModel = new StaffModel();
        foreach($res as &$v){
            $v['distance'] = round($v['distance'],2);
            $v['distance_text'] = distance($v['distance']);
            $v['avatarurl'] = media_url($v['avatarurl']);
            $v['yuyue_time'] = $StaffModel->yuyue_time($v);
            $v['tag_text'] = empty($v['tag_text'])?[]:explode(',',$v['tag_text']);
        }
        unset($v);
        showjson(0,'成功',$res); 
    }
    /**
     * 技师列表检索条件
     */
    public function staff_search_page()
    {
        $q_distance_select = SystemConfigModel::getSysInfo('q_distance_select');
        $res['distance_select'] = explode(',',$q_distance_select);
        showjson(0,'成功',$res); 
    }
    /**
     * 技师列表
     */
    public function staff_search()
    {
        $params = request()->param();
        if(empty($params['latitude']) || empty($params['longitude'])){
            showjson(0,'缺少经纬度',[]); 
        }
        $q_staff_distance = SystemConfigModel::getSysInfo('q_distance');
        $table_name = config('database.connections.mysql.prefix').'staff';
        //是否推荐技师在最上面 1先查询推荐 0不查询推荐 2混合查询
        $nominate = 1;
        if(!empty($params['page']) && $params['page']>1){
            //仅第一页和部分条件下显示
            $nominate = 0;
        }
        //处理排序
        $order_type = empty($params['order_type'])?'desc':'asc';
        if(empty($params['order_field'])){
            $order_by = " ORDER BY s_id ASC ";
        }elseif($params['order_field']=='zonghe'){
            $order_by = " ORDER BY s_id ASC ";
        }elseif($params['order_field']=='juli'){
            $nominate = 2;
            $order_by = " ORDER BY distance ASC ";
        }elseif($params['order_field']=='dianzan'){
            $nominate = 2;
            $order_by = " ORDER BY like_num desc ";
        }elseif($params['order_field']=='shoucang'){
            $nominate = 2;
            $order_by = " ORDER BY collect_num desc ";
        }elseif($params['order_field']=='fuwu'){
            $nominate = 2;
            $order_by = " ORDER BY order_num ".$order_type." ";
        }
        //条件查询
        $having = ' having distance<='.(float)$q_staff_distance.' ';
        if($nominate==1){
            $where = ' disable=0 and nominate=0 ';
        }else{
            $where = ' disable=0 ';
        }
        if(!empty($params['keyword'])){
            $where .= " and nickname like '%".$params['keyword']."%' ";
        }
        if(!empty($params['juli'])){
            $having = ' having distance<='.(float)$params['juli'].' ';
        }
        if(!empty($params['pingfen'])){
            if($params['pingfen']=='hao'){
                $q_opinion_hao = SystemConfigModel::getSysInfo('q_opinion_hao');
                $where .= " and good_opinion >=".(float)$q_opinion_hao.' ' ;
            }else if($params['pingfen']=='zhong'){
                $q_opinion_hao = SystemConfigModel::getSysInfo('q_opinion_hao');
                $q_opinion_zhong = SystemConfigModel::getSysInfo('q_opinion_zhong');
                $where .= " and good_opinion <=".(float)$q_opinion_hao." and good_opinion >=".(float)$q_opinion_zhong.' ';
            }else if($params['pingfen']=='cha'){
                $q_opinion_zhong = SystemConfigModel::getSysInfo('q_opinion_zhong');
                $q_opinion_cha = SystemConfigModel::getSysInfo('q_opinion_cha');
                $where .= " and good_opinion <=".(float)$q_opinion_zhong." and good_opinion >=".(float)$q_opinion_cha.' ';
            }
        }

        
        $limit = 10;
        $page = empty($params['page'])?0:$params['page']-1;
        $limit = ' LIMIT '.$page*$limit.','.$limit;
        $res_nominate = [];
        if($nominate==1){
            $res_nominate = Db::query("SELECT *,
            6371 * ACOS(
                COS(RADIANS(".$params['latitude'].")) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(".$params['longitude'].")) +
                SIN(RADIANS(".$params['latitude'].")) * SIN(RADIANS(latitude))
            ) AS distance
            FROM ".$table_name." where disable=0 and nominate=1 ".$having.$order_by);
        }
       
        
        $res_no_nominate = Db::query("SELECT *,
                6371 * ACOS(
                    COS(RADIANS(".$params['latitude'].")) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(".$params['longitude'].")) +
                    SIN(RADIANS(".$params['latitude'].")) * SIN(RADIANS(latitude))
                ) AS distance
            FROM ".$table_name."  where ".$where.$having.$order_by.$limit);
        $res = array_merge($res_nominate,$res_no_nominate);
        $StaffModel = new StaffModel();
        foreach($res as &$v){
            $v['distance'] = round($v['distance'],2);
            $v['distance_text'] = distance($v['distance']);
            $v['avatarurl'] = media_url($v['avatarurl']);
            $v['yuyue_time'] = $StaffModel->yuyue_time($v);
            $v['tag_text'] = empty($v['tag_text'])?[]:explode(',',$v['tag_text']);
        }
        unset($v);
        showjson(0,'成功',$res); 
    }


}
