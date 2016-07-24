<?php 
define("THINKPATH", "./ThinkPHP");
define("APP_NAME","Mobile");
define("APP_PATH","TicketSys/");
define("APP_DEBUG",true);
define('BIND_MODULE','Mobile');
define('SESSION_PREFIX','user');         //普通用户session前缀
define('SESSION_PREFIX1','user');         //管理员session
define('SESSION_PREFIX2','admin');         //普通用户session前缀
define("KEY_LENGTH",6);
define('PAGE_COUNT',10);
define("UPLOAD_IMAGE_ROOT_PATH",APP_PATH."data/upload/image/user/");
define("ORDER_REFIX", "TS");


require THINKPATH."/ThinkPHP.php";
?>