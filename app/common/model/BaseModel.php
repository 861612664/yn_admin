<?php

namespace app\common\model;

use think\Model;
use app\common\custom\Instance;
use app\common\custom\BaseFunctionsModel;

class BaseModel extends Model
{
    // 单例
    use Instance;

    use BaseFunctionsModel;
    
    // 主键
    protected $pk = 'id';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    /**
     * 创建时间字段 false表示关闭
     *
     * @var false|string
     */
    protected $createTime = 'create_time';
    /**
     * 更新时间字段 false表示关闭
     *
     * @var false|string
     */
    protected $updateTime = 'update_time';
    /**
     * 自定义的软删除
     */
    public $is_delete = 0; //是否开启删除（1.开启删除，就是直接删除；0.假删除）
    public $delete_field = 'is_delete'; //删除字段

    




}
