<?php


namespace app\api\controller;


use think\Controller;
use think\Db;

class Count extends Controller
{
    //统计-服药记录查询
    public function record(){
        if (request()->isPost()){
            $token = input('Token');
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            } else {
                $onTime = input('onTime');//开始时间
                $outTime = input('outTime');//结束时间
                $PageNumber = input('PageNumber');//页码
                $Pagelist = input('Pagelist');//每页条数
                $token = input('Token');//登录识别码
                $start = $PageNumber * $Pagelist;//开始条数
                $getData = new Power();
                $data = $getData->stops($token);//获取可查看工作点
                foreach ($data as $k => $v) {
                    $spot_id[] = $v['id'];
                }
                $spot_id = implode(",", $spot_id);
                $where['r.spot_id'] = ['in', $spot_id];
                $where['r.create_time'] = ['between',"$onTime,$outTime"];
                $res = Db::table('patients_tab p')
                    ->join('record_tab r', 'p.id = r.patient_id')
                    ->where($where)
                    ->field('p.treat_num,p.name,r.dose,r.spot_name,r.create_time')
                    ->limit($start, $Pagelist)
                    ->select();
                if ($res){
                    foreach ($res as $k => $v) {
                        $result['data']['RecordList'][$k]['TreatmentCode'] = $v['treat_num'];
                        $result['data']['RecordList'][$k]['DrugName'] = $v['name'];
                        $result['data']['RecordList'][$k]['Metering'] = $v['dose'];
                        $result['data']['RecordList'][$k]['Place'] = $v['spot_name'];
                        $result['data']['RecordList'][$k]['Time'] = date('Y-m-d H:i:s', $v['create_time']);
                    }
                }else{
                    $result['data']['RecordList'] = [];
                }

                $where1['spot_id'] = ['in', $spot_id];
                $where1['create_time'] = ['between',"$onTime,$outTime"];
                $num = Db::table('record_tab')->where($where1)->group('patient_id')->select();
                $result['data']['Strength'] = count($num);//服药人数
                $dose = Db::table('record_tab')->where($where1)->sum('dose');
                $result['data']['TotalDose'] = $dose;//服药总剂量
                $sql = Db::table('record_tab')->where($where1)->select();
                $result['data']['TotalNumber'] = count($sql);//总条数
                $result['Errno'] = 0;
                $result['Errmsg'] = '查询成功';
            }
            return json($result);
        }
    }

    //统计-服药情况查询
    public function situation(){
        if (request()->isPost()){
            $token = input('Token');
            $Login = new Login();
            $result = $Login->token($token);
            if ($result['Errno'] == 10000){
                return json($result);
            }else {
                $status = input('ClothesStart');//状态
                if ($status == 0) {

                }else{
                    $where['p.status'] = $status;
                }
                $PageNumber = input('PageNumber');//页码
                $Pagelist = input('Pagelist');//每页条数
                $token = input('Token');//登录识别码
                $start = $PageNumber * $Pagelist;//开始条数
                $getData = new Power();
                $data = $getData->stops($token);//获取可查看工作点
                foreach ($data as $k => $v) {
                    $spot_id[] = $v['id'];
                }
                $spot_id = implode(",", $spot_id);
                $where['r.spot_id'] = ['in', $spot_id];
                $res = Db::table('patients_tab p')
                    ->join('record_tab r', 'p.id = r.patient_id')
                    ->where($where)
                    ->field('p.treat_num,p.name,p.status,r.spot_name')
                    ->group('p.id')
                    ->limit($start, $Pagelist)
                    ->select();
                if ($res){
                    foreach ($res as $k => $v) {
                        $result['SituationList'][$k]['TreatmentCode'] = $v['treat_num'];//吸毒人员治疗卡号
                        $result['SituationList'][$k]['DrugName'] = $v['name'];//吸毒人员姓名
                        $result['SituationList'][$k]['State'] = $v['status'];//服药状态
                        $result['SituationList'][$k]['Place'] = $v['spot_name'];//服药地点
                    }
                }else{
                    $result['SituationList'] = [];
                }
                $num = Db::table('patients_tab p')
                    ->join('record_tab r', 'p.id = r.patient_id')
                    ->where($where)
                    ->group('p.id')
                    ->select();
                $result['Strength'] = count($num);//服药人数

                $sql = Db::table('patients_tab p')
                    ->join('record_tab r', 'p.id = r.patient_id')
                    ->group('p.id')
                    ->where($where)
                    ->count('p.id');
                $result['TotalNumber'] = $sql;//总条数
            }
            return json($result);
        }
    }

    //统计-病人详情
    public function details(){
        $token = input('Token');
        $Login = new Login();
        $result = $Login->token($token);
        if ($result['Errno'] == 10000){
            return json($result);
        }else{
            echo 'xxx';
        }
    }
}