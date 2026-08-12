<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Basem extends Model{
    
    public function get_id($table){
        $id=get_id();
        while(Db::table($table)->where('_id',$id)->value('_id')){
            $id=get_id();
        }
        return $id;
    }


    //新增代理
    public function insert_agent($arr){
        $res=[];
        $res['_id']=$this->get_id('Agent');
        $res['_t']='Agent';
        $res['C']=[];
        $res['SuperiorID']=$arr['user']=='admin'?0:$arr['user']+0;
        $res['Agentaccount']=$arr['Agentaccount'];
        $res['PassWord']=md5($arr['password']);
        $res['sign']='';
        $res['Money']=0;
        $res['PromoCode']=$arr['PromoCode'];
        $res['AgentRegGiveOnOff']=0;
        $res['Bank']='';
        $res['OpenName']='';
        $res['Name']='';
        $res['Divide']=$arr['Divide']+0;
        $res['Wechat']='';
        $res['QQ']='';
        $res['TEL']='';
        $res['nicheng']='';
        $res['time']=date('Y-m-d H:i:s',time());

        // 入库
        if(Db::table('Agent')->insert($res)){
            $this->insert_caozuo('新增代理ID：'.$res['_id']);
            return true;
        }
    }


    //短信送-金额保存
    public function insert_coin_dx($coin){
        $res=[];
        $res['_id']=$this->get_id('SmsCoinConfig');
        $res['_t']='SmsCoinConfig';
        $res['C']=[];
        $res['Coin']=$coin;

        //入库
        if(Db::table('SmsCoinConfig')->insert($res)){
            $this->insert_caozuo('新增短信送项目：'.$coin.'金币');
            return true;
        }
    }


    //短信送-申请
    public function insert_coin_sq($arr){
        $res=[];
        $res['_id']=$this->get_id('SmsGiveCoin');
        $res['_t']='SmsGiveCoin';
        $res['C']=[];
        $res['SmsCoinConfigID']=$arr['id']+0;
        $res['AgentID']=$arr['user']+0;
        $res['Number']=$arr['number']+0;
        $res['State']=0;
        $res['Time']=date('Y-m-d H:i:s',time());
        
        if(Db::table('SmsGiveCoin')->insert($res)){
            $coin=Db::table('SmsCoinConfig')->where('_id',$arr['id']+0)->value('Coin');
            $this->insert_caozuo('申请短信送：'.$coin.'金币，'.$arr['number'].'条');
            return true;
        }
    }


    //公告
    public function insert_gonggao($arr){
        $res=[];
        $res['_id']=$this->get_id($arr['table']);
        $res['title']=$arr['title'];
        $res['GongGao']=$arr['GongGao'];
        $res['time']=date('Y-m-d H:i:s',time());
        
        if(Db::table($arr['table'])->insert($res)){
            $this->insert_caozuo('新增代理公告：'.$arr['title']);
            return true;
        }
    }



    //操作
    public function insert_caozuo($text){
        $user=session('user');

        $res=[];
        $res['_id']=$this->get_id('OperationLog');
        $res['User']=$user=='admin'?0:$user;
        $res['Content']=$text;
        $res['Time']=date('Y-m-d H:i:s',time());

        if(Db::table('OperationLog')->insert($res)){
            return true;
        }
    }


}