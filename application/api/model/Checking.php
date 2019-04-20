<?php


namespace app\api\model;

use think\Db;
use think\Model;

class Checking extends Model
{
    //根据token查询所属单位及下辖单位
    public function stops($token){
        $res = Db::table('admin')->where('token',$token)->find();
        $spot_id = $res['spot_id'];
        $table = 'point';
        $pid = 'up_spot_id';
        $where['id'] = $spot_id;
        $sql = Db::table('point')->whereOr($where)->select();
        $data = $this->getTree($spot_id,$table,$pid);
        $HierarchyList = array_merge($sql,$data);
        return $HierarchyList;
    }

    //递归
    public function getTree($id,$table,$pid){
        static $arr=array();
        $data = Db::table($table)->where($pid,$id)->select();
        foreach($data as $key=>$value){
            $arr[] = $value;            //把内容存进去
            $this->getTree($value['id'],$table,$pid);    //回调进行无线递归
        }
        return $arr;
    }

    //验证提交
    public function check($data){
        foreach ($data as $k => $v){
            if ($v == ''){//验证非空
                $result['Errno'] = 10000;
                $result['Errmsg'] = '必填项不能为空';
                return $result;
            }
        }
    }



    //验证治疗卡号
    public function treat_num($treat_num,$id){
//        return $id;
        $len = strlen($treat_num);
//        return $len;
        if ($len == 13){
            if(is_numeric($treat_num)){
                if ($id == ''){
                    $where['treat_num'] = $treat_num;
                }else{
                    $where['id'] = array('neq',$id);
                    $where['treat_num'] = $treat_num;
                }
                $res = Db::table('patients_tab')->where($where)->find();
                if ($res){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '治疗卡号已存在';
                    return $result;
                }else{
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '治疗卡号不存在';
                    return $result;
                }
            }else{
                $result['Errno'] = 0;
                $result['Errmsg'] = '治疗卡号错误';
                return $result;
            }
        }else{
            $result['Errno'] = 10000;
            $result['Errmsg'] = '治疗卡号错误';
            return $result;
        }
    }

    //验证身份证号
    public function id_card($id_card,$id){
        $len = strlen($id_card);
        if ($len == 18){
            if (preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $id_card)){
                if ($id == ''){
                    $where['card_id'] = $id_card;
                }else{
                    $where['id'] = array('neq',$id);
                    $where['card_id'] = $id_card;
                }
                $res = Db::table('patients_tab')->where($where)->find();
                if ($res){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '该身份证已存在';
                    return $result;
                }else{
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '该身份证不存在';
                    return $result;
                }
            }else{
                $result['Errno'] = 10000;
                $result['Errmsg'] = '身份证号错误';
                return $result;
            }
        }else{
            $result['Errno'] = 10000;
            $result['Errmsg'] = '身份证号错误';
            return $result;
        }
    }

    //验证手机号
    public function phone($phone,$id,$table){
        $len = strlen($phone);
        if ($len == 11){
            if (preg_match("/^1[34578]\d{9}$/", $phone)){
                if ($id == ''){
                    $where['phone'] = $phone;
                }else{
                    $where['id'] = array('neq',$id);
                    $where['phone'] = $phone;
                }
                $res = Db::table($table)->where($where)->find();
                if ($res){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '该手机号已存在';
                    return $result;
                }else{
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '该手机号不存在';
                    return $result;
                }
            }else{
                $result['Errno'] = 10000;
                $result['Errmsg'] = '手机号格式错误';
                return $result;
            }
        }else{
            $result['Errno'] = 10000;
            $result['Errmsg'] = '手机号错误';
            return $result;
        }
    }

    //token验证
    public function token($token){
        if($token == ''){//token为空
            $result['Errno'] =  10000;
            $result['Errmsg'] =  '请先登陆';
            return $result;
        }else{
            $res = Db::table('admin')->field('token,token_time')->where('token',$token)->find();
            if($res){
                if ($res['token_time'] < time()){//token过期
                    $result['Errno'] =  10000;
                    $result['Errmsg'] =  '请重新登陆';
                    return $result;
                }else{//token未过期
//                    $new['token_time'] = time() + 7200;
//                    $sql = $sql = Db::table('admin')->where('token',$token)->update($new);//更新token有效期
//                    var_dump($sql);
//                    if ($sql){
                        $result['Errno'] =  0;
                        $result['Errmsg'] =  '请求成功';
                        return $result;
//                    }
//                    else{
//                        $result['Errno'] =  10000;
//                        $result['Errmsg'] =  '不要点太快哟';
//                        return $result;
//                    }
                }
            }else{//token不存在,异地登录
                $result['Errno'] =  10000;
                $result['Errmsg'] =  '账号在其他地方登录';
                return $result;
            }
        }
    }
}