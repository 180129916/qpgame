<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Db;

class Top extends Base{

    //公告
    public function gonggao(){
        $user=$this->user;
        if($user=='admin'){
            $type=input('?param.type')?input('param.type'):'yx';
            //游戏公告
            if($type=='yx'){
                $list=Db::table('GameNotice')->order('time','desc')->paginate(15,false,['query'=>['type'=>$type]]);
            //代理公告
            }elseif($type=='dl'){
                $list=Db::table('AgentNotice')->order('time','desc')->paginate(15,false,['query'=>['type'=>$type]]);
            }elseif($type=='pm'){
                $list=Db::table('HorseNotice')->order('time','desc')->paginate(15,false,['query'=>['type'=>$type]]);
            }
            $this->assign('type',$type);
        }else{
            $list=Db::table('AgentNotice')->order('time','desc')->paginate(15,false);
        }

        $this->assign('list',$list);
        return $this->fetch();
    }





    //后台账号
    public function houtai(){
        $user=$this->user;

        if($user=='admin'){
            $Agentaccount=$this->user;
        }else{
            $agent=$this->agent;
            $Agentaccount=$agent['Agentaccount'];
            $this->assign('agent',$agent);
        }

        $this->assign('Agentaccount',$Agentaccount);

        return $this->fetch();
    }

    //修改密码
    public function mima(){
        $user=$this->user;

        if($user=='admin'){
            $Agentaccount='admin';
        }else{
            $Agentaccount=Db::table('Agent')->where('_id',$user+0)->value('Agentaccount');
        }

        $this->assign('Agentaccount',$Agentaccount);
        return $this->fetch();
    }






    //操作记录
    public function caozuo(){
        $user=$this->user;

        if($user=='admin'){
            $list=Db::table('OperationLog')->where('User',0)->order('time','desc')->paginate(15,false);
        }else{
            $list=Db::table('OperationLog')->where('User',$user)->order('time','desc')->paginate(15,false);
        }
        
        $this->assign('list',$list);
        return $this->fetch();
    }

    //游戏设置
    public function config(){
        $dx=Db::table('SmsPushConfig')->where('_id',1)->find();
        $this->assign('dx',$dx);

        // 对接前端现有游戏：与大厍服 /opt/qpgame/server/config/ServerInfo.js 的 GameConfig 保持一致
        $games=[
            ['GameId'=>1,'GameName'=>'捕鱼游戏'],
            ['GameId'=>6,'GameName'=>'连线游戏'],
            ['GameId'=>3,'GameName'=>'28游戏'],
            ['GameId'=>4,'GameName'=>'红包游戏'],
            ['GameId'=>5,'GameName'=>'八搭二游戏'],
            ['GameId'=>10,'GameName'=>'牛牛游戏'],
            ['GameId'=>10,'GameName'=>'几何派对'],
            ['GameId'=>4,'GameName'=>'抢庄牛牛'],
            ['GameId'=>5,'GameName'=>'经典牛牛'],
        ];
        $this->assign('games',$games);
        return $this->fetch();
    }
}
