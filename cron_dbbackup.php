#!/usr/bin/php
<?php
//require __DIR__.'/funcs/database.class.php';
require __DIR__.'/funcs/constant.php';


$db_obj = new DatabaseConnection();

$backup_folder = APP_BASE_PATH."db_backup/";
//check if folder exists, if not, create one
if (!file_exists($backup_folder)){
    mkdir($backup_folder, 0775);
}

$filename = time().'.sql';
$result = exec("mysqldump -h ".DB_HOST." -u ".DB_USER." -p".DB_PWD." ".DB_NAME." > $backup_folder$filename");