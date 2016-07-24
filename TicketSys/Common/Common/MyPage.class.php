<?php
/**
 * 分页公共
 */
namespace Common\Common;
use Think\Crypt\Driver\Think;
use Think\Page;
class MyPage
{
    /**
     * 分页处理
     */
    public static function GetPage($ModelName)
    {
        $config=require APP_PATH."Admin/Model/Conf/config.php";
        $position='Admin\Model\\'.$ModelName;
        $model=new $position($config[$ModelName]);
        $p = new Page($model->GetCount(), PAGE_COUNT);
        $p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next', '下一页');
        $p->setConfig('last', '末页');
        $p->setConfig('first', '首页');
        $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $p->lastSuffix = false;//最后一页不显示为总页数
        $dd=$p->show();
        return $dd;
    }
    /**
     * 计算当前的查找其实位置
     */
    public static function GetSqlOffset($ModelName)
    {
        $config=require APP_PATH."Admin/Model/Conf/config.php";
        $position='Admin\Model\\'.$ModelName;
        $model=new $position($config[$ModelName]);
        $page=$_REQUEST['p'];
       
        if(empty($page)||$page<0||!is_numeric($page))
            $page=1;
        $page--;
        
        if($page*PAGE_COUNT>=$model->GetCount()&&$page!=0)
            return ($page-1)*PAGE_COUNT;
        else
            return $page*PAGE_COUNT;
    }
}