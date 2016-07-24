<?php
namespace Common\Common;
use Common\Conf\Conf;
class FileOperateTool
{
    /*
     * 删除缓存文件
     */
    public static function DelCacheFile($filename,$module,$id="",$SUFFIX="")
    {
        //用到的时候在require对应的php
        $SUFFIX=empty($SUFFIX)?HTML_FILE_SUFFIX:$SUFFIX;
        $FilePath=HTML_PATH.'/'.$module.'_'.$filename.'_'.$id.HTML_FILE_SUFFIX;                   //静态缓存目录
        unlink($FilePath);              //删除缓存文件
    }
    /*
     * 清空缓存文件
     */
    public static function ClearCacheFile()
    {
        $file_path=APP_PATH."Runtime/Temp/".SESSION_PREFIX."/";
        if(file_exists($file_path)&&is_dir($file_path))       //如果文件存在并且是文件夹
        {
            $file=opendir($file_path);             //打开文件夹            获取文件夹句柄
            while($child_file=readdir($file))            //读取文件夹
            {
                if($child_file!='.'&&$child_file!='..')         //因为文件夹默认有.或者..等文件夹
                {
                    if(is_dir($file_path."/".$child_file))           //获取的$child_file是子文件名 不包括路径
                    {
                        FileOperateTool::ClearCacheFile($file_path."/".$child_file);           //如果是文件夹的话递归调用	
                    }
                    if(is_file($file_path."/".$child_file))
                     {
                         if($child_file!="index.php")
                         {
                            // echo $file_path."/".$child_file."<br>";
                             unlink($file_path."/".$child_file);
                         }              
                     }
                }
            }
            closedir($file);           //最后关闭文件夹
        }
    }
}

?>