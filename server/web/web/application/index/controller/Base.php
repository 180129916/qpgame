<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\Basem;
use think\Db;

class Base extends Controller{
	protected $user;
    protected $agent;
    public function _initialize(){
        // session('user','admin');
        // session('user','474840');

        // Admin
        // id=1
        // name='admin'
        // pwd='e10adc3949ba59abbe56e057f20f883e'(md5)

        if(!session('?user')){
            $this->error('请重新登录！','login/login');
        }else{
        	$user=$this->user=session('user');
            if($user!='admin'){
                $agent=$this->agent=Db::table('Agent')->where('_id',$user+0)->find();
            }

            $this->assign('user',$user);   
        }
    }

    public function config(){
        $post=input('post.');
        $arr=[];
        $arr['User']=$post['zh'];
        $arr['Pass']=$post['mm'];
        $arr['Txt']=$post['tx'];
        if(Db::table('SmsPushConfig')->where('_id',1)->update($arr)){
            $basem=new Basem();
            $basem->insert_caozuo('修改“短信接口”信息');
            return json(200);
        }


    }


    public function houtai(){
        $user=$this->user+0;
        $arr=[];
        $arr['nicheng']=input('post.nicheng');
        $arr['sign']=input('post.sign');
        $arr['Bank']=input('post.Bank');
        $arr['OpenName']=input('post.OpenName');
        $arr['Name']=input('post.Name');
        $arr['Wechat']=input('post.Wechat');
        $arr['QQ']=input('post.QQ');
        $arr['TEL']=input('post.TEL');

        if(Db::table('Agent')->where('_id',$user)->update($arr)){
            $this->agent=Db::table('Agent')->where('_id',$user)->find();
            
            //操作记录
            $basem=new Basem();
            $basem->insert_caozuo('修改“后台账号”信息');

            return json(200);
        }
    }

    public function mima(){
        $basem=new Basem();
        $user=$this->user;
        $old_pwd=input('post.old_pwd');
        $new_pwd=input('post.new_pwd');
        if($user=='admin'){
            if(!Db::table('Admin')->where('name',$user)->where('pwd',md5($old_pwd))->value('_id')){
                return json(1);
            }
            if(Db::table('Admin')->where('name',$user)->setField('pwd',md5($new_pwd))){
                $basem->insert_caozuo('修改密码');//操作记录
                return json(200);
            }
        }else{
            if(!Db::table('Agent')->where('_id',$user+0)->where('PassWord',md5($old_pwd))->value('_id')){
                return json(1);
            }
            if(Db::table('Agent')->where('_id',$user+0)->setField('PassWord',md5($new_pwd))){
                $basem->insert_caozuo('修改密码');//操作记录
                return json(200);
            }
        }
    }








    //报表统计-获取报表提交数据
    public function game_list(){
    	$sta=input('post.sta');
    	$end=input('post.end');
    	$type=input('post.type');

        $list=[];
        
        if($type=='admin'){
            //游戏
            $yx=Db::table('AgentEarnings')->field('AllCoin,GetCoin,Time')->where('Time','between',[$sta,$end])->select();
            //短信送
            $dx=Db::table('SmsGiveCoin')->where('Time','between',[$sta,$end])->select();
            //注册送
            $zc=Db::table('RegGiveCoinLog')->where('time','between',[$sta,$end])->select();
        }else{
            //游戏
            $yx=Db::table('AgentEarnings')->field('GetCoin,Time')->where('AgentId',$type+0)->where('Time','between',[$sta,$end])->select();
            //短信送
            $dx=Db::table('SmsGiveCoin')->where('AgentID',$type+0)->where('Time','between',[$sta,$end])->select();
            //注册送
            $zc=Db::table('RegGiveCoinLog')->where('AgentID',$type+0)->where('time','between',[$sta,$end])->select();
        }

        //游戏
        foreach($yx as $k=>$v){
            if($type=='admin'){
                $list['yx'][$v['Time']]['Coin']=$v['AllCoin']-$v['GetCoin'];
                $list['yx'][$v['Time']]['Time']=$v['Time'];
            }else{
                $list['yx'][$v['Time']]['Coin']=$v['GetCoin'];
                $list['yx'][$v['Time']]['Time']=$v['Time'];
            }
        }

        //短信送
        $coin=[];
        $coin_db=Db::table('SmsCoinConfig')->select();
        foreach($coin_db as $v){
            $coin[$v['_id']]=$v['Coin'];
        }
        
        foreach($dx as $k=>$v){
            $list['dx'][$k]['Coin']=$coin[$v['SmsCoinConfigID']]*$v['Number'];
            $list['dx'][$k]['Time']=$v['Time'];
        }

        //注册送
        $list['zc']=$zc;

    	return json($list);
    }












