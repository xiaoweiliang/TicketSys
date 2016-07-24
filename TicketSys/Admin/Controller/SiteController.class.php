<?php
/**
 * 地铁站点管理
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\SiteModel;;
use Common\Common\Cookie;
use Common\Common\PublicTool;
use Think\Model;
class SiteController extends Controller
{
    /**
     * 系统默认入口
     */
    public function index()
    {
        $this->display("index/index");
    }

    /**
     * 添加站点
     */
    public function AddSite()
    {
        if(SessionManager::GetUserId()>0)
        {
            $name=@$_POST['name'];
            $parent_id=@$_POST['parent_id'];
            if(!empty($name)&&$parent_id>0&&is_numeric($parent_id))
            {
                $is_user=$_POST['is_run'];
                if(isset($is_user)&&is_numeric($is_user))
                {
                    $site=new SiteModel("tp_siteinfo");
                    $site->AddSite($_POST);
                   
                    //清空对应的缓存
                    //"metro".$cityid.$page
                    PublicTool::GoBack();
                }
            }
        }
    }
    /**
     * 删除站点
     */
    public function DelSite()
    {
        if(SessionManager::GetUserId()>0)
        {
            $mtid=$_GET['mtid'];
            $siteid=$_GET['siteid'];
            if(is_numeric($mtid)&&$mtid>0&&is_numeric($siteid)&&$siteid>0)
            {
                $site=new SiteModel("tp_siteinfo");
                $site->DelSite($siteid,$mtid);
                PublicTool::GoBack();
            }
        }
    }
}