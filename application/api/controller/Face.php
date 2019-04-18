<?php


namespace app\api\controller;


use think\Controller;

class Face extends Controller
{
    //人脸验证
    public function face(){
        $num = round(rand());
        $time = time();
        $arr = [
            'Action' => 'VerifyFace',//方法名
            'Url' => 'ins-09dx96dg',//待查询的实例ID
            'Limit' => 20,//最大允许输出
            'Nonce' => $num,//随机正整数
            'Offset' => 0,//偏移量
            'Region' => 'ap-guangzhou',//实例所在区域
            'SecretId' => 'AKIDvfDZgiVTHk0b1eAbr9IG5itpeJvM6u4b',//密钥Id
            'Timestamp' => $time,//当前时间戳
            'Version' => '2017-03-12',//接口版本号
        ];

        // 输入示例
        //https://iai.tencentcloudapi.com/?Action=VerifyFace
        //&Url=http://test.image.myqcloud.com/testA.jpg
        //&PersonId=11111111
        //&Version=2018-03-01
        //&<公共请求参数>

        // 执行API调用
        $url = 'https://recognition.image.myqcloud.com/face/verify';
        $response = $this->doHttpPost($url, $arr);
        return $response;
    }

    //生成签名
    public function sign()
    {
        $num = round(rand());
        $time = time();
        $arr = [
            'Action' => 'DescribeInstances',//方法名
            'InstanceIds.0' => 'ins-09dx96dg',//待查询的实例ID
            'Limit' => 20,//最大允许输出
            'Nonce' => $num,//随机正整数
            'Offset' => 0,//偏移量
            'Region' => 'ap-guangzhou',//实例所在区域
            'SecretId' => 'AKIDvfDZgiVTHk0b1eAbr9IG5itpeJvM6u4b',//密钥Id
            'Timestamp' => $time,//当前时间戳
            'Version' => '2017-03-12',//接口版本号
        ];
        //字典升序排序
        ksort($arr);
        //拼按URL键值对
        $str = '';
        foreach ($arr as $key => $value)
        {
            if ($value !== '')
            {
                $str .= $key . '=' . urlencode($value) . '&';
            }
        }
        //拼接字符串
        $string = "GETcvm.tencentcloudapi.com/?".$str;

        //生成签名
        $secretKey = 'SrAZt43sWyH9C4YovnSecLtnW9va58ez';
        $signStr = base64_encode(hash_hmac('sha1', $string, $secretKey, true));
        return $signStr;
    }

    // doHttpPost ：执行POST请求，并取回响应结果
// 参数说明
//   - $url   ：接口请求地址
//   - $params：完整接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
// 返回数据
//   - 返回false表示失败，否则表示API成功返回的HTTP BODY部分
    function doHttpPost($url, $params)
    {
        $curl = curl_init();

        $response = false;
        do
        {
            // 1. 设置HTTP URL (API地址)
            curl_setopt($curl, CURLOPT_URL, $url);

            // 2. 设置HTTP HEADER (表单POST)
            $head = array(
                'Content-Type: application/x-www-form-urlencoded'
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $head);

            // 3. 设置HTTP BODY (URL键值对)
            $body = http_build_query($params);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

            // 4. 调用API，获取响应结果
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_NOBODY, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            if ($response === false)
            {
                $response = false;
                break;
            }

            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code != 200)
            {
                $response = false;
                break;
            }
        } while (0);

        curl_close($curl);
        return $response;
    }

}