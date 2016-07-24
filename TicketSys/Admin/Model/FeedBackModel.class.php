<?php
/**
 * 用户反馈内容管理
 */
namespace  Admin\Model;
use Think\Model;
use Common\Common\Cookie;
use Common\Common\CacheManager;
use Common\Common\MyPage;
use Common\Common\TimeManager;
use Common\Common\PublicTool;
use Common\Common\KeyTool;
class FeedBackModel extends Model
{
    private $_Model="FeedBackModel";
    /**
     * 获取model
     */
    public function getModel()
    {
        return $this->_Model;
    }
    /**
     * 获取用户自动票总数
     */
    public function GetFBCount()
    {
        $userid=Cookie::GetTHUserId();
         if(!empty($userid))
            $result=$this->where("user_id=%d",$userid)->field("id")->count();
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
        $result=$this->GetFBCount();
        return $result;
    }
    /**
     * 获取所有用户的自动票记录
     * -1I表示所有自动票记录
     */
    public function GetAllUserFB()
    {
        $AllUserFB=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
        ->order("time desc")
        ->field(array("id","user_id","content","time"))
        ->select();
        if(!empty($AllUserFB))
        {
            $count=count($AllUserFB);
            for($i=0;$i<$count;$i++)
            {
                $AllUserFB[$i]['time']=TimeManager::FormatTime($AllUserFB[$i]['time']);
            }
        }
        return $AllUserFB;
    }
    /**
     * 获取单个用户的所有反馈
     */
    public function GetUserFB($user_id)
    {
        if(!empty($user_id))
        {
            $UserFBInfo=CacheManager::Get("UserFBInfo".$user_id,SESSION_PREFIX1);
            if(empty($UserFBInfo))
            {
                $UserFB=$this->where("user_id=%d",$user_id)->field(array("id","content","time"))
                ->order("time desc")
                ->select();
                if(!empty($UserFB))
                {
                    $count=count($UserFB);
                    for($i=0;$i<$count;$i++)
                    {
                        $UserFB[$i]['time']=TimeManager::FormatTime($UserFB[$i]['time']);
                        $UserFB[$i]['user_id']=$user_id;
                    }
                    CacheManager::Set("UserFBInfo".$user_id, $UserFB,SESSION_PREFIX1);
                    return $this->FilterOrder($UserFB);
                }
                else
                    return PublicTool::_Empty();
            }
            else
            {
                return $this->FilterOrder($UserFBInfo);
            }
        }
    }
    /**
     * 提取用户需要的订单
     */
    public function FilterOrder($UserMsg)
    {
        $resl_order=array();
        $offset=MyPage::GetSqlOffset($this->_Model);
        $endoffset=$offset+PAGE_COUNT;
        $count=count($UserMsg);
        if($endoffset>$count)
            $endoffset=$count;
        for($i=$offset;$i<$endoffset;$i++)
            $resl_order[]=$UserMsg[$i];
        return $resl_order;
    }
    /**
     * 删除用户反馈记录
     */
    public function DelUserFB($user_id,$fbid)
    {
        if(!empty($fbid)&&!empty($user_id))
        {
            $this->where("id=%d",$fbid)->delete();                //删除
            $usernowsfb=CacheManager::Get("UserFBInfo".$user_id,SESSION_PREFIX1);
            $count=count($usernowsfb);
            for($i=0;$i<$count;$i++)
            {
                if($usernowsfb[$i]['id']==$fbid)
                {
                    array_splice($usernowsfb,$i,1);                        //这里不能用unset
                    break;
                }
            }
            CacheManager::Set("UserFBInfo".$user_id, $usernowsfb,SESSION_PREFIX1);
        }
    }
    /**
     * 设置反馈内容
     */
    public  function  AddUserFB($user_id,$content)
    {
    	if(!empty($user_id)&&!empty($content))
    	{
    		$user_id=KeyTool::base_decodes($user_id);
    		$date['user_id']=$user_id;
    		$date['content']=$content;
    		$date['time']=TimeManager::GetTime();
    		$result=$this->table("tp_feedback")->add($date);
    		if(!empty($result))
    		{
    			return 1;
    		}
    		else 
    			return -1;
    	}
    	else 
    		return -2;
    }
}