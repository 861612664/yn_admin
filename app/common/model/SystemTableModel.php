<?php 
namespace app\common\model;

class SystemTableModel extends BaseModel
{
    protected  $name = 'system_table';
    public  $pk = 'id';
    
    protected  $appends = ['exec_type_text'];

    public static $execType = [
            '0'=>'暂存',
            '1'=>'生成model',
            '2'=>'生成列表(含model)',
            '3'=>'增删改查(含model)'
        ];
    
    public function getExecTypeTextAttr($value,$data)
    {
        if(!empty(self::$execType[$data['exec_type']])){
            return self::$execType[$data['exec_type']];
        }else{
            return ' - ';
        }
    }
   
}