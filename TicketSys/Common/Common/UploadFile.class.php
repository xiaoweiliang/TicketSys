<?php
/**
 * 上传图片到服务器
 */
namespace  Common\Common;
use Think\Upload\Driver\Local;
use Common\Common\SessionManager;
use Common\Common\TimeManager;
class UploadFile extends Local
{
    /**
     * 获取文件类型
     */
	
    public static  function GetFileType($filename)
    {
        $arr=explode(".", $filename);
        if(!empty($arr))
        {
            $count=count($arr);
            $type=$arr[$count-1];
            if(!empty($type))
                return $type;
            else
                return null;
        }
        else
            return null;
    }
    /**
     * 验证文件是否合法 
     */
    public static function CheckFile($type)
    {
 
        if($type=="image/jpeg"||$type=="image/gif"||$type=="image/png")
            return true;
        else
            return false;
    }
    /**
     * 生成文件保存名
     */
    public static function MKPathName($user_id)
    {
        if($user_id>0&&is_numeric($user_id))
        {
            return $user_id%4;
        }
        else
            return null;
    }
    /**
     * 上传文件到服务器
     */
    public  function UploadFile($FILE,$user_id='')
    {
    	if(empty($user_id))
    	{
    		$user_id=SessionManager::GetUserId();
    	}
        if($user_id>0)
        {
            if(is_array($FILE))
            {
            	
                if($this->checkRootPath(UPLOAD_IMAGE_ROOT_PATH))               //根目录存在
                {
                
                    //验证文件是否合法
                   // if(UploadFile::CheckFile($FILE['type']))
                    {
                        $file_type=UploadFile::GetFileType($FILE["name"]);
                        if(!empty($file_type))
                        {
                            $pathname=UploadFile::MKPathName($user_id);
                            if(isset($pathname)&&$this->checkSavePath($pathname))
                            {
                                $FILE['savepath']=$pathname."/";
                                $FILE['savename']=$user_id."_".TimeManager::GetMirTime().".".$file_type;
                                //组合文件保存路径
                                if($this->save($FILE))
                                    return $pathname."/".$FILE['savename'];                    //返回文件名
                                else 
                                    return false;
                            }
                            else 
                                return false;
                        }
                        else 
                            return false;
                    }
                   // else 
                    //    return false;
                }
                else 
                    return false;
            }
            else 
                return false;
        }
        else 
            return false;
    }
}