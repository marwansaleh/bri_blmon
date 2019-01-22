<?php
date_default_timezone_set('Asia/Jakarta');

//URL BASE

define("FOLDER_PREFIX","");

define("BASE_URL","/".FOLDER_PREFIX);

define("ABSTRACT_BASE","http://172.17.0.3".BASE_URL);

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
