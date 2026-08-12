<?php
namespace app\index\controller;
use think\Controller;
use think\captcha\Captcha;
use think\Db;

class Login extends Controller{

	//登录页面
    public function login(){
    	return $this->fetch();
    }


    //登录，md5加密admin/123456
    public function login_in(){
        $post=input('post.');

        if(captcha_check($post['yzm'])){
            $name=$post['name'];
            $pwd=md5($post['pwd']);

            if($name=='admin'){
                $user=Db::table('Admin')->where('_id',1)->where('pwd',$pwd)->value('name');  
            }else{
                $user=Db::table('Agent')->where('Agentaccount',$name)->where('PassWord',$pwd)->value('_id');
            }

            if($user){
                session('user',$user);
                return json(200);
            }else{
                return json(2);//账号密码错误
            }
        }else{
            return json(1);//验证码错误
        }
    }


    //登出
    public function login_out(){
    	session('user',null);
    	$this->success('退出成功！','login/login');
    }


    //验证码
    public function yzm(){
        ob_clean();
        $cf=array();
        $cf['length']=4;
        $cf['fontSize']='50';
        $cf['useCurve']=false;
        $cap=new Captcha($cf);
        return $cap->entry();
    }


    public function test(){
        return $this->fetch();
    }
}
