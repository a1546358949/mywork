<?php


namespace app\api\controller;


use think\Controller;
use think\Db;
use think\Session;

class Login extends Controller
{
    //登陆
    public function login(){
        if(request()->isPost()){
            $username = input('Username');
            $data = Db::table('admin')->where('phone',$username)->find();
            if($data){
                if($data['password'] == input('Password')){
                    $version = Db::table('version')->limit(1)->order('create_time desc')->find();
                    $result['Data']['Token'] = $data['token'];//token
                    $result['Data']['Organization'] = $data['on_spot'];//所属机构
                    $result['Data']['Name'] = $data['name'];//账号名字
                    $result['Data']['HierarchyPower'] = $data['hierarchy_power'];//层级权限
                    $result['Data']['AccountPower'] = $data['account_power'];//账号权限
                    $result['Version'] = $version['version_pc'];
                    $result['Errno'] =  0;
                    $result['Errmsg'] =  '登录成功';
                    return json($result);
                }else{
                    $result['Errno'] =  10000;
                    $result['Errmsg'] =  '账号或密码错误';
                    return json($result);
                }
            }else{
                $result['Errno'] =  10000;
                $result['Errmsg'] =  '账号或密码错误';
                return json($result);
            }
        }
    }

    //token验证
    public function token($token){
        if($token == ''){
            $result['Errno'] =  10000;
            $result['Errmsg'] =  '请先登陆';
            return $result;
        }else{
            $res = Db::table('admin')->where('token',$token)->find();
            if($res){
                $result['Errno'] =  0;
                $result['Errmsg'] =  '请求成功';
            }else{
                $result['Errno'] =  10000;
                $result['Errmsg'] =  '请重新登陆';
            }

            return $result;
        }
    }
}