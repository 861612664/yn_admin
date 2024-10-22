<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use think\facade\View;
use think\facade\Session;

use app\common\model\SystemAdminModel;
use app\common\model\SystemLogModel;
use app\common\model\SystemConfigModel;

class Login extends Base
{
    
    
    public function index()
    {
        $icp = SystemConfigModel::where([['key','=','icp']])->value('value');
        
        View::assign('icp',$icp);
        return View::fetch();
    }

    public function handleLogin(){
        $params = request()->param();
        $modelClass = new SystemAdminModel();

        if(!captcha_check($params['captcha']))
        {
            // 验证失败
            showjson(1,'验证码错误');
        }
        
        $admin = SystemAdminModel::where('name', '=', $params['name'])->find();

        if(empty($admin)){
            showjson(1,'账号不存在');
        }
        if($admin['state']==1){
            showjson(1,'账号已冻结');
        }
        if(md5(md5($params['pwd']).$admin['salt']) != $admin['pwd']){
            showjson(1,'密码错误');
        }
        $up_data = [
            'login_time' => date('Y-m-d H:i:s'),
            'login_ip' =>request()->ip()
        ];
        SystemAdminModel::where('id',$admin['id'])->update($up_data);
        Session::set('admin', $admin);

        //清除90天前的日志
        SystemLogModel::where([['create_time','<',time()-(90*86400)]])->delete();
        $response = array(
            'code' => 0,
            'msg' =>'登录成功',
        );
        echo json_encode($response);
       
    }


    
    
    
    
}
