<?php
/**
 * 城市列表
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\CacheManager;
use Common\Common\PublicTool;
use Common\Common\Cookie;
class CityModel extends Model
{
    private $TableName="tp_city";
    /**
    
    * 由站点获取城市名
    
    */
    
    public function GetCityNameBySite($site_id)
    
    {
    
    	if(!empty($site_id))
    	{
    		$result=$this->table("tp_city C,tp_metroinfo M ,tp_siteinfo S")
    
    		->where("C.id=M.parent_id and M.id=S.parent_id and S.site_id=%d",$site_id)
    
    		->field("city_name")
    
    		->select();
    
    		if(!empty($result))
    
    			return $result[0]['city_name'];
    
    		else
    
    			return '';
    
    	}
    
    	else
    
    		return '';
    
    }
    /**
     * 获取城市列表
     */
    public function GetCityList()
    {
        $city=CacheManager::Get("city");
        if(empty($city))
        {
             $result=$this->select();
             if(!empty($result))
             {
                 CacheManager::Set("city", $result);
                 return $result;
             }
             else 
                 return PublicTool::_Empty();
        }
        else
            return $city;
    }
    /**
     * 获取城市名
     */
    public function GetCityName($cityid)
    {
        if(isset($cityid))
        {
            $city=CacheManager::Get("city".$cityid);
            if(empty($city))
            {
                $result=$this->where("id=%d",$cityid)->field("city_name")->select();
                if(!empty($result))
                {
                    CacheManager::Set("city".$cityid, $result[0]);
                    return $result[0]['city_name'];
                }
                else
                    return PublicTool::_Empty();
            }
            else
                return $city['city_name'];
        }
    }
    /**
     * 获取总数据
     */
    public function GetCount()
    {
        $count=Cookie::GetDataCookie($this->TableName);
        if($count>0)
        {
            $result=$this->count();
            var_dump($result);
            Cookie::SetDataCookie($this->TableName,$result);
            $count=$result;
        }
        return $count;
    }
}