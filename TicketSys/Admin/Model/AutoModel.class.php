<?php
/**
 * 用户周票月票信息管理
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\CacheManager;
use Common\Common\Cookie;
use Common\Common\MyPage;
use Common\Common\TimeManager;
use Common\Common\PublicTool;
class AutoModel  extends Model
{
    private $_Model="AutoModel";
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
    public function GetAutoCount()
    {
        $userid=$userid=Cookie::GetTHUserId();
        $type=Cookie::GetTFType();;
        if(!empty($userid)&&isset($type)&&$type!=-1)
            $result=$this->where("user_id=%d and auto_type=%d",$userid,$type)->field("id")->count();
        else if(!empty($userid))
            $result=$this->where("user_id=%d",$userid)->field("id")->count();
        else if(isset($type)&&$type!=-1)
            $result=$this->where("auto_type=%d",$type)->field("id")->count();
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
       $result=$this->GetAutoCount();
        return $result;
    }
    /**
     * 获取所有用户的自动票记录
     * -1I表示所有自动票记录
     */
    public function GetAllUserAuto($auto_type=-1)
    {
        if($auto_type==-1)
        {
            $AllUserAuto=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->field(array("id","user_id","auto_type","days","time","work_time"))
            ->order("time desc")
            ->select();
        }
        else
        {
            $AllUserAuto=$this->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)
            ->where("auto_type=%d",$auto_type)
            ->field(array("id","user_id","auto_type","days","time","work_time"))
            ->order("time desc")
            ->select();
        }
        if(!empty($AllUserAuto))
        {
            $count=count($AllUserAuto);
            for($i=0;$i<$count;$i++)
            {
                $AllUserAuto[$i]['time']=TimeManager::FormatTime($AllUserAuto[$i]['time']);
                if($AllUserAuto[$i]['work_time']!=0)
                    $AllUserAuto[$i]['work_time']=TimeManager::FormatTime($AllUserAuto[$i]['work_time']);
            }
        }
        return $AllUserAuto;
    }
    /**
     * 获取单个用户的所有消费
     */
    public function GetUserAuto($user_id,$auto_type=-1)
    {
        if(!empty($user_id))
        {
            $UserAutoInfo=CacheManager::Get("UserAutoInfo".$user_id,SESSION_PREFIX1);
            if(empty($UserAutoInfo))
            {
                $UserAuto=$this->where("user_id=%d",$user_id)->field(array("id","auto_type","days","time","work_time"))
                ->order("time desc")
                ->select();
                if(!empty($UserAuto))
                {
                    $count=count($UserAuto);
                    for($i=0;$i<$count;$i++)
                    {
                        $UserAuto[$i]['time']=TimeManager::FormatTime($UserAuto[$i]['time']);
                        $UserAuto[$i]['user_id']=$user_id;
                        if($UserAuto[$i]['work_time']!=0)
                            $UserAuto[$i]['work_time']=TimeManager::FormatTime($UserAuto[$i]['work_time']);
                    }
                    CacheManager::Set("UserAutoInfo".$user_id, $UserAuto,SESSION_PREFIX1);
                    return $this->FilterOrder($UserAuto,$auto_type);
                }
                else
                    return PublicTool::_Empty();
            }
            else
            {
                return $this->FilterOrder($UserAutoInfo,$auto_type);
            }
        }
    }
    /**
     * 提取用户需要的消费
     */
    public function FilterOrder($UserAuto,$type)
    {
        $resl_order=array();
        $offset=MyPage::GetSqlOffset($this->_Model);
        $endoffset=$offset+PAGE_COUNT;
        if($type==-1)
        {
            $count=count($UserAuto);
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
                $resl_order[]=$UserAuto[$i];
            return $resl_order;
        }
        else
        {
            $realorder=array();
            $count=count($UserAuto);
            for($i=0;$i<$count;$i++)
            {
                if($UserAuto[$i]['auto_type']==$type)
                    $realorder[]=$UserAuto[$i];
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
     * 检测用户是否已经该类型的票
     */
    public function CheckType($user_id,$type)
    {
        if(!empty($user_id)&&isset($type))
        {
            $result=$this->where("user_id=%d and auto_type=%d",$user_id,$type)->field("id")->select();
            if(!empty($result))
                return false;
            else 
                return true;
        }
        else 
            return false;
    }
    /**
     * 添加自动票记录
     */
    public function AddUserAuto($user_id,$type,$days)
    {
        if(!empty($user_id)&&is_numeric($user_id)&&isset($type))
        {
            if($this->CheckType($user_id, $type))
            {
                if($type==0)
                    $days=7;
                else if($type==1)
                    $days=30;
                if($days>0)
                {
                    $data['user_id']=$user_id;
                    $data['auto_type']=$type;
                    $data['days']=$days;
                    $data['time']=TimeManager::GetTime();
                    $data['work_time']=0;
                    $newid=$this->add($data);
                    if($newid>0)         //添加成功
                    {
                        $data['id']=$newid;
                        $data['time']=TimeManager::FormatTime($data['time']);
                        $UserAutoInfo=CacheManager::Get("UserAutoInfo".$user_id,SESSION_PREFIX1);
                        if(!empty($UserAutoInfo))
                        {
                            $UserAutoInfo[]=$data;
                            CacheManager::Set("UserAutoInfo".$user_id, $UserAutoInfo,SESSION_PREFIX1);
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
        else
            return false;
    }
    /**
     * 删除自动票记录
     */
    public function DelUserAuto($user_id,$autoid)
    {
        if(!empty($user_id)&&!empty($autoid))
        {
            $this->where("id=%d",$autoid)->delete();
             $UserAutoInfo=CacheManager::Get("UserAutoInfo".$user_id,SESSION_PREFIX1);
            $count=count($UserAutoInfo);
            for($i=0;$i<$count;$i++)
            {
                if($UserAutoInfo[$i]['id']==$autoid)
                {
                    array_splice($UserAutoInfo,$i,1);                        //这里不能用unset
                    break;
                }
            }
            CacheManager::Set("UserAutoInfo".$user_id, $UserAutoInfo,SESSION_PREFIX1);
        }
    }
}