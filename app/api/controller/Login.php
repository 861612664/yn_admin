<?php
namespace app\api\controller;

use app\api\controller\Base;

use app\common\model\UserModel;
use app\common\model\SystemConfigModel;

use think\facade\Cache;

use EasyWeChat\OfficialAccount\Application;
use think\exception\HttpResponseException;

class Login extends Base
{
    public function __construct(\think\App $app) {
        parent::__construct($app);
    }

    //微信小程序登录-未完成
    public function wxapp_login(){
        $params = request()->param();
        $wx_config = config('wx_easy');
        $app = new Application($wx_config);
        //$accessToken = $app->getAccessToken();
        $response = $app->getClient()->get('/sns/jscode2session', [
            'appid'=>$wx_config['app_id'],
            'secret'=>$wx_config['secret'],
            "js_code" => $params['code'],
            'grant_type'=>'authorization_code',
        ]);
        
        
        if ($response->isFailed()) {
            showjson(1,'登录失败'); 
        }
        $res_data = json_decode($response->getContent(),true);
        
        if(empty($res_data['openid'])){
            error_log(json_encode($res_data),3,__FILE__.'log.log');
            showjson(1,'登录失败',$res_data); 
        }
        $openid = $res_data['openid'];
        //查询用户数据
        $memberWhere[] = ['openid','=',$openid];
        $userInfo =  UserModel::getInstance()->getInfo(0,$memberWhere);
        if(empty($userInfo)){
            //新建
            $add_data = [
                'openid'=>$openid
            ];
            UserModel::getInstance()->add($add_data);
            $userInfo = UserModel::getInstance()->getInfo('',$memberWhere);
        }
        //创建token
        $token = md5(randomFromDev());
        $session_arr = [
            'uid'=>$userInfo['uid'],
            'openid'=>$openid,
            'token'=>$token
        ]; 
        Cache::set($token, $session_arr, 3600);
        
        $userInfo['token'] = $token;
        unset($userInfo['openid']);

        showjson(0,'成功',$userInfo); 
    }
    
    /**
     * 公众号登录跳转链接获取
     */
    public function wxh5_login_url(){
        $params = request()->param();
        $token = 'page'.md5(randomFromDev());
        $SystemConfigModel = new SystemConfigModel();
        $options = '';
        if(!empty($params['options'])){
            foreach($params['options'] as $k =>$v){
                $options .= '&'.$k.'='.$v;
            }

        }
        $session_arr = [
            'page'=>$params['page'],
            'options'=>$options,
        ]; 
        Cache::set($token, $session_arr, 60);
        $app_id = $SystemConfigModel->getSysInfo('wx_appid');
        $redirect_uri = config('app.app_host').'/api/login/wxh5_login_code/page/'.$token;
        $redirect_uri = urlencode($redirect_uri);
        $go_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$app_id.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redire';
        showjson(0,'成功',$go_url); 
    }

