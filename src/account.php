<?php
use MiniOrange\Helper\DB;
/*if(!class_exists("DB")){
    require_once dirname(__FILE__) . '/helper/DB.php';
  }
*/
  if(!isset($_SESSION) and isset($_REQUEST['option'])){
    session_id("connector");
    session_start();
  }
?>
