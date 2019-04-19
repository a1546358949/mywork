<?php


namespace app\api\controller;


use app\api\model\Checking;
use think\Controller;
use think\Db;

class Treatment extends Controller
{
    //维持治疗-输入治疗卡号
    public function number(){
        if (request()->isPost()){
            $treat_num = input('Parameter');
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);

            if ($result['Errno'] == 10000){
                return json($result);
            }else{
                $data = $yanzheng->stops($token);
                foreach ($data as $k => $v){
                    $spot_id[] = $v['id'];
                }

                $where = "card_id = $treat_num or treat_num = $treat_num";
                $res = Db::table('patients_tab')->where($where)->field('id,name,gender,birthday,nation,img,status,spot_id')->find();

                if ($res){
                    if (in_array($res['spot_id'],$spot_id)){
                        $id = $res['id'];
                        $sql = Db::table('record_tab')->where('patient_id',$id)->field('create_time,dose,spot_name')->limit(7)->order('create_time desc')->select();
                        $result['Drug']['Name'] = $res['name'];
                        $result['Drug']['Gender'] = $res['gender'];
                        $result['Drug']['Birthday'] = $res['birthday'];
                        $result['Drug']['Nation'] = $res['nation'];
                        $result['Drug']['Img'] = $res['img'];
                        $result['Drug']['Parameter'] = $treat_num;
                        $result['Start'] = $res['status'];
                        if ($sql){
                            foreach ($sql as $k => $v){
                                $result['MedicationLest'][$k]['Time'] = $v['create_time'];
                                $result['MedicationLest'][$k]['Metering'] = $v['dose'];
                                $result['MedicationLest'][$k]['Place'] = $v['spot_name'];
                            }
                        }else{
                            $result['MedicationLest'] = [];
                        }
                        $result['MedicationLest'] = array_reverse($result['MedicationLest']);
                        $result['Errno'] =  0;
                        $result['Errmsg'] =  '操作成功';
                        return json($result);
                    }else{

                        $id = $res['id'];
                        $sql = Db::table('record_tab')->where('patient_id',$id)->field('create_time,dose,spot_name')->limit(3)->order('create_time desc')->select();
                        if ($sql){
                            foreach ($sql as $k => $v){
                                $result['MedicationLest'][$k]['Time'] = $v['create_time'];
                                $result['MedicationLest'][$k]['Metering'] = $v['dose'];
                                $result['MedicationLest'][$k]['Place'] = $v['spot_name'];
                            }
                            $result['MedicationLest'] = array_reverse($result['MedicationLest']);
                        }else{
                            $result['MedicationLest'] = [];
                        }
                        $result['Errno'] =  0;
                        $result['Errmsg'] =  '操作成功';
                        return json($result);
                    }
                }else{
                    $result['Errno'] =  10000;
                    $result['Errmsg'] =  '身份证号或者治疗卡号错误';
                    return json($result);
                }
            }
        }
    }

    //维持治疗-密码验证
    public function password(){
        if (request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 1){
                return json($result);
            }else {
                $sql = Db::table('admin')->where('token',$token)->field('password')->limit(1)->find();
                if (input('Password') == $sql['password']){
                    $result['str'] = base64_encode($token.'2000');
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '操作成功';
                    return json($result);
                }else{
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '密码错误';
                    return json($result);
                }
            }
        }
    }

    //维持治疗-注射药物记录
    public function record(){
        if (request()->isPost()){
            $token = input('Token');
            $yanzheng = new Checking();
            $result = $yanzheng->token($token);
            if ($result['Errno'] == 1){
                return json($result);
            }else {
                $str = input('str');
                $str1 = base64_encode($token.'1000');
                $str2 = base64_encode($token.'2000');
                if ($str == ''){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '非法操作';
                    return json($result);
                }else{
                    if ($str == $str1){
                        $data['type'] = 1;
                    }elseif ($str == $str2){
                        $data['type'] = 2;
                    }else{
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '非法操作';
                        return json($result);
                    }
                }
                $treat_num = input('Parameter');//治疗卡号
                $where = "card_id = $treat_num or treat_num = $treat_num";
                $res = Db::table('patients_tab')->where($where)->field('id,name,spot_id')->find();
                $data['patient_id'] = $res['id'];//病人ID
                $data['patient_name'] = $res['name'];//病人姓名
                $data['create_time'] = time();//创建时间

                $times = Db::table('record_tab')->field('create_time')->where('patient_id',$data['patient_id'])->limit(1)->order('create_time')->find();
                if ($times){
                    $last_time = $times['create_time'];//最后服药时间
                }else{
                    $last_time = 0;
                }
                //获得当日凌晨的时间戳
                $today = strtotime(date("Y-m-d"),time());
                if ($last_time > $today){
                    $result['Errno'] = 10000;
                    $result['Errmsg'] = '该人员今天已经开过药了';
                    return json($result);
                }else{
                    $data['dose'] = input('Dose');//剂量
                    $data['spot_id'] = input('SpotId');//服药地点id

                    $sql = Db::table('point')->where('id',$data['spot_name'])->find();
                    $data['spot_id'] = $sql['spot_id'];//服药地点

                    $doc_ids = Db::table('admin')->where('token',$token)->field('id')->find();
                    $data['doc_id'] = $doc_ids['id'];//给药医生id
                    $save = Db::table('record_tab')->insert($data);
                    if ($save) {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '操作成功';
                        return json($result);
                    } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '操作失败';
                        return json($result);
                    }
                }
            }
        }
    }
}