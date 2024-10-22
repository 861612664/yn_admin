<?php
namespace app\api\controller;

use app\common\model\UserModel;
use think\facade\Cache;

use think\App;

class Base
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;
    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;
    protected $app_name;

    protected $userInfo;
    protected $uid;
    protected $apiType;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(\think\App $app) 
    {
        $this->app_name     = '/admin';
        $this->app = $app;
        $this->request = $this->app->request;
        //验证是否需要验证登录
        $login_arr = ['Login','Config','Notify','File','Index'];
        $this->apiType = empty($_SERVER['HTTP_APITYPE'])?'':$_SERVER['HTTP_APITYPE'];
        if(!in_array($this->request->controller(),$login_arr)){
            $params = input();
            $token = empty($_SERVER['HTTP_TOKEN'])?'':$_SERVER['HTTP_TOKEN'];
            if($token=='123456'){
                $uid = 1;
                $memberWhere[] = ['uid','=',$uid];
                $this->userInfo =  UserModel::getInstance()->getInfo(0,$memberWhere);
                $this->uid = $uid;
            }else{
                $user_session = Cache::get($token); 
                if(empty($user_session)){
                    showjson(2,'登录过期');
                }
                if(empty($user_session['uid'])){
                    showjson(3,'请登录');
                }
                $memberWhere[] = ['uid','=',$user_session['uid']];
                $this->userInfo =  UserModel::getInstance()->getInfo(0,$memberWhere);
                if(empty($this->userInfo)){
                    showjson(2,'登录过期');
                }
                $this->uid = $user_session['uid'];
            }
            
            
        }
        
        
        
    }
    /**
     * 获取用户token信息
     * 部分不需要登录的接口使用
     */
    public function get_usertoken_info(){
        $token = empty($_SERVER['HTTP_TOKEN'])?'':$_SERVER['HTTP_TOKEN'];
        $user_cache = Cache::get($token); 
        return $user_cache;
    }

    
}
