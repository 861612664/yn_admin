<?php
declare (strict_types = 1);

namespace app\index\controller;
use app\index\controller\Base;
use app\common\model\SystemConfigModel;
use think\facade\View;
use think\facade\Session;


class Index extends Base
{
    public function index()
    {
        $pingtai_name = SystemConfigModel::where([['key','=','pingtai_name']])->value('value');
        $icp = SystemConfigModel::where([['key','=','icp']])->value('value');
        
        View::assign('pingtai_name',$pingtai_name);
        View::assign('icp',$icp);
        return View::fetch();
    }
    
}
