<?php
/**
 * 用户接口入口
 */
namespace Mobile\Controller;
use Think\Controller;
use Common\Common\PublicTool;
use Common\Common\JsonOperate;
use Common\Common\KeyTool;
use Common\Common\SessionManager;
use Common\Common\ToolBack;
use Common\Common\CacheManager;
class IndexController extends Controller 
{
	/**
	 * 所有的控制器
	 */
	private $_allcontroller=array(
		'User'=>1,
		'UserModify'=>1,	
		'Index'=>1,
		'UserModifyPWD'=>1,
		'FeedBack'=>1,
		'Consume'=>1,
		'Entrance'=>1,
		'OutBound'=>1,
		'Pay'=>1,
		'SubWay'=>1,
		'UserOrder'=>1,
		'QrcodeTicket'=>1,
			
	);
	/**
	 * 检测控制器存在
	 */
	public function CheckComtroller($cle)
	{
		if(($this->_allcontroller[$cle])==1)
				return true;
		else 
			return false;
	}
	
	
    /**
     * 系统默认入口
     * 系统接口路由
     */
	
        /**
         * 1：所有接口的入口地址
         * 2：检测地址的合法性 （合法：。。。 不合法：写日志返回错误消息）
         * 3：检测请求数据的合法性（合法：。。。 不合法：写日志返回错误消息）
         * 4：处理数据
         * 5：返回消息
         */
	public function index()
    {
		$array=$_POST;    
		if(!empty($array['m'])&&$this->CheckComtroller($array['m']))
    	{
    		$controler="Mobile\Controller\\".$array['m']."Controller";
    	
    		if(PublicTool::CheckFunction($controler, $array['action']))
    		{
    			
    			if($this->IsOnline($array['action'], $array['session_id'], $array['user_id']))
    			{
    				
    				
    				$obj=new $controler();
    				$date=$obj->$array['action']();
    				$this->JsonReturn($date);
    			}
    			else 
    			{
    				$date['is_ok']=411;
    				echo json_encode($date);
    			}
    			
    		}
    		else 
    		{
    			//LogManager::writelog('请求数据不合法'.$_GET['action'],'error');
    			$result['is_ok']=10;
    			echo JsonOperate::JsonEncode($result);
    		}
    	}
    	else
    	{
    	     //LogManager::writelog('请求地址不合法'.$_GET['m'],'error');
    		$result['is_ok']=410;
    		echo JsonOperate::JsonEncode($result);
    	}
  }
	
  		  
     public function IsOnline($action,$session_id,$user_id)
     {
     	if($action=="UserLogin"||$action=="UserRegister")
     	{
     		return true;
     	}
     	
     	$user_id=KeyTool::base_decodes($user_id);
     	$user_id_tempt=SessionManager::GetUserId();
     	
   
     
     	if($user_id_tempt==$user_id)
     	{
     		return true;
     	}
     	else 
     		return false;
     }
	/**
	 * jsom返回
	 */
     public function JsonReturn($value)
     {
     	if(is_array($value)&&!empty($value))
     	{
     		ToolBack::echoback(1,$value);
     	}
     	else 
     	{
     		if(empty($value))
     		{
     			$value=0;
     		}
     		$date['is_ok']=$value;
     		echo JsonOperate::JsonEncode($date);
     	}
     }	  
}