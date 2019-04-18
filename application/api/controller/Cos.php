<?php


namespace app\api\controller;

use think\Controller;

class Cos extends Controller
{
    public function getAuth(){
        // 获取前端过来的参数
        $params = file_get_contents("php://input");
        $pathname = isset($params['pathname']) ? $params['pathname'] : '/';
        $method = isset($params['method']) ? $params['method'] : 'get';
        $query = isset($params['query']) ? $params['query'] : array();
        $headers = isset($params['headers']) ? $params['headers'] : array();

        // 返回数据给前端
        header('Content-Type: text/plain');
        header('Allow-Control-Allow-Origin: http://127.0.0.1'); // 这里修改允许跨域访问的网站
        header('Allow-Control-Allow-Headers: origin,accept,content-type');
        return $this->cos_sign($method, $pathname, $query, $headers);
    }


    /*
     * 获取签名
     * @param string $method 请求类型 method
     * @param string $pathname 文件名称
     * @param array $query query参数
     * @param array $headers headers
     * @return string 签名字符串
     */
    public function cos_sign($method, $pathname, $query, $headers){//cos桶签名算法

        // 整理参数
        !$query && ($query = array());
        !$headers && ($headers = array());
        $method = strtolower($method ? $method : 'get');
        $pathname = $pathname ? $pathname : '/';
        substr($pathname, 0, 1) != '/' && ($pathname = '/' . $pathname);
        //签名算法
//        $sign =
//            "q-sign-algorithm=sha1
//            &q-ak=[SecretID]
//            &q-sign-time=[SignTime]
//            &q-key-time=[KeyTime]
//            &q-header-list=[SignedHeaderList]
//            &q-url-param-list=[SignedParameterList]
//            &q-signature=[Signature]";

        $qSignAlgorithm = 'sha1';
        $qAk = "AKIDvfDZgiVTHk0b1eAbr9IG5itpeJvM6u4b";//q-ak  帐户 ID，即 SecretId，在访问管理控制台的 API 密钥管理 页面可获取
        $SecretKey = "SrAZt43sWyH9C4YovnSecLtnW9va58ez";

        // 签名有效起止时间
        $start = time() - 1;
        $end = $start + 600; // 签名过期时刻，600 秒后
        $qSignTime = "$start;$end"; //q-sign-time 本签名的有效起止时间，通过 Unix 时间戳 描述起始和结束时间，以秒为单位
        $qKeyTime  = "$start;$end"; //q-key-time 与 q-sign-time 值相同
        $qHeaderList = '';//q-header-list HTTP 请求头部。需从key:value中提取部分或全部 key，且 key 需转化为小写，并将多个 key 之间以字典顺序排序，如有多组 key，可用;连接。
        $qUrlParamList = '';//q-url-param-list HTTP 请求参数。需从 key=value 中提取部分或全部 key，且 key 需转化为小写，并将多个 key 之间以字典顺序排序，如有多组 key，可用;连接。


        // 步骤一：计算 SignKey
        $signKey = hash_hmac("sha1", $qKeyTime, $SecretKey);

        // 步骤二：构成 FormatString
        $formatString = implode("\n", array(strtolower($method), $pathname, $this->obj2str($query), $this->obj2str($headers), ''));

        // 步骤三：计算 StringToSign
        $stringToSign = implode("\n", array('sha1', $qSignTime, sha1($formatString), ''));

        // 步骤四：计算 Signature
        $qSignature = hash_hmac('sha1', $stringToSign, $signKey);

        // 步骤五：构造 Authorization
        $sign = implode('&', array(
            'q-sign-algorithm=' . $qSignAlgorithm,
            'q-ak=' . $qAk,
            'q-sign-time=' . $qSignTime,
            'q-key-time=' . $qKeyTime,
            'q-header-list=' . $qHeaderList,
            'q-url-param-list=' . $qUrlParamList,
            'q-signature=' . $qSignature
        ));
        return $sign;
    }

    // 工具方法
    public  function getObjectKeys($obj)
    {
        $list = array_keys($obj);
        sort($list);
        return $list;
    }

    public  function obj2str($obj)
    {
        $list = array();
        $keyList = $this->getObjectKeys($obj);
        $len = count($keyList);
        for ($i = 0; $i < $len; $i++) {
            $key = $keyList[$i];
            $val = isset($obj[$key]) ? $obj[$key] : '';
            $key = strtolower($key);
            $list[] = rawurlencode($key) . '=' . rawurlencode($val);
        }
        return implode('&', $list);
    }

}