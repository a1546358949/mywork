<?php


namespace app\api\controller;


use app\api\model\Checking;
use think\Controller;
use think\Db;

class Point extends Controller
{
    //层级管理-首页
    public function index(){
        $result = [];
        if(request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $HierarchyList = $yanzheng->stops($token);
                $result['count'] = count($HierarchyList);
                if ($HierarchyList) {
                    foreach ($HierarchyList as $k => $v){
                        if ($v['status'] !== 2){
                            $result['HierarchyList'][$k]['id'] = $v['id'];
                            $result['HierarchyList'][$k]['SpotName'] = $v['spot_name'];
                            $result['HierarchyList'][$k]['SpotPlace'] = $v['location'];
                            $result['HierarchyList'][$k]['Spottask'] = $v['manager'];
                            $result['HierarchyList'][$k]['SpotPhone'] = $v['manager_phone'];
                            $result['HierarchyList'][$k]['SpotSuperior'] = $v['up_spot'];
                            $result['HierarchyList'][$k]['Level'] = $v['level'];
                        }
                    }
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '请求成功';
                }
            }
            return json($result);
        }
    }

    //层级管理-新增（修改）
    public function add_update(){
        $result = [];
        if (request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = input('id');
                $data['spot_name'] = input('SpotName');//层级点名称
                if ($data['spot_name'] == ''){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '工作点名称不能为空';
                    return json($result);
                }
                $data['location'] = input('SpotPlace');//层级地点
                if ($data['location'] == ''){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '地点不能为空';
                    return json($result);
                }
                $data['up_spot'] = input('SpotSuperior');//上级单位
                if ($data['up_spot'] == ''){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '上级单位不能为空';
                    return json($result);
                }
                $data['manager'] = input('Spottask');//层级负责人(非必填项)
                $data['manager_phone'] = input('SpotPhone');//层级负责人电话(非必填项)
                if ($data['manager'] !== ''){
                    $manager = Db::table('admin')->where('name',$data['manager'])->field('id')->find();
                    $data['manager_id'] = $manager['id'];//层级负责人(非必填项)
                }
                $p_id = Db::table('point')->where('spot_name', $data['up_spot'])->field('id')->find();
                $data['up_spot_id'] = $p_id['id'];
                if ($data['up_spot_id']) {
                   if ($id == ''){//id为空，此处为新增
                       //检测层级是否存在
                       $sql = Db::table('point')->where('spot_name', $data['spot_name'])->find();
                       if ($sql) {
                           $status = $sql['status'];
                           //判断层级是否已删除
                           if ($status == 2) {
                               $res = Db::table('point')->where('spot_name', $data['spot_name'])->update(['status' => 0]);
                               if ($res) {
                                   $result['Errno'] = 0;
                                   $result['Errmsg'] = '添加成功';
                               }
                           } else {
                               $result['Errno'] = 1;
                               $result['Errmsg'] = '工作点已存在';
                           }
                       } else {
                           $res = Db::table('point')->insert($data);
                           if ($res) {
                               $result['Errno'] = 0;
                               $result['Errmsg'] = '添加成功';
                           }
                       }
                   }else{//id不为空，此处为修改
                       $res = Db::table('point')->where('id', $id)->update($data);
                       if ($res) {
                           $result['Errno'] = 0;
                           $result['Errmsg'] = '修改成功';
                       }
                   }
                } else {
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '上级单位不存在';
                }
            }
        }
        return json($result);
    }

    //层级管理-删除
    public function delect(){
        $result = [];
        if (\request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = input('id');//层级点名称
                $res = Db::table('point')->where('id', $id)->update(['status' => 2]);
                if ($res) {
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '删除成功';
                }
            }
        }
        return json($result);
    }
}