    //代理列表-修改分成
    public function divide(){
        $basem=new Basem();
        $user=$this->user;
        $post=input('post.');
        //判断代理占比<下级代理
        if(Db::table('Agent')->where('SuperiorID',$post['id']+0)->where('Divide>'.$post['val']+0)->find()){
            return json(1);//修改失败，该代理占比比例比此代理的下级代理要低
        }

        if($user=='admin'){
            if(Db::table('Agent')->where('_id',$post['id']+0)->setField('Divide',$post['val']+0)){
                $basem->insert_caozuo('修改代理ID'.$post['id'].'，分成比例为'.$post['val']);//操作记录
                return json(200);
            }
        }else{
            if(Db::table('Agent')->where('_id',$post['id']+0)->where('SuperiorID',$user+0)->setField('Divide',$post['val']+0)){
                $basem->insert_caozuo('修改代理ID'.$post['id'].'，分成比例为'.$post['val']);//操作记录
                return json(200);
            }
        }
    }

    //代理列表-新增代理
    public function agent(){
        $user=$this->user;
        $post=input('post.');
        //账号是否存在
        if(Db::table('Agent')->where('Agentaccount',$post['Agentaccount'])->find()){
            return json(1);//账号已存在
        }
        //推荐码是否存在
        if(Db::table('Agent')->where('PromoCode',$post['PromoCode'])->find()){
            return json(2);//推荐码已存在
        }
        //占成比例超出
        if($user!='admin'){
            $agent=$this->agent;
            if($agent['Divide']<$post['Divide']){
                return json(3);//新增代理的占成比例超出了你自身的占成比例
            }
        }
        if($post['Divide']>100){
            return json(3);//占比超出100
        }

        // 入库
        $arr=[];
        $arr['user']=$user;
        $arr['Agentaccount']=$post['Agentaccount'];
        $arr['password']=$post['password'];
        $arr['PromoCode']=$post['PromoCode'];
        $arr['Divide']=$post['Divide'];

        $basem=new Basem();
        if($basem->insert_agent($arr)){
            return json(200);
        }
    }










    // 玩家列表-解封账号
    public function seal(){
        $user=$this->user;
        $id=input('post.id')+0;

        $seal=Db::table('UserInfo')->where('_id',$id)->value('Seal');
        $val='2';
        if($seal==1 || $seal==2){
            $val='0';   
        }
        if(Db::table('UserInfo')->where('_id',$id)->setField('Seal',$val)){
            $basem=new Basem();
            $text=$val==0?'解封':'封禁';
            $basem->insert_caozuo($text.'玩家ID:'.$id);//操作记录
            return json(200);
        }
    }


    //账号转移
    public function zhuanyi(){
        $type=input('post.type');
        $s_id=input('post.s_id');
        $x_id=input('post.x_id');

        $basem=new Basem();

        if($type=='dl'){
            if(!Db::table('Agent')->where('_id',$s_id+0)->value('_id')){
                return json(1);//不存在代理
            }
            if(!Db::table('Agent')->where('_id',$x_id+0)->value('_id')){
                return json(2);//不存在代理
            }
            if(Db::table('Agent')->where('_id',$x_id+0)->setField('SuperiorID',$s_id)){
                $basem->insert_caozuo('将代理ID：'.$x_id.'　转给　代理ID：:'.$s_id);//操作记录
                return json(200);
            }
        }

        if($type=='wj'){
            if(!Db::table('Agent')->where('PromoCode',$s_id)->value('_id')){
                return json(3);//不存在代理
            }
            if(!Db::table('UserInfo')->where('_id',$x_id+0)->value('_id')){
                return json(4);//玩家不存在
            }

            if(Db::table('UserInfo')->where('_id',$x_id)->setField('Referrer',$s_id)){
                $basem->insert_caozuo('将玩家ID：'.$x_id.'　转给　代理ID：:'.$s_id);//操作记录
                return json(200);
            }
        }
    }

    //提现
    public function tixian(){
        $post=input('post.');
        $basem=new Basem();

        if(Db::table('TXlog')->where('_id',$post['id']+0)->setField('State',$post['type']+0)){
            $order=Db::table('TXlog')->where('_id',$post['id']+0)->find();
            switch($order['State']){
                case 1:
                    $text='拒绝';
                break;
                case 2:
                    $text='同意';
                break;
            }
            $basem->insert_caozuo('处理提现订单：'.$order['orderid'].','.$text);//操作记录
            return json(200);
        }
    }




    // 注册送保存
    public function coin_zc(){
        $user=$this->user;
        $of=Db::table('Agent')->where('_id',$user+0)->value('AgentRegGiveOnOff');
        $of=$of==1?0:1;

        if(Db::table('Agent')->where('_id',$user+0)->setField('AgentRegGiveOnOff',$of+0)){
            $text=$of==1?'开启':'关闭';
            $basem=new Basem();
            $basem->insert_caozuo('短信送：'.$text);//操作记录
            return json(200);
        }
    }









