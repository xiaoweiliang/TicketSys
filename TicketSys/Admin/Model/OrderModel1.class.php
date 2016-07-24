<?php
/**
 * 用户订单信息管理
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\PublicTool;
use Common\Common\TimeManager;
use Common\Common\Cookie;
use Common\Common\CacheManager;
use Common\Common\MyPage;
use Common\Common\KeyTool;
use Common\Common\CalculateTicket;
class OrderModel1 extends Model
{
    private $_Model="OrderModel";
    /**
     * 获取model
     */
    public function GetModel()
    {
        return $this->_Model;
    }
    /**
     * 获取总订单数
     */
    public function GetOrderCount()
    {
        $userid=Cookie::GetTHUserId();
        $type=Cookie::GetTFType();
        if(!empty($userid)&&isset($type))
        {
            if($type==-1)
            {
                $result=$this->table("tp_userorder U ,tp_orderinfo O")->where("U.order_sn=O.order_sn and user_id=%d",$userid)->field("user_id")->count();
            }
            else 
            {
                $result=$this->table("tp_userorder U ,tp_orderinfo O")->where("U.order_sn=O.order_sn and user_id=%d and order_type=%d",$userid,$type)->field("user_id")->count();
            }
        }
        else if(!empty($userid))
            $result=$this->table("tp_userorder")->where("user_id=%d",$userid)->field("user_id")->count();
        else if(isset($type)&&$type>=0)
            $result=$this->table("tp_userorder")->where("order_type=%d",$type)->field("user_id")->count();
        else
             $result=$this->table("tp_userorder")->field("user_id")->count();
        if(!empty($result))
        {
            return $result;
        }
        else
            return 0;
    }
    /**
     * 获取总数据
     */
    public function GetCount()
    {
            $result=$this->GetOrderCount();
            return $result;
    }
    /**
     * 获取所有用户的订单信息
     * -1I表示所有订单
     */
    public function GetAllUserOrder($order_type=-1)    
    {
        if($order_type==-1)
        {
            $AllUserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
            ->where("U.order_sn=O.order_sn")
            ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field(array("user_id,U.order_sn","order_type","order_money","time","start_point","end_point"))
            ->order("time desc")
            ->select();
        }
        else if($order_type==0||$order_type==1||$order_type==2)
        {
            $AllUserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
            ->where("U.order_sn=O.order_sn and order_type=%d",$order_type)
            ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field(array("user_id,U.order_sn","order_type","order_money","time","start_point","end_point"))
            ->order("time desc")
            ->select();
        }
        else 
            $AllUserOrder=array();
 
        if(!empty($AllUserOrder))
        {
            $count=count($AllUserOrder);
            for($i=0;$i<$count;$i++)
            {
                $Site=new SiteModel("tp_siteinfo");
                $AllUserOrder[$i]['start_point']=$Site->GetSiteName($AllUserOrder[$i]['start_point']);
                $AllUserOrder[$i]['end_point']=$Site->GetSiteName($AllUserOrder[$i]['end_point']);
                $AllUserOrder[$i]['time']=TimeManager::FormatTime($AllUserOrder[$i]['time']);
            }
        }
         return $AllUserOrder;
    }
    /**
     * 获取单个用户的所有订单
     */
    public function GetUserOrder($user_id,$order_type=-1)
    {
            if(!empty($user_id))
            {
                $UserOrderInfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
                if(empty($UserOrderInfo))
                {
                    $UserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
                    ->where("U.order_sn=O.order_sn and user_id=%d",$user_id)
                    ->field(array("U.order_sn","order_type","order_money","time","start_point","end_point"))
                    ->order("time desc")
                    ->select();
                  
                    if(!empty($UserOrder))
                    {
                        $count=count($UserOrder);
                        for($i=0;$i<$count;$i++)
                        {
                            $Site=new SiteModel("tp_siteinfo");
                            $UserOrder[$i]['start_point']=$Site->GetSiteName($UserOrder[$i]['start_point']);
                            $UserOrder[$i]['end_point']=$Site->GetSiteName($UserOrder[$i]['end_point']);
                            $UserOrder[$i]['time']=TimeManager::FormatTime($UserOrder[$i]['time']);
                            $UserOrder[$i]['user_id']=$user_id;
                        }
                        CacheManager::Set("UserOrderInfo".$user_id, $UserOrder,SESSION_PREFIX1);
                        return $this->FilterOrder($UserOrder,$order_type);
                    }
                    else 
                        return PublicTool::_Empty();
                }
                else
                {
                    return $this->FilterOrder($UserOrderInfo,$order_type);
                }
            } 
            else
                return PublicTool::_Empty();
    }
    /**
     * 提取用户需要的订单
     */
    public function FilterOrder($Order,$type)
    {
        $resl_order=array();
        $offset=MyPage::GetSqlOffset($this->_Model);
        $endoffset=$offset+PAGE_COUNT;
        if($type==-1)
        {
            $count=count($Order);
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
                if(!empty($Order[$i]))
                    $resl_order[]=$Order[$i];
            return $resl_order;

        }      
        else
        {
            $realorder=array();
            $count=count($Order);
            for($i=0;$i<$count;$i++)
            {
            	
                if($Order[$i]['order_type']==$type)
                     $realorder[]=$Order[$i];
            }
            $count=count($realorder);
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
                if(!empty($realorder[$i]))
                    $resl_order[]=$realorder[$i];
            
            return $resl_order; 
        }
    }
    /**
     * 删除用户订单
     */
    public function DelUserOrder($user_id,$order_id)
    {
        if(!empty($order_id)&&strlen($order_id)==12&&!empty($user_id))
        {
            $this->table("tp_userorder")->where("order_sn='%s'",$order_id)->delete();                //删除
            $this->where("order_sn='%s'",$order_id)->delete();                        //删除
            $usernoworder=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
            $count=count($usernoworder);
            for($i=0;$i<$count;$i++)
            {
                if($usernoworder[$i]['order_sn']==$order_id)
                {
                    array_splice($usernoworder,$i,1);                        //这里不能用unset
                    break;
                }         
            }
            CacheManager::Set("UserOrderInfo".$user_id, $usernoworder,SESSION_PREFIX1);
        }
    }
    /**
     * 梁晓伟
     */
    
    
    /**
     * 取消订单
     */
    public function CanelUserOrder($user_id,$order_id)
    {
    	if(!empty($order_id)&&strlen($order_id)==12&&!empty($user_id))
    	{
    		//$user_id=KeyTool::base_decodes($user_id);
    		$this->table("tp_userorder")->where("order_sn='%s'",$order_id)->delete();                //删除
    		$this->where("order_sn='%s'",$order_id)->delete();                        //删除
    		$usernoworder=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
    		$count=count($usernoworder);
    		for($i=0;$i<$count;$i++)
    		{
    		if($usernoworder[$i]['order_sn']==$order_id)
    		{
    			array_splice($usernoworder,$i,1);                        //这里不能用unset
    			break;
    		}
    		}
    		CacheManager::Set("UserOrderInfo".$user_id, $usernoworder,SESSION_PREFIX1);
    		}
    }
    		
    		
    		
    		/**
    		 * 退订单
    		 */
    		public  function  RecedeOrder($user_id,$order_sn)
    		{
    			if(!empty($order_sn)&&strlen($order_sn)==12&&!empty($user_id))
    			{
    				$money_num=$this->GetOrderMoney($order_sn);
    				$user_money=$this->GetUserMoney($user_id);
    				if($money_num)
    				{
    					if($user_money)
    					{
    						$d['order_type']=2;
    						$add['money_num']=$money_num;
    						$add['time']=TimeManager::GetTime();
    						$add['money_type']=1;
    						$add['user_id']=$user_id;
    						$order=$this->table("tp_userorder")->where("order_sn='%s' and order_type=%s",$order_sn,0)->save($d);   //修改订单为退
    					    if(empty($order))
    					    {
    					    	return -6;//不是未出行订单
    					    }
    						$User = M("tp_userinfo");		
    					    $User->user_money = $user_money+$money_num;
    					    $userinfo=$User->where("id= %s ",$user_id)->save(); // 不能用$this->table  	
    					    if(!empty($userinfo))
    					    {
    					    	return -7;//修改账户余额失败
    					    } 
    						$usermon=M('tp_usermoneyaccount');//增加消费记录
    						$usermoneycount=$usermon->add($add);
    						if(empty($usermoneycount))
    						{
    							return -5;//插入消费记录失败
    						}
    						$UserOrderInfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);//订单缓冲
    						$UserSpendInfo=CacheManager::Get("UserSpendInfo".$user_id,SESSION_PREFIX1);//消费缓冲
    						$UserInfo=CacheManager::Get("UserInfo".$user_id,SESSION_PREFIX1);//用户详细信息缓冲,,金额
    						if(!empty($UserOrderInfo))
    						{
    							$count=count($UserOrderInfo);
    							for($i=0;$i<$count;$i++)
    							{
    		
    							if($UserOrderInfo[$i]['order_sn']==$order_sn)
    							{
    							$UserOrderInfo[$i]['order_type']=2;
    									break;
    							}
    							}
    							CacheManager::Set("UserOrderInfo".$user_id, $UserOrderInfo,SESSION_PREFIX1);//修改用户订单缓冲数据
    					}
    					if(!empty($UserSpendInfo))
    					{
    					$count=count($UserSpendInfo);
    					$UserSpend[$count]['id']=$UserSpend[$count-1]['id']+1;
    					$UserSpend[$count]['money_num']=$money_num;
    					$UserSpend[$count]['time']=TimeManager::FormatTime(TimeManager::GetTime());;
    					$UserSpend[$count]['money_type']=1;
    					$UserSpend[$count]['user_id']=$user_id;
    					CacheManager::Set("UserSpendInfo".$user_id, $UserSpendInfo,SESSION_PREFIX1);//增加消费缓冲记录
    					}
    					if(!empty($UserInfo))
    					{
    					$count=count($UserInfo);
    					for($i=0;$i<$count;$i++)
    					{
    		
    					if($UserInfo[$i]['id']==$user_id)
    						{
    						$UserInfo[$i]['user_money']=$user_money+$money_num;
    						break;
    						}
    						}
    						CacheManager::Set("UserInfo".$user_id, $UserInfo,SESSION_PREFIX1);//修改用户订单缓冲数据
    						}
    						return 1;
    						}
    						else
    							return -3;//查找账户余额失败
    								
    					}
    							else
    								return -2;//查找金钱失败
    		
    			}
    				else
    					return -1;//提交数据不正确
   }
    				/**
                  	 * 获取订单金额
    		   	 */
    		   	 public  function GetOrderMoney($order_sn)
    		   	 {
    		   		$re=$this->table('tp_orderinfo')->where("order_sn='%s'",$order_sn)->field('order_money')->select();
    		   		if(!empty($re))
    					{
    					return $re[0]['order_money'];
    		}
    		else
    		return false;
    		}
    		/**
    		* 获取用户余额
    		*/
    		public  function GetUserMoney($user_id)
    		{
    		$re=$this->table('tp_userinfo')->where("id= %s",$user_id)->field('user_money')->select();
    		if(!empty($re))
    		{
    		    return $re[0]['user_money'];
    		}
    			else
    				return false;
   	       }
   	       /**
   	        * 增加订单
   	        */
   	       public function   AddOrder($user_id,$order_sn,$money,$start_point,$end_point)
   	       {
   	       	$usernoworder=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   	       	$count=count($count);
   	       	$date['user_id']=$user_id;
   	       	$date['order_sn']=$order_sn;
   	       	$date['order_type']=3;
   	        $order=$this->table('tp_userorder')->add($date);
   	         if(!empty($order))
   	         {
   	         	$site=new SiteModel('tp_siteinfo');
   	         	$start_id=$site->GetSiteid($start_point);
   	         	$end_id=$site->GetSiteid($end_point);
   	         	$info['order_sn']=$order_sn;
   	         	$info['order_money']=$money;
   	         	$info['time']=TimeManager::GetTime();
   	         	$info['start_point']=$start_id;
   	         	$info['end_point']=$end_id;
   	         	$orderinfo=M('tp_orderinfo');
   	         	$orderinfo->add($info);
   	         	//$orderinfo=$this->table('tp_orderinfo')->add($info);
   	         	$usernoworder[$count]['order_sn']=$order_sn;
   	         	$usernoworder[$count]['order_type']=3;
   	         	$usernoworder[$count]['order_money']=$money;
   	         	$usernoworder[$count]['time']=$info['time'];
   	         	$usernoworder[$count]['start_point']=$start_point;
   	         	$usernoworder[$count]['end_point']=$end_point;
   	         	$usernoworder[$count]['user_id']=$user_id;
   	         	CacheManager::Set("UserOrderInfo".$user_id, $usernoworder,SESSION_PREFIX1);
   	         	return true;
   	         }
   	         else 
   	         	return false;
   	       }
   	       
   	       
   	       /**
   	        *提交订单
   	        */
   	       public function  BeforePayMent($user_id,$start_point,$end_point)
   	       {
   	       	if($this->IsNoPay($user_id))
   	       	{
   	       		return -1;
   	       	}
   	       	if(!empty($user_id)&&!empty($start_point)&&!empty($end_point))
   	       	{
   	       		 
   	       		$site=new SiteModel('tp_siteinfo');
   	       		$start_id=$site->GetSiteid($start_point);
   	       		$end_id=$site->GetSiteid($end_point);
   	       		$money=CalculateTicket::XiAnTicket($start_id, $end_id);//计算票价
   	       		$order_sn=PublicTool::MKOrderSn();
   	       		//添加订单记录
   	       		$order_result=$this->AddOrder($user_id, $order_sn, $money, $start_point,$end_point);
   	       		if($order_result)
   	       		{
   	       			$date['money']=$money;
   	       			$date['order_sn']=$order_sn;
   	       			return $date;
   	       		}
   	       		else
   	       			return 0;
   	       	}
   	       }
   	       /**
   	        * 确认支付
   	        */
   	       public function  OkPayMent($user_id,$order_sn,$user_pay_key)
   	       {
   	       		if(!empty($user_id)&&!empty($order_sn)&&!empty($user_pay_key))
   	       		{
   	       			$user=new UserModel('tp_userinfo');
   	       			$money=$this->GetOrderMoney($order_sn);//获取订单金额
   	       			$is_money=$user->IsUserMoney($user_id, $money);//账户余额
   	       			if(!$is_money)
   	       			{
   	       				return  -3;//余额不足,id不对
   	       			}
   	       			if($this->IsNoPayOrder($user_id,$order_sn))
   	       			{
   	       			
   	       				if($user->IsPayKey($user_id, $user_pay_key))
   	       				{
   	       						if($this->ModifyOrder($user_id, $order_sn))
   	       						{
   	       							if($user->ModifyUserMoney($user_id,$is_money- $money))
   	       							{
   	       								return 1;
   	       							}
   	       							else 
   	       								return -4;//修改账户余额失败
   	       						
   	       						}
   	       						else 
   	       							return -5;//修改账单失败
   	       					
   	       				}
   	       				else 
   	       					return -2;//支付密码错误；
   	       			}
   	       			else
   	       				return -1;//不存在未支付账单
   	       		}
   	       }
   	       /**
  	       * 存在未支付存在返回true
   	       */
   	       public  function IsNoPay($user_id)
   	       {
   	       		$userorderinfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   	       		if(!empty($userorderinfo))
   	       		{
   	       			$count=count($userorderinfo);
   	       			for($i=0;$i<$count;$i<$count)
   	       			{
   	       				if($userorderinfo[$i]['order_type']==3)
   	       				{
   	       					
   	       					return true;
   	       				}
   	       			}
   	       			return false;
   	       		}
   	       		else 
   	       		{
   	       			
   	       			$userorder=$this->table("tp_userorder")->where("user_id=%s and order_type=%s",$user_id,3)->select();
   	       			if(!empty($userorder))
   	       			{
   	       				return true;
   	       			}
   	       			else 
   	       				return false;
   	       		}
   	       }
   	       /**
   	        * 判断是否是未支付账单
   	        */
   	       public function IsNoPayOrder($user_id,$order_sn)
   	       {
   	       	$userorderinfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   	       	if(!empty($userorderinfo))
   	       	{
   	       		$count=count($userorderinfo);
   	       		 for($i=0;$i<$count;$i++)
   	       		 {
   	       		 	if(($userorderinfo[$i]['order_sn'])==$order_sn&&($userorderinfo[$i]['order_type']==3))
   	       		 	{
   	       		 		return true;
   	       		 	}
   	       		 }
   	       		 return false;
   	       	}
   	       	else
   	       	{
   	       		 $orderinfo=$this->table('tp_userorder')->where("order_sn='%s' and order_type=%s",$order_sn,3)->select();
   	       		 if(!empty($orderinfo))
   	       		 {
   	       		 	return true;
   	       		 }
   	       		 else 
   	       		 	return false;
   	       		 
   	       	}
   	       }
   	       /**
   	        * 支付完 修改订单
   	        */
   	      function ModifyOrder($user_id,$order_sn)
   	      {
   	      	$OrderInfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
 			$order=$this->table("tp_userorder")->where("order_sn='%s'",$order_sn)->setField("order_type",0);
 			if(!empty($order))
 			{
 				if(!empty($OrderInfo))
 				{
 					$count=count($OrderInfo);
 					for($i=0;$i<$count;$i++)
 					{
 					    if($OrderInfo[$i]['order_sn']==$order_sn)
 						{
 							$OrderInfo[$i]['order_sn']=0;
 							CacheManager::Set("UserOrderInfo".$user_id, $OrderInfo,SESSION_PREFIX1);
 							return true;
 					    }
 					}
 					//return false;
 				}
 				return true;
 				
 			}
 			else 
 				return false;
   	      	
   	      	
   	      }
}