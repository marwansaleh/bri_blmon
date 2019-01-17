<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

if (isset($_GET['username']))
{
    $db_obj = new DatabaseConnection();
    $random_number = mt_rand(1000,9999);
    $new_password = $random_number.get_random_string();
    //$new_password = password_rehash($new_password);
    
    $username = $_GET["username"];
    $sql = "UPDATE users SET 
            password='".password_rehash($new_password)."',
            login_status=0
            WHERE (STRCMP(user_name, '$username')=0)";
    $result = $db_obj->query($sql);
    if ($result&&$db_obj->getNumRecord()>0)
    {
        
        echo "<p>Reset account dengan username <strong>$username</strong> berhasil dengan password <strong>$new_password</strong></p>";
    }else{
        echo "<p>Gagal mereset account dengan username <strong>$username</strong>".$db_obj->getLastError()."</p>";
    }
}else{
    exit("Parameter not satisfied");
}
?>
