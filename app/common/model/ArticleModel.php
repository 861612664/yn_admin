<?php 

namespace app\common\model; 
//文章表 

class ArticleModel extends BaseModel 
{ 
    protected  $name = 'article'; 
    public  $pk = 'id';
    
    public  $appends = ['pic_full'];
    public  $delete_field_exist = true;
    
    public function getPicFullAttr($value,$data)
    {
        return media_url($data['pic']);
    }
    
    public function articleClassify()
    {
        return $this->belongsTo(ArticleClassifyModel::class, 'ac_id','ac_id');
    }

} 
