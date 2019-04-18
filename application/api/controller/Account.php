<?php


namespace app\api\controller;


use think\Controller;
use think\Db;

class Account extends Controller
{
    //账号管理-首页
    public function index(){
        if (request()->isPost()){
            $token = input('Token');
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $getData = new Power();
                $data = $getData->stops($token);
                foreach ($data as $k => $v){
                    $spot_id[] = $v['id'];
                }
                $spot_id = implode(",",$spot_id);
                $where['spot_id'] = ['in',$spot_id];
                $res = Db::table('admin')->where($where)->select();
                $result['count'] = count($res);
                if ($res){
                    foreach ($res as $k => $v){
                        $result['AccountList'][$k]['id'] =  $res[$k]['id'];
                        $result['AccountList'][$k]['AccountName'] =  $res[$k]['name'];
                        $result['AccountList'][$k]['AccountPlace'] =  $res[$k]['on_spot'];
                        $result['AccountList'][$k]['AccountPhone'] =  $res[$k]['phone'];
                        $result['AccountList'][$k]['AccountStart'] =  $res[$k]['status'];
                        $result['AccountList'][$k]['HierarchyPower'] =  $res[$k]['hierarchy_power'];
                        $result['AccountList'][$k]['AccountPower'] =  $res[$k]['account_power'];
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
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = input('id');
                $data['name'] = input('AccountName');//工作人员姓名
                $data['on_spot'] = input('AccountPlace');//工作人员所在单位（非必填项）
                $data['phone'] = input('AccountPhone');//工作人员联系电话
                $data['status'] = input('AccountStart');//账号状态0（正常）1（冻结）
                $data['hierarchy_power'] = input('HierarchyPower');//层级权限 (数据为布尔类型)
                $data['account_power'] = input('AccountPower');//账号权限 (数据为布尔类型)
                //查询所属治疗点id
                $spot_id = Db::table('point')->where('spot_name', $data['on_spot'])->field('id')->find();
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
                        } else {
                            $res = Db::table('admin')->insert($data);
                            if ($res) {
                                $result['Errno'] = 0;
                                $result['Errmsg'] = '添加成功';
                            }
                        }
                    }else{//id不为空，此处为修改
                        $data['update_time'] = time();
                        $res = Db::table('admin')->where('id', $id)->update($data);
                        if ($res) {
                            $result['Errno'] = 0;
                            $result['Errmsg'] = '修改成功';
                        }
                    }
                } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '所在单位不存在';
                }
            }
        }
        return json($result);
    }

    //账号管理-冻结
    public function delect(){
    if (\request()->isPost()){
        $token = input('Token');
        $Login = new Login();
        $result = $Login->token($token);
        if ($result['Errno'] == 1){
            return json($result);
        }else {
            $id = input('id');
            $res = Db::table('admin')->where('id', $id)->update(['status' => 1]);
            if ($res) {
                $result['Errno'] = 0;
                $result['Errmsg'] = '冻结成功';
            }
        }
    }
    return json($result);
}
}