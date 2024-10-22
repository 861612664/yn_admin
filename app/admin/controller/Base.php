<?php
namespace app\admin\controller;

use think\App;
use think\facade\View;
use think\exception\HttpResponseException;

use app\common\model\SystemAdminModel;
use app\common\model\SystemLogModel;

use think\facade\Request;

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

    protected $admin;
    protected $admin_id;

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
        $login_arr = ['Login','Config'];
        if(!in_array($this->request->controller(),$login_arr)){
            if(!session('?admin') && $this->request->controller()=='Index' && $this->request->baseFile()!='/index.php'){
                return $this->redirect($this->app_name.'/login/index')->send();
            }else if(!session('?admin')){
                return $this->redirect('/index.php');
            }
            $admin_session = session('admin');
            
            $admin_info = SystemAdminModel::getInstance()->getInfo($admin_session['id']);
            $this->admin = $admin_info;
            $this->admin_id = $admin_info['id'];
            View::assign('adminInfo',$admin_info);
        }
        $log_data = [
            'ip'=>request()->ip(),
            'admin_id'=>$this->admin_id,
            'path'=>$this->app_name.'/'.request()->controller().'/'.request()->action(),
            'params'=>json_encode(request()->param()),
        ];
        SystemLogModel::getInstance()->add($log_data);
        View::assign('srcVersion','1.0');
        View::assign('app_name',$this->app_name);
        
    }

    public function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }
    

   
    
}
