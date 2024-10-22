<?php
declare (strict_types = 1);

namespace app\admin\controller;
use app\admin\controller\Base;
use think\facade\View;


class Config extends Base
{
    /**
     * 基础配置
     */
    public function getConfig()
    {
        $config = [
            // 文件域名
            'oss_domain' => config('system.oss_domain'),
            // 网站名称
            'web_name' => config('system.web_name'),
            // 网站图标
            'web_favicon' => config('system.web_favicon'),
            // 网站logo
            'web_logo' => config('system.web_logo'),
            // 登录页
            'login_image' => config('system.login_image'),
            // 版权信息
            'copyright_config' => config('system.copyright_config'),
        ];
        showjson(1,'登录成功',$config);
    }
    /**
     * 公共头部加载
     */
    public function haeder(){
        return View::fetch('/haeder');
    }
}
