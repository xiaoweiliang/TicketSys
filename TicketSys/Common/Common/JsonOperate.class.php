<?php
/**
 * json操作类
 */
 namespace Common\Common;
 class JsonOperate
 {
     /*
      * jsonencode
      */
     public static function JsonEncode($ready_arr)
     {
         if(is_array($ready_arr))
            return json_encode($ready_arr);
         else
             return json_encode(array());
     }
     /*
      * jsondecode
      */
     public static function JsonDecode($ready_json)
     {
         $retarr=json_decode($ready_json);
         if(is_array($retarr))
             return $retarr;
         else 
             return array();
     }
 }
?>