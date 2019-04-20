<?php


namespace app\api\controller;

//入组模块
use app\api\model\Checking;
use think\Controller;
use think\Db;

class Group extends Controller
{
    //入组-判断身份证（外地人员）
    public function nonlocal(){
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();
            $result = $check->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = '';
                $id_card = input('Parameter');
                $result = $check->id_card($id_card,$id);
                if ($result['Errmsg'] == '该身份证已存在'){
                    $result['Errno'] = 0;
                    return json($result);
                }else {
                    return json($result);
                }
            }
        }
    }

    //入组-外地人员新增
    public function nonlocal_add(){
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();
            $result = $check->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $data['birthday'] = input('Parameter');
                $data['name'] = input('Name');
                $data['birthday'] = input('Birthday');
                $data['type'] = 1;
                $data['create_time'] = time();
                $data['update_time'] = time();
                $data['treatment_point'] = input('TreatmentPoint');
                $sql = Db::table('point')->where('spot_name',$data['treatment_point'])->field('id')->find();
                $data['spot_id'] = $sql['id'];
                $res = Db::table('patients_tab')->insert($data);
                if ($res) {
                    $result['Errno'] = 0;
                    $result['Errmsg'] = '添加成功';
                    return json($result);
                }
            }
        }
    }

    //入组-判断治疗卡（本地人员）
    public function judge(){
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();
            $result = $check->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $id = '';
                $treat_num = input('Parameter');
                $result = $check->treat_num($treat_num,$id);
//                print_r($result);exit;
                if ($result['Errmsg'] == '治疗卡号已存在'){
                    $result['Errno'] = 0;
                }
                if($result['Errno'] == 10000){
                    return json($result);
                }else{
                    $res = Db::table('patients_tab')->where('treat_num', $treat_num)->find();
                    if ($res) {
                        foreach ($res as $k => $v){
                            $result['Drug']['id'] = $res['id'];
                            $result['Drug']['Name'] = $res['name'];
                            $result['Drug']['Gender'] = $res['gender'];
                            $result['Drug']['TreatNum'] = $res['treat_num'];
                            $result['Drug']['Birthday'] = $res['birthday'];
                            $result['Drug']['Nation'] = $res['nation'];
                            $result['Drug']['CardID'] = $res['card_id'];
                            $result['Drug']['Occupation'] = $res['occupation'];
                            $result['Drug']['Marriage'] = $res['marriage'];
                            $result['Drug']['Education'] = $res['education'];
                            $result['Drug']['Phone'] = $res['phone'];
                            $result['Drug']['Address'] = $res['address'];
                            $result['Drug']['RelativesName'] = $res['relatives_name'];
                            $result['Drug']['RelativesPhone'] = $res['relatives_phone'];
                            $result['Drug']['PoliceStation'] = $res['police_station'];
                            $result['Drug']['PoliceStationPhone'] = $res['police_station_phone'];
                            $result['Drug']['FirstSuctionTime'] = $res['first_suction_time'];
                            $result['Drug']['MainDrugs'] = $res['main_drug'];
                            $result['Drug']['QuitSecond'] = $res['quit_second'];
                            $result['Drug']['TreatmentPoint'] = $res['treatment_point'];
                            $result['Drug']['Img'] = $res['img'];
                        }
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '已入组';
                        return json($result);
                    } else {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '未入组';
                        return json($result);
                    }
                }
            }
        }
    }

    //入组-新增(修改)
    public function add_update(){
        if (request()->isPost()){
            $token = input('Token');
            $check = new Checking();//实例化Checking模型
            $result = $check->token($token);//验证token
            if ($result['Errno'] == 10000 ){
                return json($result);
            }else {
                $id = input('id');
                $data['name'] = input('Name');//名字
                $data['gender'] = input('Gender');//性别
                $data['treat_num'] = input('TreatNum');//治疗卡号
                $data['birthday'] = input('Birthday');//生日
                $data['nation'] = input('Nation');//民族
                $data['card_id'] = input('CardID');//身份证号
                $result = $check->id_card($data['card_id'],$id);//验证身份证号
                if ($result['Errno'] == 10000){
                    return json($result);
                }
                $data['education'] = input('Education');//教育程度
                $data['phone'] = input('Phone');//手机号
                $result = $check->phone($data['phone'],$id,$table = 'patients_tab');//验证手机号
                if ($result['Errno'] == 10000){
                    return json($result);
                }
                $data['treatment_point'] = input('TreatmentPoint');//所属治疗点
                $sql = Db::table('point')->where('spot_name',$data['treatment_point'])->field('id')->find();//查询治疗点id
                $data['spot_id'] = $sql['id'];//所属治疗点id
                $data['address'] = input('Address');
                $result = $check->check($data);//验证非空
                if ($result['Errno'] == 10000){
                    return json($result);
                }
                $data['occupation'] = input('Occupation');//职业
                $data['marriage'] = input('Marriage');//是否结婚
                $data['relatives_name'] = input('RelativesName');//亲属名字
                $data['relatives_phone'] = input('RelativesPhone');//亲属手机号
                $data['police_station'] = input('PoliceStation');//所属派出所
                $data['police_station_phone'] = input('PoliceStationPhone');//派出所电话
                $data['first_suction_time'] = input('FirstSuctionTime');//首吸时间
                $data['main_drug'] = input('MainDrugs');//主要毒品
                $data['quit_second'] = input('QuitSecond');//解读次数

                $data['img'] = input('Img');//头像
                $data['update_time'] = time();
                if ($id == ''){
                    $data['create_time'] = time();
                    $res = Db::table('patients_tab')->insert($data);
                    if ($res) {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '添加成功';
                        return json($result);
                    }
                }else{
                    $res = Db::table('patients_tab')->where('id', $id)->update($data);
                    if ($res) {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '修改成功';
                        return json($result);
                    } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '修改失败';
                        return json($result);
                    }
                }
            }
        }
    }
}