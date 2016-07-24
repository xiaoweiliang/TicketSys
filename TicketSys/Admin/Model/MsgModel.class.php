<?php
/**
 * 用户提醒消息管理
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\Cookie;
use Common\Common\CacheManager;
use Common\Common\MyPage;
use Common\Common\TimeManager;
use Common\Common\PublicTool;
class MsgModel extends Model
{
    private $_Model="MsgModel";
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
    public function GetMsgCount()
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
        $result=$this->GetMsgCount();
        return $result;
    }
    /**
     * 获取所有用户的自动票记录
     * -1I表示所有自动票记录
     */
    public function GetAllUserMsg()
    {
        $AllUserMsg=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
        ->order("time desc")
        ->field(array("id","user_id","message","time"))
        ->select();
        if(!empty($AllUserMsg))
        {
            $count=count($AllUserMsg);
            for($i=0;$i<$count;$i++)
            {
                $AllUserMsg[$i]['time']=TimeManager::FormatTime($AllUserMsg[$i]['time']);
            }
        }
        return $AllUserMsg;
    }
    /**
     * 获取单个用户的所有消费
     */
    public function GetUserMsg($user_id)
    {
        if(!empty($user_id))
        {
            $UserMsgInfo=CacheManager::Get("UserMsgInfo".$user_id,SESSION_PREFIX1);
            if(empty($UserMsgInfo))
            {
                $UserMsg=$this->where("user_id=%d",$user_id)->field(array("id","message","time"))
                ->order("time desc")
                ->select();
                if(!empty($UserMsg))
                {
                    $count=count($UserMsg);
                    for($i=0;$i<$count;$i++)
                    {
                        $UserMsg[$i]['time']=TimeManager::FormatTime($UserMsg[$i]['time']);
                        $UserMsg[$i]['user_id']=$user_id;
                    }
                    CacheManager::Set("UserMsgInfo".$user_id, $UserMsg,SESSION_PREFIX1);
                    return $this->FilterOrder($UserMsg);
                }
                else
                    return PublicTool::_Empty();
            }
            else
            {
                return $this->FilterOrder($UserMsgInfo);
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
     * 添加用户消息
     */
    public function AddUserMsg($user_id,$content)
    {
        if(!empty($user_id)&&!empty($content))
        {
            $userinfo=new UserModel("tp_userinfo");
            $no=$userinfo->GetUserPhone($user_id);
            if(!empty($no))
            {
                /**
                 * 表示用户存在
                 */
                $data['message']=$content;
                $data['time']=TimeManager::GetTime();
                $data['user_id']=$user_id;
                $accountid=$this->add($data);
                if($accountid>0)                //添加成功
                {
                    $data['id']=$accountid;
                    $data['time']=TimeManager::FormatTime($data['time']);
                    $usernowsmsg=CacheManager::Get("UserMsgInfo".$user_id,SESSION_PREFIX1);
                    if(!empty($usernowsmsg))
                    {
                        $usernowsmsg[]=$data;
                        CacheManager::Set("UserMsgInfo".$user_id, $usernowsmsg,SESSION_PREFIX1);
                    }
                    return true;
                }  
               else 
                   return false;
            }
            else 
                return false; 
        }
        else
            return false;
    }
    /**
     * 删除用户消息
     */
    public function DelUserMsg($user_id,$msgid)
    {
        if(!empty($msgid)&&!empty($user_id))
        {
            $this->where("id=%d",$msgid)->delete();                //删除
            $usernowsmsg=CacheManager::Get("UserMsgInfo".$user_id,SESSION_PREFIX1);
            $count=count($usernowsmsg);
            for($i=0;$i<$count;$i++)
            {
                if($usernowsmsg[$i]['id']==$msgid)
                {
                    array_splice($usernowsmsg,$i,1);                        //这里不能用unset
                    break;
                }
            }
            CacheManager::Set("UserMsgInfo".$user_id, $usernowsmsg,SESSION_PREFIX1);
            return false;
        }
        else 
            return true;
    }
}