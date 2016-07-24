<?php
/**
 * 该模块用来向客户端返回数据的公共模块
 * Author 任雄伟
 * 时间 ：2016/6/22
 */
namespace Common\Common;
use Common\Common\PublicTool;
use Common\Common\JsonOperate;
use Common\Conf\Conf;
class ToolBack
{
    public static function echoback($is_ok,$elseinfo=array())
    {
        if($is_ok==true&&!is_numeric($is_ok)||$is_ok==1)
        {
            $is_ok=1;
        }     
        else
        {
            //获取错误消息 
            $elseinfo['detaile']=PublicTool::getcheck_result($is_ok);
            $is_ok=0;
        }     
        $echodata=array();
        $echodata['is_ok']=$is_ok;
        $echodata['else_info']=$elseinfo;
        
        echo JsonOperate::JsonEncode($echodata);
    }
    /*
     * 返回到客户端
     */
    public static function _ECHO($content)
    {
        if(Conf::$is_echo)
        {
            if(!empty($content))
            {
                echo $content."<br/>";
            }
            else
                echo "null <br/>";
        }
    }
}

?>