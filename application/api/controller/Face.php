<?php


namespace app\api\controller;

use app\api\model\Checking;
use think\Controller;
use think\Loader;

class Face extends Controller
{
    //百度人脸对比
    public function checkface(){
        if (request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 1){
                return json($result);
            }else {
                Loader::import('baidu-face.AipFace', EXTEND_PATH,'.php');//加载AipFace文件
                $APP_ID = '16054859';
                $API_KEY = '6AfLwSawAY7BCmecLoYcq8kd';
                $SECRET_KEY = '23VtrwhjqRhNLHZx9YhMZhLPiuFx4U9I';
                $aipFace = new \AipFace($APP_ID, $API_KEY, $SECRET_KEY);
                $image1 = input('img1');
                $image2 = input('img2');
                $data1 = [
                    "image" => base64_encode(file_get_contents($image1)),
                    "image_type" => "BASE64",
                    "face_type" => "LIVE",
                    "quality_control" => "LOW",
                    "liveness_control" => "HIGH"
                ];
                $data2 = [
                    "image" => base64_encode(file_get_contents($image2)),
                    "image_type" => "BASE64",
                    "face_type" => "LIVE",
                    "quality_control" => "LOW",
                    "liveness_control" => "HIGH"
                ];
                $reslist = $aipFace->match([$data1,$data2]);
                if (isset($reslist['result']['score'])){
                    if ($reslist['result']['score'] > 80){
                        $result['str'] = base64_encode($token.'1000');
                        $result['Errno'] =  0;
                        $result['Errmsg'] =  '头像检测成功';
                        return json($result);
                    }else{
                        $result['Errno'] =  10000;
                        $result['Errmsg'] =  '头像检测不符，请重拍';
                        return json($result);
                    }
                }else{
                    $result['Errno'] =  10000;
                    $result['Errmsg'] =  '头像检测不符，请重拍';
                    return json($result);
                }
            }
        }
    }
}