<?php
require_once("./../funcs/database.class.php");
require_once("./../funcs/functions.php"); 
require_once("./../funcs/constant.php"); 
require_once("./../funcs/tools.php"); 

$db_obj = new DatabaseConnection();

if (isset($_GET['logid'])&&isset($_GET['security']))
{
    //securty phrase must be same
    if ($_GET['security']==md5(SEC_FORCE_LOGIN))
    {        
        $login_id = mysql_escape_string($_GET['logid']);        
        
        //get the user detail from database
        $sql = "SELECT u.id, u.user_name, u.full_name, u.position, u.access, t.type, u.login_status, 
                u.last_login, u.last_ip,u.created_on, u.avatar
                FROM users u, user_types t
                WHERE (u.access=t.id)AND(u.id=$login_id)";
        
        $login = $db_obj->execSQL($sql);
        if ($login)
        {
            //create new session for loggedin user
            login_session_update($login[0],$db_obj);
            
            header("location: index");
            exit;
        }
    }else{
        logs("visitor","Use forcelogin link but un-match security code",$db_obj);
    }
}else{
    logs("visitor","Use forcelogin link but nothing query string in the address",$db_obj);
}
?>
