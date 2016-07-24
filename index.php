<?php 
define("THINKPATH", "./ThinkPHP");
define("APP_NAME","Admin");
define("APP_PATH","TicketSys/");
define("APP_DEBUG",true);
define('BIND_MODULE','Admin');
define('SESSION_PREFIX','admin');         //管理员session 
define('SESSION_PREFIX1','user');         //管理员session
define('COOKIE_PREFIX','_count');
define('PAGE_COUNT',15);
define("CACHE_PATH",'TicketSys/Runtime/Temp/');
define("KEY_LENGTH",6);
define('SESSION_PREFIX2','admin');         //普通用户session前缀
define("ORDER_REFIX", "TS");
//ini_set("session.save_handler", "user");//����PHP��SESSION���û�����
require THINKPATH."/ThinkPHP.php";
?>  