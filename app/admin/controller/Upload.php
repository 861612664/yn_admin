<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\ServiceResponseException;

use app\common\model\ImagesModel;

use think\facade\View;

class Upload extends Base
{
    
    /**
     * 富文本上传图片
     */
    public function saveFile(){
        $params = request()->param();
        $file = request()->file('file');
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'article', $file);
        $all_file_path = dirname(dirname(dirname(dirname(__FILE__))));
        $savename = '/storage/'.$savename;
        $img_res = getimagesize($all_file_path.'/public'.$savename);
        $img_type = [1 =>'gif',2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'BMP',7=>'TIFF',8=>'TIFF',9=>'JPC',10=>'JP2',11=>'JPX',12=>'JB2',13=>'SWC',14=>'IFF',15=>'WBMP',16=>'XBM'];
        if(empty($img_res[2]) || empty($img_type[$img_res[2]])){
            exit(json_encode(['code'=>1,'msg'=>'文件格式错误']));
        }
        if(empty($params['key'])){
            exit(json_encode(['location'=>$savename]));
        }else{
            exit(json_encode(['location'=>$savename,'key'=>$params['key']]));
        }
        
        
    }

    

    
    
    
    
}
