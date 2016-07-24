<?php
/**
 * 城市数据控制器
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\CityModel;
use Admin\Model\MetroModel;
use Common\Common\MyPage;
use Common\Common\SessionManager;
use Common\Common\PublicTool;
use Common\Common\Cookie;
class CityController extends Controller
{
    private $Map=array(
        'GetCityInfo'=>'获取城市地铁信息',
        'ChangeState'=>'修改线路的运行状态'
    );
    /**
     * 方法说明表
    */
    public function GetInstation($function)
    {
        return $this->Map[$function];
    }
    /**
     * 系统默认入口
     */
    public function index()
    {
        $this->display("index/index");
    }
    /**
     * 获取城市的地铁信息
     */
    //http://blog.csdn.net/qq1355541448/article/details/24720639
    public function GetCityInfo()
    {
        if(SessionManager::GetUserId()>0)
        {
            $city=new CityModel("tp_city");
            $city_name=$city->GetCityName($_REQUEST['cityid']);
   
            $metro=new MetroModel("tp_metroinfo");
            $this->assign('page',MyPage::GetPage($metro->GetModel()));
            $this->assign("metro_list",$metro->GetMetroInfo($_REQUEST['cityid']));
            $this->assign("city",$city_name);
            $this->assign("cityid",$_REQUEST['cityid']);
            $this->assign("manager","地铁线路管理");
            $this->display("City/CityInfo");
        }
    }
    /**
     * 修改线路的状态
     */
    public function ChangeState()
    {
        $mtid=$_GET['mtid'];
        if(is_numeric($mtid)&&$mtid>0)
        {
            $nowstate=$_GET['state'];
            $nowstate=$nowstate==1?0:1;
            $metro=new MetroModel("tp_metroinfo");
            $metro->ChangeState($mtid, $nowstate);
           
            //清空对应的缓存
            //"metro".$cityid.$page
            PublicTool::GoBack();
        }
    }
}