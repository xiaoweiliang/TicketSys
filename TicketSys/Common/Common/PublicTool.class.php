<?php
/**
 * 工具类
 * Author 任雄伟
 * 时间 2016/6/22
 */
namespace Common\Common;
class PublicTool 
{
    private static $TipInfo=array(
        0=>"系统异常",
        1=>'注册成功',
        '-1'=>'注册失败',
        2=>'登录成功',
        '-2'=>'登录失败',
        3=>'用户名不能为空',
        4=>'用户密码不能为空',
        5=>'手机号码不能为空',
        6=>'手机号码话格式错误',
        7=>'已有该用户',
        8=>true
    );
    
    private static $userinfo=array(
        1=>true,
        'name'=>3,
        'key'=>4,
    );
    //检测是否有空的值
    public static function check_empty($data)
    {
        foreach ($data as $key=>$value)
        {
            if(empty($value))
            {
                break;
                return $key;
            }
        }
        return 1;
    }
    //获取对应的提示
    public static function getcheck_result($id)
    {
        return PublicTool::$TipInfo[$id];
    }
    
    //判断用户传来的数据
    public static function checkuserinfo($data)
    {
        $id=PublicTool::check_empty($data);
        return PublicTool::$userinfo[ $id ];
    }
    
    //字符串转int
    public static  function ParseInt($string)
    {
        settype($string, "integer");
        return $string;
    }
    
    /**
     * 返回空数组
     */
    public static function _Empty()
    {
        return array();
    }
    /**
     * 返回上一页
     */
    public static function GoBack()
    {
        echo "<script> window.location.href='$_SERVER[HTTP_REFERER]';</script>";
    }
    /**
     * 生成随机数
     */
    public static function MKRound()
    {
        srand((double)microtime()*1000000);//create a random number feed.
        $randnum=rand(1000,9999); 
        return $randnum;
    }
    /**
     * 生成订单
     */
    public static function MKOrderSn()
    {
        return ORDER_REFIX.TimeManager::GetTime();
    }
    /**
     * 检测方法是否存在
     */
    public  static function CheckFunction($class,$function)
    {
        if(!empty($class)&&!empty($function))
        {
            $allfunction=get_class_methods($class);
            $count=count($allfunction);
            $flag=0;
            
            for($i=0;$i<$count;$i++)
            {
                if($allfunction[$i]==$function)
                {
                    $flag=1;
                    break;
                }
            }
            if($flag==1)
                return true;
            else
                 return false;
        }
        else
            return false;
    }
}
?>