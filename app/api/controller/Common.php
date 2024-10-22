<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\controller\Base;

use think\facade\Db;

class Common extends Base
{
    public function upload()
    {
        $params = request()->param();
        $file = request()->file('file');
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'api', $file);
        $all_file_path = dirname(dirname(dirname(dirname(__FILE__))));
        $savename = '/storage/'.$savename;
        $img_res = getimagesize($all_file_path.'/public'.$savename);
        $img_type = [1 =>'gif',2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'BMP',7=>'TIFF',8=>'TIFF',9=>'JPC',10=>'JP2',11=>'JPX',12=>'JB2',13=>'SWC',14=>'IFF',15=>'WBMP',16=>'XBM'];
        if(empty($img_res[2]) || empty($img_type[$img_res[2]])){
            showjson(1,'文件格式错误',[]);
        }

        $res['fullurl'] = media_url($savename);
        $res['url'] = $savename;
        showjson(0,'成功',$res); 
    }
    


}
