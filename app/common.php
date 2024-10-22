<?php
// 应用公共文件

function media_url($img){
    if(empty($img)){
        return '';
    }
    if(substr($img,0,4)=='http'){
        return $img;
    }
    if(config('app.cos_open')){
        return config('app.cos_url').$img;
    }else{
        return config('app.app_host').$img;
    }
}


/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function yn_curl($url,$params=false,$ispost=1){
    $httpInfo = array();
    $ch = curl_init();
 
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}

//微信昵称为特殊表情字符 出错问题
function removeByte4($str)
{
    return preg_replace('/[\xF0-\xF7].../s','', $str);
}

/**
 * 随机数  token
 */
function randomFromDev($len = 16)
{
    $chars='abcdefghijklmnopqrstuvwxyz0123456789';
    $str='';
    for($i=0;$i<$len;$i++){
        $str.=substr($chars,mt_rand(0,strlen($chars)-1),1);
    }
    return $str;
}

function createOrderSN($prefix='sn'){
    return $prefix.'_'.date('mdhis').randomFromDev(4);
} 

/**
 * 获取周一时间戳
 */
function monday(){
    $timestamp = time();
    $monday_date = date('Y-m-d', (time() - ((date('w',time()) == 0 ? 7 : date('w',time())) - 1) * 24 * 3600));
    return strtotime($monday_date);
}
/**
 * 获取周几
 */
function week_num($time){
    $weekday = date('N',$time);
    $weekdayNames = array(
        '1' => '周一',
        '2' => '周二',
        '3' => '周三',
        '4' => '周四',
        '5' => '周五',
        '6' => '周六',
        '7' => '周日'
    );
    return $weekdayNames[$weekday];
}

//距离显示
function distance($distance){
    if($distance<0.5){
        return '不足500米';
    }elseif($distance<1){
        return '约'.round($distance*1000).'米';
    }elseif($distance<10){
        return '约'.round($distance,1).'公里';
    }else{
        return '约'.round($distance).'公里';
    }
}
function distance_mi($distance){
    if($distance<1000){
        return $distance.'米';
    }else{
        return '距离约'.round($distance/1000,1).'公里';
    }
}

//通过经纬度计算
function get_distance($from,$to,$km=true,$decimal=2){
    sort($from);
    sort($to);
    $EARTH_RADIUS = 6370.996; // 地球半径系数
    
    $distance = $EARTH_RADIUS*2*asin(sqrt(pow(sin( ($from[0]*pi()/180-$to[0]*pi()/180)/2),2)+cos($from[0]*pi()/180)*cos($to[0]*pi()/180)* pow(sin( ($from[1]*pi()/180-$to[1]*pi()/180)/2),2)))*1000;
    
    if($km){
        $distance = $distance / 1000;
    }
 
    return round($distance, $decimal);
}

//时间 转 时间戳
function time_to_num($time){
    return strtotime('2000-01-01 '.$time)-strtotime('2000-01-01');
}

//时间戳 转 时间
function num_to_time($num){
    return date('H:i',strtotime('2000-01-01')+$num);
}