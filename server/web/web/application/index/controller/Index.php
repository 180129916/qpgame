<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Db;


class Index extends Base{


	//报表统计
    public function index(){
        $user=$this->user;

        $time=time();
        $day=date('Y-m-d',$time);
        $sta=date('Y-m-01',strtotime(date("Y-m-d")));
        $end=date('Y-m-d', strtotime("$sta +1 month -1 day"));

        if($user=='admin'){
            $count_dl=Db::table('Agent')->count();      //代理数量
            $count_wj=Db::table('gameaccount.newuseraccounts')->count();   //玩家数量

            $ri_1=Db::table('AgentEarnings')->where('Time',$day)->sum('AllCoin');
            $ri_2=Db::table('AgentEarnings')->where('Time',$day)->sum('GetCoin');
            $yue_1=Db::table('AgentEarnings')->where('Time','between',[$sta,$end])->sum('AllCoin');
            $yue_2=Db::table('AgentEarnings')->where('Time','between',[$sta,$end])->sum('GetCoin');

            $sq=Db::table('SmsGiveCoin')->where('Time','between',[$sta,$end])->select();
        }else{
            $agent=$this->agent;

            $count_dl=Db::table('Agent')->where('SuperiorID',$agent['_id'])->count();
            $count_wj=Db::table('gameaccount.newuseraccounts')->count();

            $ri_1=Db::table('AgentEarnings')->where('AgentId',$agent['_id'])->where('Time',$day)->sum('AllCoin');
            $ri_2=Db::table('AgentEarnings')->where('AgentId',$agent['_id'])->where('Time',$day)->sum('GetCoin');
            $yue_1=Db::table('AgentEarnings')->where('AgentId',$agent['_id'])->where('Time','between',[$sta,$end])->sum('AllCoin');
            $yue_2=Db::table('AgentEarnings')->where('AgentId',$agent['_id'])->where('Time','between',[$sta,$end])->sum('GetCoin');

            $sq=Db::table('SmsGiveCoin')->where('AgentID',$agent['_id'])->where('Time','between',[$sta,$end])->select();

            $dl=Db::table('Agent')->where('_id',$user)->find();
            $this->assign('dl',$dl);
        }

        $song=0;
        $coin=[];
        $coin_db=Db::table('SmsCoinConfig')->select();
        foreach($coin_db as $v){
            $coin[$v['_id']]=$v['Coin'];
        }

        foreach($sq as $v){
            $song+=$coin[$v['SmsCoinConfigID']]*$v['Number'];
        }
       
        $this->assign('ri_1',$ri_1);
        $this->assign('ri_2',$ri_2);
        $this->assign('yue_1',$yue_1);
        $this->assign('yue_2',$yue_2);

        $this->assign('song',$song);
        
        $this->assign('count_dl',$count_dl);
        $this->assign('count_wj',$count_wj);
        
    	return $this->fetch();
    }




    //输赢排行
    public function shuying(){
        $user=$this->user;
        $type=input('?param.type')?input('param.type'):0;

        $px1='_id';
        $px2='desc';
        switch($type){
            case 1:
                $px1='WinCoin';
                $px2='desc';
            break;
            case 2:
                $px1='WinCoin';
                $px2='asc';
            break;
            case 3:
                $px1='LoseCoin';
                $px2='desc';
            break;
            case 4:
                $px1='LoseCoin';
                $px2='asc';
            break;
        }

        if($user=='admin'){
            $list=Db::table('UserInfo')->order($px1,$px2)->paginate(15,false,['query'=>['type'=>$type]]);
        }else{
            $agent=$this->agent;
            $list=Db::table('UserInfo')->where('Referrer',$agent['PromoCode'])->order($px1,$px2)->paginate(15,false,['query'=>['type'=>$type]]);
        }

        $this->assign('type',$type);
        $this->assign('list',$list);
        return $this->fetch();
    }




