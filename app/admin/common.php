<?php
// 这是系统自动生成的公共文件
/**
     * 返回请求
     * @param $status 0正常 1错误 2登录过期或未登录
     * @param string $msg
     * @param array $data
     * @param bool $saveLog
     */
    function showjson($status, $msg='', $data=array(), $path='')
    {
        if($status==0){
            $show = 1;
        }
        $response = array(
            'code' => $status,
            'msg' =>$msg,
            'data' => $data,
            'path' =>$path
        );
        exit(json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    function textarea_js($name){
        $html = "<script>
		tinymce.init({
			selector: '#".$name."',
			language_url: '/admin/lib/tinymce/langs/zh-Hans.js', // 中文语言包路径
			language: 'zh-Hans', //注意大小写
			plugins: ' preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media  code codesample table charmap  pagebreak nonbreaking anchor insertdatetime advlist lists wordcount   help emoticons autosave indent2em autoresize formatpainter axupimgs',
			
			toolbar1: 'code undo redo | fontsize formatpainter forecolor backcolor bold italic underline | bold italic backcolor | ' +
			'alignleft aligncenter alignright alignjustify | ',
			toolbar2:'bullist numlist checklist outdent indent | removeformat | indent2em image media table ',
			height: 650, //编辑器高度
			min_height: 400,
			images_upload_url: '/admin/Upload/saveFile',
			image_description: false,
            image_dimensions: false,
			setup: function (editor) {
				editor.on('blur', function () {
					tinyMCE.activeEditor.save();
				});
			}
		});
	    </script>";
        echo $html;
    }