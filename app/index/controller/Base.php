<?php
namespace app\index\controller;

use think\App;
use think\facade\View;
use think\exception\HttpResponseException;

use app\common\model\SystemAdminModel;
use app\common\model\SystemLogModel;

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
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(\think\App $app) 
    {
        
    }

    
    

   
    
}
