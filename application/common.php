<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
namespace app\api\common;


class Test
{
    public function responseSend(&$params)
    {
        // 响应头设置 我们就是通过设置header来跨域的 这就主要代码了 定义行为只是为了前台每次请求都能走这段代码
    	header('Access-Control-Allow-Origin:*');
    	header('Access-Control-Allow-Methods:*');
	    header('Access-Control-Allow-Headers:*');
//	    header('Access-Control-Allow-Credentials:false');
    }
}