    //在线列表（调 GM 接口 GetuserListOnline）
    public function online(){
        $online=[];
        try{
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://127.0.0.1/gmManage');
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,'act=GetuserListOnline');
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_TIMEOUT,6);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,6);
            $resp=curl_exec($ch);
            curl_close($ch);
            $data=json_decode($resp,true);
            if(is_array($data)){
                foreach($data as $o){
                    $online[]=[
                        'userId'=>isset($o['_userId'])?$o['_userId']:'',
                        'account'=>isset($o['_account'])?$o['_account']:'',
                        'score'=>isset($o['_score'])?$o['_score']:'',
                    ];
                }
            }
        }catch(\Exception $e){}
        $this->assign('online',$online);
        return $this->fetch();
    }




    //代理列表
    public function daili(){
        $user=$this->user;
        $ss=input('?param.ss')?input('param.ss'):'';

        if($user=='admin'){
            if($ss!=''){
                $list=Db::table('Agent')
                ->whereOr('_id',$ss+0)
                ->whereOr('SuperiorID',$ss+0)
                ->whereOr('Divide',$ss+0)
                ->whereOr('Agentaccount',$ss)
                ->whereOr('PromoCode',$ss)
                ->whereOr('Bank',$ss)
                ->whereOr('OpenName',$ss)
                ->whereOr('Name',$ss)
                ->whereOr('Wechat',$ss)
                ->whereOr('QQ',$ss)
                ->whereOr('TEL',$ss)
                ->order('Divide','desc')
                ->paginate(15,false,['query'=>['ss'=>$ss]]);
            }else{
                $list=Db::table('Agent')->order('Divide','desc')->paginate(15,false);
            }
        }else{
            if($ss!=''){
                $list=Db::table('Agent')
                ->whereOr('_id',$ss+0)
                ->whereOr('SuperiorID',$ss+0)
                ->whereOr('Divide',$ss+0)
                ->whereOr('Agentaccount',$ss)
                ->whereOr('PromoCode',$ss)
                ->whereOr('Bank',$ss)
                ->whereOr('OpenName',$ss)
                ->whereOr('Name',$ss)
                ->whereOr('Wechat',$ss)
                ->whereOr('QQ',$ss)
                ->whereOr('TEL',$ss)
                ->where('SuperiorID',$user+0)
                ->order('Divide','desc')
                ->paginate(15,false,['query'=>['ss'=>$ss]]);
            }else{
                $list=Db::table('Agent')->where('SuperiorID',$user+0)->order('Divide','desc')->paginate(15,false);
            }
        }

        $dl=[];
        $wj=[];
        foreach($list as $v){
            $dl[$v['_id']]=Db::table('Agent')->where('SuperiorID',$v['_id'])->count();
            $wj[$v['_id']]=Db::table('UserInfo')->where('Referrer',$v['PromoCode'])->count();
        }
        
        $this->assign('list',$list);
        $this->assign('dl',$dl);
        $this->assign('wj',$wj);
        $this->assign('ss',$ss);
        
    	return $this->fetch();
    }




    //玩家列表（读真实玩家库 gameaccount.newuseraccounts，原生SQL跨库查询）
    public function wanjia(){
        $ss=input('?param.ss')?input('param.ss'):'';
        $p=input('?param.p')?max(1,intval(input('param.p'))):1;
        $size=15;
        $where='';
        if($ss!=''){
            $ss=addslashes($ss);
            if(is_numeric($ss)){
                $where=" WHERE Id=$ss OR Account LIKE '%$ss%' OR nickname LIKE '%$ss%' OR phoneNo LIKE '%$ss%'";
            }else{
                $where=" WHERE Account LIKE '%$ss%' OR nickname LIKE '%$ss%' OR phoneNo LIKE '%$ss%'";
            }
        }
        $cnt=Db::query("SELECT COUNT(*) AS c FROM gameaccount.newuseraccounts $where");
        $total=isset($cnt[0]['c'])?$cnt[0]['c']:0;
        $pages=$total>0?ceil($total/$size):1;
        $offset=($p-1)*$size;
        $list=Db::query("SELECT * FROM gameaccount.newuseraccounts $where ORDER BY Id DESC LIMIT $offset,$size");
        $this->assign('ss',$ss);
        $this->assign('list',$list);
        $this->assign('p',$p);
        $this->assign('pages',$pages);
        return $this->fetch();
    }

    public function coin(){
        $list=Db::table('AddCoinLog')->order('time','desc')->paginate(15,false);

        $this->assign('list',$list);
        
        return $this->fetch();
    }

    //给玩家发币（走游戏服官方 GameBalance 通道：在线实时到账 / 在游戏中待退出生效 / 离线下次登录生效）
    public function coin_in(){
        $uid=input('post.userid');
        $coin=input('post.coin/d',0);
        if($coin<=0){
            $this->error('金币量必须大于0');
        }
        // 支持按 玩家Id 或 账号(Account) 查找真实玩家
        $row=Db::table('gameaccount.newuseraccounts')->where('Id',intval($uid))->find();
        if(!$row){
            $row=Db::table('gameaccount.newuseraccounts')->where('Account',$uid)->find();
        }
        if(!$row){
            $this->error('玩家不存在（请填写玩家列表中的 Id 或 账号）');
        }
        $realId=$row['Id'];
        // 确保 userinfo_imp 行存在（游戏服真实分数表；缺了会导致离线/落库不生效）
        Db::execute("INSERT IGNORE INTO gameaccount.userinfo_imp(userId,score,diamond,giftTicket) VALUES({$realId},0,0,0)");
        // 调游戏服 GM 接口 AddCoin（内部走 GameBalance，与真实充值同一通道，落 userinfo_imp）
        $resp=$this->gm_post('AddCoin',['userId'=>$realId,'coin'=>$coin]);
        $status=isset($resp['status'])?$resp['status']:'offline';
        $label=[
            'online'=>'在线玩家已实时到账',
            'ingame'=>'玩家在游戏中，退出游戏后生效',
            'offline'=>'玩家离线，下次登录游戏生效',
        ];
        $msg=isset($label[$status])?$label[$status]:'已提交';
        Db::table('AddCoinLog')->insert([
            'UserID'=>$realId,
            'Coin'=>$coin,
            'time'=>date('Y-m-d H:i:s'),
        ]);
        $this->success('赠送成功：已为玩家 '.$realId.'('.$row['Account'].') 增加 '.$coin.' 金币。'.$msg,'index/coin');
    }

    // GM 接口 POST 辅助（与 online() 同方式）
    private function gm_post($act,$params){
        $params['act']=$act;
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,'http://127.0.0.1/gmManage');
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,6);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,6);
        $resp=curl_exec($ch);
        curl_close($ch);
        $data=json_decode($resp,true);
        return is_array($data)?$data:[];
    }




    //账号转移
    public function zhuanyi(){
        return $this->fetch();
    }

    //提现
    public function tixian(){
        $list=Db::table('TXlog')->order('State','asc')->paginate(15,false);

        $this->assign('list',$list);
        return $this->fetch();
    }


    //注册送
    public function coin_zc(){
        $user=$this->user;

        if($user=='admin'){
            $list=Db::table('RegGiveCoinLog')->order('time','desc')->paginate(15,false);
        }else{
            $agent=Db::table('Agent')->where('_id',$user+0)->find();
            $list=Db::table('RegGiveCoinLog')->where('AgentID',$user+0)->order('time','desc')->paginate(15,false);

            $this->assign('agent',$agent);
        }


        $this->assign('list',$list);
        return $this->fetch();
    }
    

    //短信送
    public function coin_dx_sq(){
        $user=$this->user;

        $coin=Db::table('SmsCoinConfig')->order('Coin','asc')->select();

        if($user=='admin'){
            //申请记录
            $sq=Db::table('SmsGiveCoin')->order('State','asc')->order('Time','desc')->paginate(15,false);
        }else{
            //申请记录
            $sq=Db::table('SmsGiveCoin')->where('AgentID',$user+0)->order('Time','desc')->paginate(15,false);
        }

        $this->assign('coin',$coin);
        $this->assign('sq',$sq);
        return $this->fetch();
    }

    //短信送-发送
    public function coin_dx_fs(){
        $user=$this->user;

        if($user=='admin'){
            //短信记录
            $list=Db::table('SmsGiveCoinLog')->order('time','desc')->paginate(15,false);

        }else{
            $coin=Db::table('SmsCoinConfig')->order('Coin','asc')->select();
            //短信剩余
            $sq_all=Db::table('SmsGiveCoin')->where('AgentID',$user+0)->where('State',2)->select();
            $sq_sy=[];
            foreach($sq_all as $k=>$v){
                $sy=Db::table('SmsGiveCoinLog')->where('AgentID',$user+0)->where('SmsGiveCoinID',$v['_id']+0)->count();
                if($sy<$v['Number']){
                    $sq_sy[$v['_id']]['id']=$v['_id'];
                    $sq_sy[$v['_id']]['sq']=$v['Number'];
                    $sq_sy[$v['_id']]['sy']=$v['Number']-$sy;
                    foreach($coin as $u){
                        if($u['_id']==$v['SmsCoinConfigID']){
                            $sq_sy[$v['_id']]['coin']=$u['Coin'];
                        }
                    }
                }
            }

            //短信记录
            $list=Db::table('SmsGiveCoinLog')->where('AgentID',$user+0)->order('time','desc')->paginate(15,false);

            $this->assign('sq_sy',$sq_sy);
        }

        $this->assign('list',$list);
        return $this->fetch();
    }
}
