<?php
/**
 * 用户消费管理
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\Cookie;
use Common\Common\CacheManager;
use Common\Common\MyPage;
use Common\Common\TimeManager;
use Common\Common\PublicTool;
class SpendModel extends Model
{
   
    private $_Model="SpendModel";
    /**
     * 获取model
     */
    public function GetModel()
    {
        return $this->_Model;
    }
     /**
     * 获取用户消费记录总数
     */
    public function GetSpendCount()
    {
        $userid=Cookie::GetTHUserId();
        $type=Cookie::GetTFType();;
        if(!empty($userid)&&isset($type)&&$type!=-1)
            $result=$this->where("user_id=%d and money_type=%d",$userid,$type)->field("id")->count();
        else if(!empty($userid))
            $result=$this->where("user_id=%d",$userid)->field("id")->count();
        else if(isset($type)&&$type!=-1)
            $result=$this->where("money_type=%d",$type)->field("id")->count();
        else
            $result=$this->field("id")->count();
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
         $result=$this->GetSpendCount();
        return $result;
    }
    /**
     * 获取所有用户的消费记录
     * -1I表示所有消费记录
     */
    public function GetAllUserSpend($spend_type=-1)
    {
        if($spend_type==-1)
        {
            $AllUserSpend=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field(array("id","user_id","money_num","time","money_type"))
            ->order("time desc")
            ->select();
        }
        else if($spend_type==1||$spend_type==2||$spend_type==3||$spend_type==0)
        {
            $AllUserSpend=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->where("money_type=%d",$spend_type)
            ->field(array("id","user_id","money_num","time","money_type"))
            ->order("time desc")
            ->select();
        }
        if(!empty($AllUserSpend))
        {
            $count=count($AllUserSpend);
            for($i=0;$i<$count;$i++)
            {
                $AllUserSpend[$i]['time']=TimeManager::FormatTime($AllUserSpend[$i]['time']);
            }
        }
        return $AllUserSpend;
    }
    /**
     * 获取单个用户的所有消费
     */
    public function GetUserSpend($user_id,$spend_type=-1)
    {
    	
        if(!empty($user_id))
        {
        	
            $UserSpendInfo=CacheManager::Get("UserSpendInfo".$user_id,SESSION_PREFIX1);
            if(empty($UserSpendInfo))
            {
            	
                $UserSpend=$this->where("user_id=%d",$user_id)->field(array("id","money_num","time","money_type"))
                ->order("time desc")
                ->select();
                if(!empty($UserSpend))
                {
                    $count=count($UserSpend);
                    for($i=0;$i<$count;$i++)
                    {
                        $UserSpend[$i]['time']=TimeManager::FormatTime($UserSpend[$i]['time']);
                        $UserSpend[$i]['user_id']=$user_id;
                       
                    }
                    CacheManager::Set("UserSpendInfo".$user_id, $UserSpend,SESSION_PREFIX1);
                    return $this->FilterOrder($UserSpend,$spend_type);
                }
                else
                    return PublicTool::_Empty();
            }
            else
            {
            	
                return $this->FilterOrder($UserSpendInfo,$spend_type);
            }
        }
    }
    /**
     * 提取用户需要的订单
     */
    public function FilterOrder($UserSpend,$type)
    {
        $resl_order=array();
         $offset=MyPage::GetSqlOffset($this->_Model);
        $endoffset=$offset+PAGE_COUNT;
        if($type==-1)
        {
            $count=count($UserSpend);
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
                $resl_order[]=$UserSpend[$i];
            return $resl_order;
        }
        else
        {
            $realorder=array();
            $count=count($UserSpend);
            for($i=0;$i<$count;$i++)
            {
                if($UserSpend[$i]['money_type']==$type)
                    $realorder[]=$UserSpend[$i];
            }
            $count=count($realorder);
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
                $resl_order[]=$realorder[$i];
            return $resl_order;
        }
    }
    /**
     * 删除消费记录
     */
    public function DelUserSpend($user_id,$spendid)
    {
        if(!empty($spendid)&&!empty($user_id))
        {
            $this->where("id=%d",$spendid)->delete();                //删除
            $usernowspend=CacheManager::Get("UserSpendInfo".$user_id,SESSION_PREFIX1);
            $count=count($usernowspend);
            for($i=0;$i<$count;$i++)
            {
                if($usernowspend[$i]['id']==$spendid)
                {
                    array_splice($usernowspend,$i,1);                        //这里不能用unset
                    break;
                }         
            }
            CacheManager::Set("UserSpendInfo".$user_id, $usernowspend,SESSION_PREFIX1);
        }
    }
}