<?php


namespace app\api\controller;


use app\api\model\Checking;
use think\Controller;
use think\Db;

class Account extends Controller
{
    //账号管理-首页
    public function index(){
        $result = [];
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();
            $result = $check->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $data = $check->stops($token);
                foreach ($data as $k => $v){
                    if($v['status'] == 0){
                        $spot_id[] = $v['id'];
                    }
                }
                $spot_id = implode(",",$spot_id);
                $where['spot_id'] = ['in',$spot_id];
                $res = Db::table('admin')->where($where)->select();
                $result['count'] = count($res);
                if ($res){
                    foreach ($res as $k => $v){
                        if($v['status'] == 0){
                            $result['AccountList'][$k]['id'] =  $res[$k]['id'];
                            $result['AccountList'][$k]['AccountName'] =  $res[$k]['name'];
                            $result['AccountList'][$k]['AccountPlace'] =  $res[$k]['on_spot'];
                            $result['AccountList'][$k]['AccountPhone'] =  $res[$k]['phone'];
                            $result['AccountList'][$k]['AccountStart'] =  $res[$k]['status'];
                            $result['AccountList'][$k]['HierarchyPower'] =  $res[$k]['hierarchy_power'];
                            $result['AccountList'][$k]['AccountPower'] =  $res[$k]['account_power'];
                        }
                    }
                    $result['Errno'] =  0;
                    $result['Errmsg'] =  '请求成功';
                }
            }
        }
        return json($result);
    }

    //账号管理-新增（修改）
    public function add_update(){
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();
            $result = $check->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = input('id');
                $data['name'] = input('AccountName');//工作人员姓名
                if ($data['name'] == ''){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '姓名不能为空';
                    return json($result);
                }
                $data['password'] = input('Password');//工作人员密码
                if ($data['password'] == ''){
                    $data['password'] = '123456';
                }
                $data['phone'] = input('AccountPhone');//工作人员手机号码
                $result = $check->phone($data['phone'],$id,$table='admin');//验证手机号
                if ($result['Errno'] == 10000){
                    return json($result);
                }
                $data['status'] = input('AccountStart');//账号状态0（正常）1（冻结）
                $data['hierarchy_power'] = input('HierarchyPower');//层级权限 (数据为数字类型)
                $data['account_power'] = input('AccountPower');//账号权限 (数据为数字类型)

                $data['on_spot'] = input('AccountPlace');//工作人员所在单位（非必填项）
                //查询所属治疗点id
                $where['spot_name'] = $data['on_spot'];
                $where['status'] = array('neq',2);
                $spot_id = Db::table('point')->where($where)->field('id')->find();
                $data['spot_id'] = $spot_id['id'];
                if ($data['spot_id']) {
                    if ($id == ''){//id为空，此处为新增
                        $data['create_time'] = time();
                        $data['update_time'] = time();
                        $data['token'] = md5($data['phone'] . $data['create_time']);
                        //检测账号是否存在
                        $sql = Db::table('admin')->where('phone', $data['phone'])->find();
                        if ($sql) {
                            $result['Errno'] = 10000;
                            $result['Errmsg'] = '账号已存在';
                            return json($result);
                        } else {
                            $res = Db::table('admin')->insert($data);
                            if ($res) {
                                $result['Errno'] = 0;
                                $result['Errmsg'] = '添加成功';
                                return json($result);
                            }
                        }
                    }else{//id不为空，此处为修改
                        $sql = Db::table('admin')->where('token',$token)->field('id,status,hierarchy_power,account_power')->find();//查询操作这信息
                        if($sql['id']  == $id){//如果操作者修改自身
                            if($sql['status'] == $data['status'] && $sql['hierarchy_power'] == $data['hierarchy_power'] && $sql['account_power'] == $data['account_power']){//判断是否修改自身权限
                                $res = Db::table('admin')->where('id', $id)->update($data);
                                if ($res) {
                                    $result['Errno'] = 0;
                                    $result['Errmsg'] = '修改成功';
                                    return json($result);
                                }else{
                                    $result['Errno'] = 10000;
                                    $result['Errmsg'] = '修改失败';
                                    return json($result);
                                }
                            }else{
                                $result['Errno'] = 10000;
                                $result['Errmsg'] = '不能更改自己的权限';
                                return json($result);
                            }
                        }else{
                            $data['update_time'] = time();
                            if ($data['status'] == 1){
                                $data['token'] = '';
                            }
                            $res = Db::table('admin')->where('id', $id)->update($data);
                            if ($res) {
                                $result['Errno'] = 0;
                                $result['Errmsg'] = '修改成功';
                                return json($result);
                            }else{
                                $result['Errno'] = 10000;
                                $result['Errmsg'] = '修改失败';
                                return json($result);
                            }
                        }
                    }
                } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '所在单位不存在';
                        return json($result);
                }
            }
        }
    }
}