<?php
/*
 * 缓存管理
 */
 namespace Common\Common;
 use Common\Common\SessionManager;
 use Common\Common\KeyTool;
 class CacheManager
 {
     /*
      * 设置缓存   参数1  缓存标识符   参数2    缓存内容   参数3   判断是否要添加方法名
      */
     public static function Set($index,$value,$model=null)                    
     {
         if(!empty($value))
         {
              $index=CacheManager::MKName($index,$model);
               S($index,$value,null,$model);               //写缓存
         }
     }
     /*
      * 获取缓存             参数1 缓存下表           参数2   判断是否要添加方法名
      */
     public static function Get($index,$model='')
     {
         if(!empty($index))
         {
              $index=CacheManager::MKName($index,$model);
              /*if(!empty($model))
              {
                  if(Cookie::GetCookie($model)==2)               //表示要清空缓存
                  {
                      CacheManager::ClearCache($index);
                  }
              }*/
              return S($index,'',null,$model);
         }
         return "";
     }
     /**
      * 生成缓存文件名
      */
     public static function MKName($index,$model=null)
     {
         $index.=KeyTool::base_encode(SessionManager::GetUserId());
         if(!empty($model))
             $index.=$model;
         else 
            $index.=SESSION_PREFIX;
         return $index;
     }
     /*
      * 清空缓存
      */
     public static function ClearCache($key,$model=null)
     {
         $key=CacheManager::MKName($key,$model);
         RM($key,$model);
     }
 }
?>