    /**
     * 通过code获取用户数据
     */
    public function wxh5_login_code(){
        $params = request()->param();
        $SystemConfigModel = new SystemConfigModel();
        $wx_h5 = [
            'app_id'=>$SystemConfigModel->getSysInfo('wx_appid'),
            'secret'=>$SystemConfigModel->getSysInfo('wx_appsecret'),
        ];
        $app = new Application($wx_h5);
        $oauth = $app->getOauth();
        // 获取 OAuth 授权用户信息
        $user = $oauth->userFromCode($params['code']);
        $user_res = $user->toArray();
        
        $openid = empty($user_res['token_response']['openid'])?'':$user_res['token_response']['openid'];
      
        if(empty($openid)){
            return $this->redirect(config('app.app_host').'/h5/#/pages/index/index')->send();
        }
        
        //查询用户数据
        $memberWhere[] = ['openid','=',$openid];
        $userInfo =  UserModel::getInstance()->getInfo(0,$memberWhere);
        if(empty($userInfo)){
            $uid = 0;
        }else{
            $uid = $userInfo['uid'];
        }
        //创建token
        $token = md5(randomFromDev());
        $session_arr = [
            'uid'=>$uid,
            'openid'=>$openid,
            'token'=>$token
        ]; 
        Cache::set($token, $session_arr, 864000);
        $redirect_info = Cache::get($params['page']); 
        if(empty($uid)){
            return $this->redirect(config('app.app_host').'/h5/#/pages/user/login?token='.$token)->send();
        }else{
            return $this->redirect(config('app.app_host').'/h5/#/'.$redirect_info['page'].'?token='.$token.$redirect_info['options'])->send();
        }
        
    }
    public function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }

    /**
     * 发送短信
     */
    public function send_sms(){
        $params = request()->param();
        $token = 'phone'.$params['phone'];
        $code = rand(10000,99999);
        $session_arr = [
            'time'=>time(),
            'code'=>$code,
            'type'=>$params['type']
        ];
        Cache::set($token, $session_arr, 600);
        $Alisms = new Alisms();
        $res = $Alisms->sendSmsCode($params['phone'],$code);
        if($res['code']){
            showjson(0,'发送成功',[]); 
        }else{
            showjson(1,'发送失败',[]); 
        }
    }

    /**
     * 注册
     */
    public function reg(){
        $params = request()->param();
        //查询手机号是否注册
        $user_info = UserModel::where([['phone'=>$params['phone']]])->find();
        //验证短信验证码
        $token = 'phone'.$params['phone'];
        $phone_send = Cache::get($token); 
        if(empty($phone_send)){
            showjson(1,'验证码超时或无效',[]); 
        }
        if($phone_send['code'] !=$params['code'] || $phone_send['type'] != 'reg'){
            showjson(1,'验证码错误',[]); 
        }
        
        if(!empty($user_info)){
            //showjson(1,'手机号已存在',[]);
            if($user_info['disable']){
                showjson(1,'账号已禁用',[]); 
            } 
            //登录 - 修改密码
            UserModel::where([['phone'=>$params['phone']]])->save([
                'pwd'=>md5(md5($params['pwd']).'hcd')
            ]);
        }else{
            //注册
            $uid = UserModel::getInstance()->add([
                'pwd'=>md5(md5($params['pwd']).'hcd'),
                'phone'=>$params['phone']
            ]);
            $user_info = UserModel::getInstance()->getInfo($uid);
        }
        $user_cache = $this->get_usertoken_info();
        if(!empty($user_cache['openid'])){
            UserModel::where([['phone'=>$params['phone']]])->save([
                'openid'=>$user_cache['openid']
            ]);
            $user_cache['uid'] = $user_info['uid'];
            $token = $user_cache['token'];
            Cache::set($token, $user_cache, 864000);
        }else{
            $token = md5(randomFromDev());
            $session_arr = [
                'uid'=>$user_info['uid'],
                'openid'=>'',
                'token'=>$token
            ]; 
            Cache::set($token, $session_arr, 864000);
        }
        showjson(0,'注册成功',['token'=>$token]); 
    }

    /**
     * 手机号登录
     */
    public function phone_login(){
        $params = request()->param();
        //查询手机号是否注册
        $user_info = UserModel::where([['phone','=',$params['phone']]])->find();
        if(empty($user_info)){
            showjson(1,'手机号未注册，请先注册',[]); 
        }
        if($params['login_type'] == 1){
            //验证短信验证码
            $token = 'phone'.$params['phone'];
            $phone_send = Cache::get($token); 
            if(empty($phone_send)){
                showjson(1,'验证码超时或无效',[]); 
            }
            if($phone_send['code'] !=$params['code'] || $phone_send['type'] != 'reg'){
                showjson(1,'验证码错误',[]); 
            }
        }else{
            //密码登录
            if($user_info['pwd'] != md5(md5($params['pwd']).'hcd')){
                showjson(1,'密码错误',[]); 
            }
        }
        $user_cache = $this->get_usertoken_info();
        if(!empty($user_cache['openid'])){
            UserModel::where([['phone','=',$params['phone']]])->save([
                'openid'=>$user_cache['openid']
            ]);
            $user_cache['uid'] = $user_info['uid'];
            $token = $user_cache['token'];
            Cache::set($token, $user_cache, 864000);
        }else{
            $token = md5(randomFromDev());
            $session_arr = [
                'uid'=>$user_info['uid'],
                'openid'=>'',
                'token'=>$token
            ]; 
            Cache::set($token, $session_arr, 864000);
        }
        showjson(0,'登录成功',['token'=>$token]); 
    }

    /**
     * 退出登录
     */
    public function quit_login(){
        $user_cache = $this->get_usertoken_info();
        if(empty($user_cache['uid'])){
            showjson(0,'退出成功',[]); 
        }
        UserModel::where([['uid','=',$user_cache['uid']]])->save([
            'openid'=>''
        ]);
        Cache::delete($user_cache['token']);
        showjson(0,'退出成功',[]); 
    }
    
    
    
}
