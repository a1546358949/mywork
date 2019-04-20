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
            $data = Db::table('admin')->where('phone',$username)->find();//查询账号信息
            if($data){
                if ($data['status'] == 0){
                    if($data['password'] == input('Password')){//验证密码
                        $time = time();
                        $token = md5($username.$time);//生成新token
                        $new['token'] = $token;
                        $new['token_time'] = $time + 7200;//设置token有效时间
                        $sql = Db::table('admin')->where('phone',$username)->update($new);//更新token和token有效期
                        if ($sql){
                            $version = Db::table('version')->limit(1)->order('create_time desc')->find();//查询最新版本号
                            $result['Data']['Token'] = $token;//token
                            $result['Data']['Organization'] = $data['on_spot'];//所属机构
                            $result['Data']['Name'] = $data['name'];//账号名字
                            $result['Data']['HierarchyPower'] = $data['hierarchy_power'];//层级权限
                            $result['Data']['AccountPower'] = $data['account_power'];//账号权限
                            $result['Version'] = $version['version_pc'];//最新版本号
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
                }else{
                    $result['Errno'] =  10000;
                    $result['Errmsg'] =  '账号已冻结';
                    return json($result);
                }
            }else{
                $result['Errno'] =  10000;
                $result['Errmsg'] =  '用户不存在';
                return json($result);
            }
        }
    }
}