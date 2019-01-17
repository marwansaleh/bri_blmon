<?php
date_default_timezone_set('Asia/Jakarta');

//URL BASE
define("FOLDER_PREFIX","bri_csr/");
define("BASE_URL","/projects/htdocs/".FOLDER_PREFIX);
define("ABSTRACT_BASE","http://".$_SERVER['SERVER_NAME'].BASE_URL);
define("CUSTOM_URL", "customs/");
define("PROFILE_URL",CUSTOM_URL."profile/photo/");

define("APP_BASE_PATH", dirname(__FILE__) .'/../');
/*
 * Security phrase for forcelogin
 */
define("SEC_FORCE_LOGIN","amazzura.biz@gmail.com");
/*
 * ACTION UPDATE
 */
define("ACT_CREATE","create");
define("ACT_EDIT","edit");
define("ACT_DELETE","delete");

/*
 * Jenis saldo
 */
define("SALDO_ALOCATION",0);
define("SALDO_REAL", 1);
?>
