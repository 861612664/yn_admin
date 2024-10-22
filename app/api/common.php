<?php

/**
 * 返回请求
 * @param $status 0正常 1错误 2登录过期或未登录
 * @param string $msg
 * @param array $data
 * @param bool $saveLog
 */
function showjson($status, $msg='', $data=array())
{
    $response = array(
        'code' => $status,
        'msg' =>$msg,
        'data' => $data
    );
    exit(json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
}

