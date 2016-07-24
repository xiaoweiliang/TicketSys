<?php
/**
 * 地铁站点管理
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\PublicTool;
use Common\Common\CacheManager;
use Common\Common\Cookie;
use Common\Common\MyPage;
class SiteModel extends Model
{
    private $TableName="tp_siteinfo";
    private $_Model="SiteModel";
    /**
     * 获取table名
     */
    public function GetTable()
    {
        return $this->TableName;
    }
    /**
     * 获取model
     */
    public function GetModel()
    {
        return $this->_Model;
    }
    /**
     * 获取站点名
     */
    public function GetSiteName($siteid)
    {
        if(!empty($siteid))
        {
            $result=$this->where("id=%d",$siteid)->field("name")->select();
            if(!empty($result))
            {
                return $result[0]['name'];
            }
            else
                return '';
        }
    }
    /**
     * 获取线路的所有站点信息
     */
    public function GetMetroAllSite($MetroId)
    {
        if(!empty($MetroId))
        {
            $page=$_REQUEST['p'];
            if(empty($page)||!is_numeric($page))
                $page=1;
           /* if(Cookie::GetCookie($this->_Model)==1)               //表示要清空缓存
              {
                  $page1=ceil($this->GetCount()/PAGE_COUNT);
                   
                  CacheManager::ClearCache("AllSite".$MetroId.$page1);
              }
            $AllSite=CacheManager::Get("AllSite".$MetroId.$page,$this->_Model);
    
            if(empty($AllSite))*/
            {
                $result=$this->where("parent_id=%d",$MetroId)->field(array("id,name,is_run,detaile_info"))->order("id asc")->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->select();
                if(!empty($result))
                {
                   // CacheManager::Set("AllSite".$MetroId.$page, $result,$this->_Model);
 
                    return $result;
                }               
                else 
                    return PublicTool::_Empty();
            }
          /*  else
                return $AllSite;*/
        }
        else
            return PublicTool::_Empty();
    }
    /**
     * 获取总数据
     */
    public function GetCount()
    {
        $_REQUEST['cityid']=$_REQUEST['mtid'];
        $count=Cookie::GetDataCookie($this->TableName);
        if($count==0)
        {
            if(!empty($_REQUEST['mtid'])&&is_numeric($_REQUEST['mtid']))
                $result=$this->where("parent_id=%d",$_REQUEST['mtid'])->count();
            else
                $result=$this->count();
            Cookie::SetDataCookie($this->TableName,$result);
            $count=$result;
        }
        $_REQUEST['cityid']='';
        return $count;
    }
    /**
     * 修改地铁线路的运行状态
     */
    public function ChangeState($id,$state)
    {
        $this-> where('id=%d',$id)->setField('is_run',$state);
        //Cookie::SetCookie($this->GetModel(), 2);                   //条件
    }
    /**
     * 添加站点
     */
    public function AddSite($siteinfo)
    {
        if(!empty($siteinfo))
        {
            $this->add($siteinfo);
          //  Cookie::SetCookie($this->GetModel(), 1);                   //条件
            $_REQUEST['cityid']=$siteinfo['parent_id'];
            Cookie::ClearDataCookie($this->GetTable());
            $_REQUEST['cityid']='';
        }
    }
    /**
     * 删除路线
     */
    public function DelSite($siteid,$mtid)
    {
        $site=new SiteModel("tp_siteinfo");
        $site->where("id=%d",$siteid)->delete();
        $_REQUEST['cityid']=$mtid;
        Cookie::ClearDataCookie($this->TableName);
    }
    
    /*
     * 梁晓伟
     */
    
    /**
     * 通过名字来获取城市id
     */
    public function GetSiteid($sitename)
    {
    	if(!empty($sitename))
    	{
    		$result=$this->where("name='%s'",$sitename)->field("id")->select();
    		if(!empty($result))
    		{
    			return $result[0]['id'];
    		}
    		else
    			return '';
    	}
    }
    /**
     * 通过站点id获取城市名
     */
    public function GetName($siteid)
    {
    	if(!empty($siteid))
    	{
    		$result=$this->where("site_id='%s'",$siteid)->field("name")->select();
    		if(!empty($result))
    		{
    			return $result[0]['name'];
    		}
    		else
    			return '';
    	}
    }
    /**
     * 通过id来获取siteid
     */
    public function GetSiteidd($id)
    {
    	if(!empty($id))
    	{
    		$result=$this->where("id=%s",$id)->field("site_id")->select();
    		if(!empty($result))
    		{
    			return $result[0]['site_id'];
    		}
    		else
    			return '';
    	}
    }
}