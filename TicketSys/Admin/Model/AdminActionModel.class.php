<?php
/**
 * 管理员操作类型
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\PublicTool;
use Common\Common\CacheManager;
class AdminActionModel extends Model
{
    /**
     * 获取管理员操作类型
     */
    public function GetAdminTopAction()
    {
        $action=CacheManager::Get("admin_top_action");
        if(empty($action))
        {
            $result=$this->where("parent_id=%d",.0)->select();
            if(!empty($result))
            {
                CacheManager::Set("admin_top_action", $result);
                return $result;
            }            
            else
                PublicTool::_Empty();
        }
        else 
        {
            return $action;
        }   
    }
    public function GetAdminChildAction()
    {
        $action=CacheManager::Get("admin_child_action");
        if(empty($action))
        {  
            $result=$this->where("parent_id<>%d",.0)->select();
            if(!empty($result))
            {
                CacheManager::Set("admin_child_action", $result);
                return $result;
            }
            else
                PublicTool::_Empty();
        }
        else
        {
            return $action;
        }
    }
}