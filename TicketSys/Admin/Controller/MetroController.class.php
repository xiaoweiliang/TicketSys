<?php
/**
 * 城市线路管理
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\PublicTool;
use Admin\Model\SiteModel;
use Admin\Model\MetroModel;
use Common\Common\MyPage;
use Common\Common\SessionManager;
class MetroController extends Controller
{
    /**
     * 系统默认入口
     */
    public function index()
    {
        $this->display("index/index");
    }
    /**
     * 获取线路的站点信息
     */
    public function GetMetroInfo()
    {
        if(SessionManager::GetUserId()>0)
        {
            if(PublicTool::checkuserinfo($_GET))
            {
                $Site=new SiteModel("tp_siteinfo");
                $this->assign('page',MyPage::GetPage($Site->GetModel()));
                $this->assign("site_list",$Site->GetMetroAllSite($_GET['mtid']));
                $this->assign("city",$_GET['city']);
                $this->assign("cityid",$_GET['cityid']);
                $this->assign("mtid",$_GET['mtid']);
                $this->assign("metrono",$_GET['metrono']."号线");
                $this->assign("manager","站点管理");
                $this->display("Metro/AllSite");
            }
            else
            {
                $this->success('请求失败，数据不个法', 'javascript:history.back(-1);');
            }
        }
    }
    /**
     * 修改线路的状态
     */
    public function ChangeState()
    {
        if(SessionManager::GetUserId()>0)
        {
            $siteid=$_GET['siteid'];
            if(is_numeric($siteid)&&$siteid>0)
            {
                $nowstate=$_GET['state'];
                $nowstate=$nowstate==1?0:1;
                $Site=new SiteModel("tp_siteinfo");
                $Site->ChangeState($siteid, $nowstate);
               
                //清空对应的缓存
                //"metro".$cityid.$page
                PublicTool::GoBack();
            }
        }
    }
    /**
     * 添加线路
     */
    public function AddMetro()
    {
        if(SessionManager::GetUserId()>0)
        {
            $metroid=@$_POST['metro_id'];
            $parent_id=@$_POST['parent_id'];
            if($metroid>0&&is_numeric($metroid)&&$parent_id>0&&is_numeric($parent_id))
            {
                $is_user=$_POST['state'];
                if(isset($is_user)&&is_numeric($is_user))
                {
                    $metro=new MetroModel("tp_metroinfo");
                    $metro->AddMetro($_POST);
                    //清空对应的缓存
                    //"metro".$cityid.$page
                    PublicTool::GoBack();
                }
            }
        }
    }
   /**
    * 删除路线
    */
    public function DelMetro()
    {
        if(SessionManager::GetUserId()>0)
        {
            $mtid=$_GET['mtid'];
            $cityid=$_GET['cityid'];
            if(is_numeric($mtid)&&$mtid>0&&is_numeric($cityid)&&$cityid>0)
            {
                $metro=new MetroModel("tp_metroinfo");
                $metro->DelMetro($mtid,$cityid);
                PublicTool::GoBack();
            }
        }
    }
}