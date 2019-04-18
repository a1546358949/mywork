<?php


namespace app\api\controller;


use think\Controller;
use think\Db;

class Power extends Controller
{
    //根据token查询所属单位及下辖单位
    public function stops($token){
        $res = Db::table('admin')->where('token',$token)->find();
        $spot_id = $res['spot_id'];
        $table = 'point';
        $pid = 'up_spot_id';
        $where['id'] = $spot_id;
        $sql = Db::table('point')->whereOr($where)->select();
        $data = $this->getData($spot_id,$table,$pid);
        $HierarchyList = array_merge($sql,$data);
        return $HierarchyList;
    }

    //递归
    public function getData($id,$table,$pid){
        static $arr=array();
        $data = Db::table($table)->where($pid,$id)->select();
        foreach($data as $key=>$value){
            $arr[] = $value;            //把内容存进去
            $this->getData($value['id'],$table,$pid);    //回调进行无线递归
        }
        return $arr;
    }

    //验证治疗卡号
    public function treat_num($treat_num){
        $len = strlen($treat_num);
        if ($len == 13){
            $result['Errno'] = 0;
            $result['Errmsg'] = '验证成功';
        }else{
            $result['Errno'] = 10000;
            $result['Errmsg'] = '治疗卡号错误';
        }
    }

    //验证身份证号
}