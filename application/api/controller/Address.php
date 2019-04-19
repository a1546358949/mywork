<?php


namespace app\api\controller;


use app\api\model\Checking;
use think\Controller;
use think\Db;

class Address extends Controller
{
    //调用接口
    public function address(){
        $result = [];
        if (request()->isPost()){
            $token = input('Token');

            $yanzheng = new Checking();
            $result = $yanzheng->token($token);

            if ($result['Errno'] == 10000){
                return json($result);
            }else{
                $data = $yanzheng->stops($token);//获取可查看工作点

                foreach ($data as $k => $v) {
                    $spot_id[] = $v['id'];
                }
                $spot_id = implode(",", $spot_id);

                $where['id'] = ['in', $spot_id];
                $result = $this->getData($where);
                return json($result);
            }
        }
    }

    //获取数据
    public function getData($where){
        $sql = Db::table('point')->field('up_spot_id,spot_name,id')->where($where)->select();
        $pid = 0;
        $res = $this->getMenu($sql,$pid);
        return $res;
    }

    //形成树状结构
    public  function  getMenu($data,$pid,$deep=0)
    {
        $arr = [];
        $tree = [];
        foreach ($data as $row) {
            if($row['up_spot_id']==$pid){
                $arr['value'] = $row['id'];
                $arr['label'] = $row['spot_name'];
                $arr['children']=$this->getMenu($data,$row['id'],$deep+1);
                $tree[]=$arr;
            }
        }
        return $tree;
    }
}