    //短信送-金额保存
    public function coin_dx_add(){
        $coin=input('post.coin')+0;
        if(Db::table('SmsCoinConfig')->where('Coin',$coin)->value('Coin')){
            return json(1);//金额已存在
        }


        if(input('?post.id')){
            $id=input('post.id')+0;
            if(Db::table('SmsCoinConfig')->where('_id',$id)->setField('Coin',$coin)){
                return json(200);
            }
        }else{
            $basem=new Basem();
            if($basem->insert_coin_dx($coin)){
                return json(200);
            }
        }
    }

    //短信送-金额删除
    public function coin_dx_del(){
        $id=input('post.id')+0;
        $coin=Db::table('SmsCoinConfig')->where('_id',$id)->value('Coin');
        if(Db::table('SmsCoinConfig')->where('_id',$id)->delete()){
            $basem=new Basem();
            $basem->insert_caozuo('删除短信送'.$coin.'金币项目');//操作记录
            return json(200);
        }
    }

    // 审核
    public function coin_dx_sh(){
        $id=input('post.id')+0;
        $type=input('post.type')+0;
        if(Db::table('SmsGiveCoin')->where('_id',$id)->setField('State',$type)){
            $basem=new Basem();
            $basem->insert_caozuo('审核短信送');//操作记录
            return json(200);
        }
    }

    //申请
    public function coin_dx_sq(){
        $post=input('post.');

        $arr=[];
        $arr['id']=$post['id']+0;
        $arr['user']=$post['user']+0;
        $arr['number']=$post['number']+0;

        $basem=new Basem();
        if($basem->insert_coin_sq($arr)){
            return json(200);
        }
    }

    //发短信
    public function coin_dx_send(){
        $post=input('post.');
        $agent_id=$post['agent_id']+0;
        $user_id=$post['user_id']+0;
        $SmsGiveCoinID=$post['SmsGiveCoinID']+0;

        //代理验证
        $PromoCode=Db::table('Agent')->where('_id',$agent_id)->value('PromoCode');
        if(!$PromoCode){
            return json(1);//代理不存在
        }

        //验证用户
        $phone=Db::table('UserInfo')->where('_id',$user_id)->where('Referrer',$PromoCode)->value('Account');
        // $phone=15622156450;
        if(!$phone){
            return json(2);//用户不存在
        }

        //获取金额
        $sms=Db::table('SmsGiveCoin')->where('_id',$SmsGiveCoinID)->where('AgentID',$agent_id)->where('State',2)->find();
        if(!$sms){
            return json(3);//短信送申请不存在
        }
        $coin=Db::table('SmsCoinConfig')->where('_id',$sms['SmsCoinConfigID']+0)->value('Coin');
        
        //发送
        $config=Db::table('SmsPushConfig')->where('_id',1)->find();

        $api='http://utf8.api.smschinese.cn/?';

        $user=$config['User'];
        $pwd=$config['Pass'];
        $content=$config['Txt'].$coin;
        
        $url=$api."Uid=".$user."&Key=".$pwd."&smsMob=".$phone."&smsText=".$content;
    
        $res=file_get_contents($url);

        return $res;
    }













    //公告修改和新增
    public function gonggao(){
        $arr=[];
        $arr['title']=input('post.title');
        $arr['GongGao']=input('post.GongGao');

        switch(input('post.type')){
            case 'yx':
                $table='GameNotice';
                $text='游戏';
            break;
            case 'dl':
                $table='AgentNotice';
                $text='代理';
            break;
            case 'pm':
                $table='HorseNotice';
                $text='跑马';
            break;
        }

        if(input('post.id')==0){
            $basem=new Basem();
            $arr['table']=$table;
            if($basem->insert_gonggao($arr)){
                return json(200);
            }
        }else{
            if(Db::table($table)->where('_id',input('post.id')+0)->update($arr)){
                $basem=new Basem();
                $basem->insert_caozuo('更新'.$text.'公告');//操作记录
                return json(200);
            }
        }
    }

    //公告删除
    public function gonggao_del(){
        switch(input('post.type')){
            case 'yx':
                $table='GameNotice';
                $text='游戏';
            break;
            case 'dl':
                $table='AgentNotice';
                $text='代理';
            break;
            case 'pm':
                $table='HorseNotice';
                $text='跑马';
            break;
        }

        if(Db::table($table)->where('_id',input('post.id')+0)->delete()){
            $basem=new Basem();
            $basem->insert_caozuo('删除'.$text.'公告');//操作记录
            return json(200);
        }
    }
}