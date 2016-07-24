<?php
/**
 * 地铁线路数据库操作
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\CacheManager;
use Common\Common\PublicTool;
use Common\Common\Cookie;
use Common\Common\MyPage;
class MetroModel extends Model
{
    private $TableName="tp_metroinfo";
    private $_Model="MetroModel";
    /**
     * 获取model
     */
    public function GetModel()
    {
        return $this->_Model;
    }
    /**
     * 获取城市地铁信息
     */
    public function GetMetroInfo($cityid)
    {
        if(isset($cityid)&&is_numeric($cityid))
        {
            $page=$_REQUEST['p'];
            if(empty($page)||!is_numeric($page))
                $page=1;
            /*if(Cookie::GetCookie($this->_Model)==1)               //表示要清空缓存
            {
                $page1=ceil($this->GetCount()/PAGE_COUNT);
                CacheManager::ClearCache("metro".$cityid.$page1);
            }
            else if(Cookie::GetCookie($this->_Model)==3)                    //清楚后面的所有也得缓存
            {
                $page1=ceil($this->GetCount()/PAGE_COUNT);
                for($i=$page;$i<=$page1+1;$i++)
                {
                    CacheManager::ClearCache("metro".$cityid.$i);
                }
            }
            $city=CacheManager::Get("metro".$cityid.$page,$this->_Model);
            if(empty($city))*/
            {
                if($cityid!=0)
                     $result=$this->where("parent_id=%d",$cityid)->field(array("id","metro_id","state"))->order("metro_id asc")->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->select();               //获取id   线路号   运行状态
               else 
                    $result=$this->field(array("id","metro_id","state"))->order("metro_id asc")->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->select();               //获取id   线路号   运行状态
                if(!empty($result))
                {
                    //CacheManager::Set("metro".$cityid.$page, $result,$this->_Model);
                    return $result;
                }
                else
                    return PublicTool::_Empty();
            }
           /* else
                return $city;*/
        }
        else
            return PublicTool::_Empty();
    }
    /**
     * 获取总数据
     */
    public function GetCount()
    {
        $count=Cookie::GetDataCookie($this->TableName);
        if($count==0)
        {
            if(!empty($_REQUEST['cityid'])&&is_numeric($_REQUEST['cityid']))
                 $result=$this->where("parent_id=%d",$_REQUEST['cityid'])->count();
            else 
                $result=$this->count();
            Cookie::SetDataCookie($this->TableName,$result);
            $count=$result;
        }
        return $count;
    }
    /**
     * 修改地铁线路的运行状态
     */
    public function ChangeState($id,$state)
    {
        $this-> where('id=%d',$id)->setField('state',$state);
        //Cookie::SetCookie($this->GetModel(), 2);                   //条件
    }
    /**
     * 添加路线
     */
    public function AddMetro($metroinfo)
    {
        if(!empty($metroinfo))
        {
            if($this->CheckExist($metroinfo['parent_id'],$metroinfo['metro_id']))
            {
                $this->add($metroinfo);
               // Cookie::SetCookie($this->GetModel(), 1);                   //条件
                $_REQUEST['cityid']=$metroinfo['parent_id'];
                Cookie::ClearDataCookie($this->TableName);
                $_REQUEST['cityid']='';
            }
        }
    }
    /**
     * 检测路线是否存在
     */
    public function CheckExist($parent_id,$metroid)
    {
        $result=$this->where("parent_id=%d and metro_id=%d",$parent_id,$metroid)->field("id")->select();
        if(!empty($result))
            return false;
        else
            return true;
    }
    /**
     * 删除路线
     */
    public function DelMetro($mtid,$cityid)
    {
        $site=new SiteModel("tp_siteinfo");
        $site->where("parent_id=%d",$mtid)->delete();
        $this->where("id=%d and parent_id=%d",$mtid,$cityid)->delete();
        //Cookie::SetCookie($this->GetModel(), 3);                   //条件
        $_REQUEST['cityid']=$cityid;
        Cookie::ClearDataCookie($this->TableName);
    }
}