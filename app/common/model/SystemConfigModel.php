<?php 
namespace app\common\model;

class SystemConfigModel extends BaseModel
{
    protected  $name = 'system_config';
    public  $pk = 'id';

    public static function getSysInfo($key){
        $info = self::where([['key','=',$key]])->find();
        if($info['type']==3){
            $info['value'] = media_url($info['value']);
        }
        if($info['type']==5){
            $info['value'] = str_replace('../../storage',config('app.app_host').'/storage',$info['value']);
            $info['value'] = str_replace('<img ',"<img style='max-width:100%;height:auto;display:block;'",$info['value']);
            
        }
        return $info['value'];
    }
}
