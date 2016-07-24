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
use Common\Common\CalculateTicket;
use Common\Common\KeyTool;
class OrderModel extends Model
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
            $value[]=$userid;
            $where="U.order_sn=O.order_sn and user_id=%d";
            if($type>0)
            {
                if($type==1||$type==2||$type==3||$type==4)            //1  未完成  2 未出行   3已出行        4  已完成
                {
                    if($type==4)
                        $where.=" and  is_complete>=2";
                    else if($type==1||$type==2)
                    {
                        $where.=" and  is_complete=%d";
                        $value[]=$type;
                    }                 
                    else
                        $where.=" and order_type=0  and  is_complete=3";
                }
                else if($type==5||$type==6)
                {
                    $type=$type-4;
                    $where.=" and order_type=%d";
                    $value[]=$type;
                }
            }
            $result=$this->table("tp_userorder U ,tp_orderinfo O")->where($where,$value)->field("user_id")->count();
            dump($this->_sql());
        }
        else if(!empty($userid))
            $result=$this->table("tp_userorder")->where("user_id=%d",$userid)->field("user_id")->count();
        else if(isset($type)&&$type>=0)
        {
            $where="";
            if($type==1||$type==2||$type==3||$type==4)            //1  未完成  2 未出行   3已出行        4  已完成
            {
                if($type==4)
                    $where="is_complete>=2";
                else  if($type==1||$type==2)
                    $where="is_complete=%d";
                else 
                    $where="order_type=0 and is_complete=3";
            }
            else if($type==5||$type==6)
            {
                $type=$type-4;
                $where="order_type=%d";
            }
            $result=$this->table("tp_userorder")->where($where,$type)->field("user_id")->count();
        }
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
    /*$AllUserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
    ->where("U.order_sn=O.order_sn and order_type=%d",$order_type)
    ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
    ->field(array("user_id,U.order_sn","order_type","order_money","time","start_point","end_point"))
    ->order("time desc")
    ->select();*/
    public function GetAllUserOrder($order_type=-1)    
    {
        $where="U.order_sn=O.order_sn";
        $filed=array("user_id,U.order_sn","order_type","order_money","time","start_point","end_point","is_complete");
        $value=array();
        $User=new UserModel("tp_userinfo");
        if($order_type==-1)
        {
            $AllUserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
            ->where($where)
            ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field($filed)
            ->order("time desc")
            ->select();
        }
        else if($order_type==1||$order_type==2||$order_type==3||$order_type==4)            //1  未完成  2 未出行   3已出行        4  已完成
        {
                if($order_type==4)
                {
                    $where.=" and  is_complete>=2";
                }               
                else if($order_type==1||$order_type==2)
                    $where.="  and is_complete=%d";
                else
                    $where.=" and order_type=0 and is_complete=3";
        }
        else if($order_type==5||$order_type==6)
        {
            $order_type=$order_type-4;
            $where.=" and order_type=%d";
        }
        else 
            $AllUserOrder=array();
        if(!empty($where)&&$order_type>0)
            $AllUserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
            ->where($where,$order_type)
            ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field($filed)
            ->order("time desc")
            ->select();
        
        dump($this->_sql());
        if(!empty($AllUserOrder))
        {
            $City=new   CityModel("tp_city");
            $count=count($AllUserOrder);
            for($i=0;$i<$count;$i++)
            {
                $Site=new SiteModel("tp_siteinfo");
                $userinfo=$User->GetUserInfo($AllUserOrder[$i]['user_id']);
                if($AllUserOrder[$i]['order_type']==0)               //买票
                {
                    if($AllUserOrder[$i]['is_complete']==1)
                        $AllUserOrder[$i]['order_type']=1;
                    else if($AllUserOrder[$i]['is_complete']==2)
                        $AllUserOrder[$i]['order_type']=2;
                    else if($AllUserOrder[$i]['is_complete']==3)
                        $AllUserOrder[$i]['order_type']=3;
                }
                 else 
                     $AllUserOrder[$i]['order_type']=$AllUserOrder[$i]['order_type']+4;
                $AllUserOrder[$i]['user_phone']=$userinfo['phone_no'];
                $AllUserOrder[$i]['city_name']=$City->GetCityNameBySite($AllUserOrder[$i]['start_point']);
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
                    $City=new   CityModel("tp_city");
                    $Site=new SiteModel("tp_siteinfo");
                    $UserOrder=$this->table("tp_userorder  U,tp_orderinfo O")
                    ->where("U.order_sn=O.order_sn and user_id=%d",$user_id)
                    ->field(array("U.order_sn","order_type","order_money","time","start_point","end_point","is_complete"))
                    ->order("time desc")
                    ->select();
                   
                    if(!empty($UserOrder))
                    {
                        $count=count($UserOrder);
                        for($i=0;$i<$count;$i++)
                        {
                            $UserOrder[$i]['city_name']=$City->GetCityNameBySite($UserOrder[$i]['start_point']);
                            $UserOrder[$i]['start_point']=$Site->GetSiteName($UserOrder[$i]['start_point']);
                            $UserOrder[$i]['end_point']=$Site->GetSiteName($UserOrder[$i]['end_point']);
                            $UserOrder[$i]['time']=TimeManager::FormatTime($UserOrder[$i]['time']);
                            $UserOrder[$i]['user_id']=$user_id;
                            if($UserOrder[$i]['order_type']==0)               //买票
                            {
                                if($UserOrder[$i]['is_complete']==1)
                                    $UserOrder[$i]['order_type']=1;
                                else if($UserOrder[$i]['is_complete']==2)
                                    $UserOrder[$i]['order_type']=2;
                                else if($UserOrder[$i]['is_complete']==3)
                                    $UserOrder[$i]['order_type']=3;
                            }
                            else
                                $UserOrder[$i]['order_type']=$UserOrder[$i]['order_type']+4;
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
     * 修改
     */
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
                if($type==4)
                {
                     if($Order[$i]['is_complete']>=2)
                        $realorder[]=$Order[$i];
                }
               
                else if($type==1)
                { 
                    if($Order[$i]['is_complete']==$type)
                        $realorder[]=$Order[$i];
                }
                ////未出行的
                else if($type==2)
                {
                	if($Order[$i]['is_complete']==2&&$Order[$i]['order_type']==0)
                		$realorder[]=$Order[$i];
                }
                else if($type==3)//包括已出行的和退票的
                {
                	if($Order[$i]['order_type']==1||$Order[$i]['is_complete']==3)
                		$realorder[]=$Order[$i];
                }
                else if($type==6)
                {
                	if($Order[$i]['order_type']==2)
                		$realorder[]=$Order[$i];
                }
                ////
                else if($Order[$i]['order_type']==$type)
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
   	 *提交订单
   	 */
   	  public function  BeforePayMent($user_id,$start_point,$end_point)
   	  {
   	  	$user_id=KeyTool::base_decodes($user_id);
   	     if($this->IsNoPay($user_id))
   	       		return -1;
   	     if(!empty($user_id)&&!empty($start_point)&&!empty($end_point))
   	       	{
   	       		$site=new SiteModel('tp_siteinfo');
   	       		//$start_id=$site->GetSiteid($start_point);
   	       		//$end_id=$site->GetSiteid($end_point);
   	       		
   	       	
   	        	$money=CalculateTicket::XiAnTicket($start_point, $end_point);//计算票价
   	       		$order_sn=PublicTool::MKOrderSn();
   	       		$start_id=$site->GetSiteidd($start_point);
   	       		$end_id=$site->GetSiteidd($end_point);
   	       		$time=TimeManager::GetTime();
   	       		//添加订单记录
   	       		$order_result=$this->AddOrder($user_id, $order_sn, $money,$time, $start_id,$end_id);
   	       		if($order_result)
   	       		{
   	       			$date['money']=$money;
   	       			$date['order_sn']=$order_sn;
   	       			$date['time']=TimeManager::FormatTime1($time);
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
   	  	$user_id=KeyTool::base_decodes($user_id);
   	  	$user=new UserModel('tp_userinfo');
   	  	$phone_no=$user->GetUserPhone($user_id);
   	  	$user_pay_key=KeyTool::_before_base_decode($phone_no, $user_pay_key);
   	  	
   	  	if($user->IsExistPayrKey($user_id)==1)
   	  	{
   	  		return -6;//设置支付密码
   	  	}
   	  	if(!empty($user_id)&&!empty($order_sn)&&!empty($user_pay_key))
   	  	{
   	  		$user=new UserModel('tp_userinfo');
   	  		$money=$this->GetOrderMoney($order_sn);//获取订单金额
   	  		$is_money=$user->IsUserMoney($user_id, $money);//金额够用时返回账户余额
   	  		
   	  		if($this->IsNoPayOrder($user_id,$order_sn))
   	  		{
   	  			if(!$is_money)
   	  			{
   	  				return  -3;//余额不足,id不对
   	  			}
   	  			if($user->IsPayKey($user_id, $user_pay_key))
   	  			{
   	  				if($this->ModifyOrder($user_id, $order_sn,2))///////////
   	  				{
   	  					if($user->ModifyUserMoney($user_id,-$money))
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
   	   * 增加未支付订单
   	   */
   	  public function   AddOrder($user_id,$order_sn,$money,$time,$start_point,$end_point)
   	  {
   	  	$usernoworder=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   	  	$count=count($usernoworder);
   	  	$date['user_id']=$user_id;
   	  	$date['order_sn']=$order_sn;
   	  	$date['order_type']=0;
   	  	$date['is_complete']=1;
   	  	$order=$this->table('tp_userorder')->add($date);
   	  	if(!empty($order))
   	  	{
   	  		if(empty($usernoworder))
   	  			$count=0;
   	  		$site=new SiteModel('tp_siteinfo');
   	  		//$start_id=$site->GetSiteid($start_point);
   	  		//$end_id=$site->GetSiteid($end_point);
   	  		$start_id=$start_point;
   	  		$end_id=$end_point;
   	  		$info['order_sn']=$order_sn;
   	  		$info['order_money']=$money;
   	  		$info['time']=$time;
   	  		$info['start_point']=$start_id;
   	  		$info['end_point']=$end_id;
   	  		$orderinfo=M('tp_orderinfo');
   	  		$orderinfo->add($info);
   	  		//$orderinfo=$this->table('tp_orderinfo')->add($info);
   	  		$usernoworder[$count]['order_sn']=$order_sn;
   	  		$usernoworder[$count]['order_type']=0;
   	  		$usernoworder[$count]['is_complete']=1;
   	  		$usernoworder[$count]['order_money']=$money;
   	  		$usernoworder[$count]['time']=$info['time'];
   	  		$usernoworder[$count]['start_point']=$site->GetName($start_id);
   	  		$usernoworder[$count]['end_point']=$site->GetName($end_id);;
   	  		$usernoworder[$count]['user_id']=$user_id;
   	  		CacheManager::Set("UserOrderInfo".$user_id, $usernoworder,SESSION_PREFIX1);
   	  		return true;
   	  	}
   	  	else
   	  		return false;
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
   	  				$d['order_type']=1;
   	  				$d['is_complete']=2;
   	  				$add['money_num']=$money_num;
   	  				$add['time']=TimeManager::GetTime();
   	  				$add['money_type']=1;
   	  				$add['user_id']=$user_id;
   	  				$order=$this->table("tp_userorder")->where("user_id=%s and order_sn='%s' and order_type=%s and is_complete=%s",$user_id,$order_sn,0,2)->save($d);   //修改订单为退
   	  				if(empty($order))
   	  				{
   	  					return -6;//不是未出行订单
   	  				}
   	  				$User = M("tp_userinfo");
   	  				$User->user_money = $user_money+$money_num;
   	  				$userinfo=$User->where("id= %s ",$user_id)->save(); // 不能用$this->table
   	  				if(empty($userinfo))
   	  				{
   	  					return -7;//修改账户余额失败
   	  				}
   	  				$UserOrderInfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);//订单缓冲
   	  				$UserInfo=CacheManager::Get("UserInfo".$user_id,SESSION_PREFIX1);//用户详细信息缓冲,,金额
   	  				if(!empty($UserOrderInfo))
   	  				{
   	  					$count=count($UserOrderInfo);
   	  					for($i=0;$i<$count;$i++)
   	  					{
   	  
   	  					if($UserOrderInfo[$i]['order_sn']==$order_sn)
   	  					{
   	  					$UserOrderInfo[$i]['order_type']=1;
   	  					$UserOrderInfo[$i]['is_complete']=2;
   	  					break;
   	  					}
   	  					}
   	  					CacheManager::Set("UserOrderInfo".$user_id, $UserOrderInfo,SESSION_PREFIX1);//修改用户订单缓冲数据
   	  				}
   	  					if(!empty($UserInfo))
   	  					{
   	  						if($UserInfo[0]['id']==$user_id)
   	  							$UserInfo[0]['user_money']=$user_money+$money_num;
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
     * 判断是否存在未支付 账单    存在返回true
     */
    public  function IsNoPay($user_id)
    {
    	$userorderinfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
    	if(!empty($userorderinfo))
    	{
    		$count=count($userorderinfo);
    		for($i=0;$i<$count;$i++)
    		{
    			if($userorderinfo[$i]['order_type']==0&&$userorderinfo[$i]['is_complete']==1)
    				return true;
    		}
    		return false;
    	}
    	else
    	{
    
    		$userorder=$this->table("tp_userorder")->where("user_id=%s and order_type=%s and is_complete=%s",$user_id,0,1)->select();
    		if(!empty($userorder))
    			return true;
    		else
    			return false;
    	}
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
    		if(($userorderinfo[$i]['order_sn'])==$order_sn
    	    &&($userorderinfo[$i]['order_type']==0
            &&($userorderinfo[$i]['is_complete']==1)))
    			return true;
    		}
    		return false;
    	}
    	else
    	{
    		$orderinfo=$this->table('tp_userorder')->where("order_sn='%s' and order_type=%s and is_complete=%s",$order_sn,0,1)->select();
    		if(!empty($orderinfo))
    			return true;
    		else
    			return false;
    
    	}
   }
   /**
    * 支付完 修改订单
    */
   function ModifyOrder($user_id,$order_sn,$is_complete)
   {
   	$OrderInfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   	$order=$this->table("tp_userorder")->where("user_id=%s and order_sn='%s'",$user_id,$order_sn)->setField("is_complete",$is_complete);
   	if(!empty($order))
   	{
   		if(!empty($OrderInfo))
   		{
   			$count=count($OrderInfo);
   			for($i=0;$i<$count;$i++)
   			{
   				if($OrderInfo[$i]['order_sn']==$order_sn)
   				{
   					$OrderInfo[$i]['is_complete']=$is_complete;
   					CacheManager::Set("UserOrderInfo".$user_id, $OrderInfo,SESSION_PREFIX1);
   					return true;
   				}
   			}
   		   return false;
   		}
   	    return true;	
   	}
   	else
   		return false; 
   	}
   	/**
   	 * 充值订单
   	 */
   	public  function RechangeOrder($user_id,$order_sn,$time,$money)
   	{
   		$userorder=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   		$count=count($userorder);
   		$date['user_id']=$user_id;
   		$date['order_sn']=$order_sn;
   		$date['order_type']=2;
   		$date['is_complete']=2;
   		$userorder=$this->table('tp_userorder')->add($date);
   		$date_orderinfo['order_sn']=$order_sn;
   		$date_orderinfo['order_money']=$money;
   		$date_orderinfo['time']=$time;
   		$orderinfo=M(tp_orderinfo);
   		$userorderinfo=$orderinfo->add($date_orderinfo);
   		if(!empty($userorder)&&!empty($userorderinfo))
   		{
   			if(empty($userorder))
   				$count=0;
   			$date[$count]['time']=TimeManager::FormatTime($time);
   			$date[$count]['user_id']=$user_id;
   			$date[$count]['order_sn']=$order_sn;
   			$date[$count]['order_type']=2;
   			$date[$count]['is_complete']=2;
   			$date[$count]['order_money']=$money;
   			CacheManager::Set("UserOrderInfo".$user_id,$date,SESSION_PREFIX1);
   			return true;
   		}
   		else
   			return false;
   	}
   	/**
   	 * 取消订单
   	 */
   	public function CancelOrder($user_id,$order_sn)
   	{
   	//	$user_id=KeyTool::base_decodes($user_id);
   		$order=CacheManager::Get("UserPrderInfo".$user_id,SESSION_PREFIX1);
   		$count=count($order);
   		$date['order_type']=3;
   		$date['is_complete']=-2;
   		$result=$this->table("tp_userorder")->where("order_sn='%s' and order_type=%s and is_complete=%s",$order_sn,0,1)->setField($date);
   		if(!empty($result))
   		{
   			if(!empty($order))
   			{
   				for($i=0;$i<$count;$i++)
   				{
   					if($order[$i]['order_sn']==$order_sn)
   					{
   						$order[$i]['order_type']=3;
   						$order[$i]['is_complete']=-2;
   						CacheManager::Set("UserOrderInfo".$user_id, $order,SESSION_PREFIX1);
   						return 1;
   					}
   				}
   			}	
   			return 1;
   		}
   		else
   			return -1;
   		
   	}
   	/**
   	 * 判断是否是未出行订单
   	 */
   	public function IsNoTripOrder($user_id,$order_sn)
   	{
   		$userorderinfo=CacheManager::Get("UserOrderInfo".$user_id,SESSION_PREFIX1);
   		if(!empty($userorderinfo))
   		{
   			$count=count($userorderinfo);
   			for($i=0;$i<$count;$i++)
   			{
   			if(($userorderinfo[$i]['order_sn'])==$order_sn
   			&&($userorderinfo[$i]['order_type']==0
   					&&($userorderinfo[$i]['is_complete']==2)))
   				return true;
   			}
   			return false;
   		}
   		else
   			{
   			$orderinfo=$this->table('tp_userorder')->where("user_id=%s and order_sn='%s' and order_type=%s and is_complete=%s",$user_id,$order_sn,0,2)->select();
    		if(!empty($orderinfo))
   	    		return true;
   			else
   				return false;
   	
   			}
   	}
   	public function QrcodeTicket($POST)
   	{
   		$order_sn=$POST['order_sn'];
   		$user_id=KeyTool::base_decodes($POST['user_id']);
   		if($this->IsNoTripOrder($user_id, $order_sn))
   		{
   			//修改订单
   			if($this->ModifyOrder($user_id, $order_sn, 3))
   			{
   				return 1;
   			}
   			else 
   				return -2;//系统错误
   			
   		}
   		else 
   			return 0;//不是未出行订单
   	}
}