<?php


namespace app\api\controller;

//入组模块
use think\Controller;
use think\Db;

class Group extends Controller
{
    //入组-判断治疗卡
    public function judge(){
        if (request()->isPost()){
            $token = input('Token');
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $treat_num = input('Parameter');
                $yanzheng = new Power();
                $result = $yanzheng->treat_num($treat_num);
                if ($result['Errno'] == 10000){
                    return json($result);
                }else {
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
                    } else {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '未入组';
                    }
                }
            }
        }
        return json($result);
    }

    //入组-新增(修改)
    public function add_update(){
        if (request()->isPost()){
            $token = input('Token');
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000 ){
                return json($result);
            }else {
                $id = input('id');
                $data['name'] = input('Name');
                $data['gender'] = input('Gender');
                $data['treat_num'] = input('TreatNum');
                $data['birthday'] = input('Birthday');
                $data['nation'] = input('Nation');
                $data['card_id'] = input('CardID');
                $data['occupation'] = input('Occupation');
                $data['marriage'] = input('Marriage');
                $data['education'] = input('Education');
                $data['phone'] = input('Phone');
                $data['address'] = input('Address');
                $data['relatives_name'] = input('RelativesName');
                $data['relatives_phone'] = input('RelativesPhone');
                $data['police_station'] = input('PoliceStation');
                $data['police_station_phone'] = input('PoliceStationPhone');
                $data['first_suction_time'] = input('FirstSuctionTime');
                $data['main_drug'] = input('MainDrugs');
                $data['quit_second'] = input('QuitSecond');
                $data['treatment_point'] = input('TreatmentPoint');
                $sql = Db::table('point')->where('spot_name',$data['treatment_point'])->field('id')->find();
                $data['spot_id'] = $sql['id'];
                $data['img'] = input('Img');
                $data['update_time'] = time();
                $data['status'] = 3;
                if ($id == ''){
                    $data['create_time'] = time();
                    $res = Db::table('patients_tab')->insert($data);
                    if ($res) {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '入组成功';
                    } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '入组失败';
                    }
                }else{
                    $res = Db::table('patients_tab')->where('id', $id)->update($data);
                    if ($res) {
                        $result['Errno'] = 0;
                        $result['Errmsg'] = '修改成功';
                    } else {
                        $result['Errno'] = 10000;
                        $result['Errmsg'] = '修改失败';
                    }
                }
            }
        }
        return json($result);
    }
}