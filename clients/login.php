<?php 
require_once("./../funcs/database.class.php");
require_once("./../funcs/functions.php"); 
require_once("./../funcs/constant.php"); 
require_once("./../funcs/tools.php"); 

if (isset($_POST['btnLogin']))
{
    //Create object database
    $db_obj = new DatabaseConnection();
    
    //Create log that someone trying to login
    //use original value of input value
    
    //store user input into variable
    $username = mysql_real_escape_string($_POST['i_username'], $db_obj->connection);
    $password = mysql_real_escape_string($_POST['i_password'], $db_obj->connection);
    
    //check login
    $login_error_msg="";
    login($username, $password, $db_obj, $login_error_msg);
}
if (isLoggedin()){
    header("location: index");
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
</head>

<body>
    <div id="panel-header">
    	<div class="app-title"></div>
    	<div class="logo"></div>
    </div><div class="clr"></div>
    
 	<div id="login-box">
    	<div class="separator"></div>
        <div class="login-key-container">        	
        	<div class="login-key">
                    <form action="<?php echo cur_page_name(false);?>" method="post">
                	<table border="0" width="100%">
                    	<tr>
                        	<td>Username</td>
                            <td align="right"><input type="text" id="i_username" name="i_username" /></td>
                        </tr>
                        <tr>
                        	<td>Password</td>
                            <td align="right"><input type="password" id="i_password" name="i_password" /></td>
                        </tr>
                        <tr>
                        	<td>&nbsp;</td>
                            <td align="right"><input type="submit" name="btnLogin" value="Login" /></td>
                        </tr>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <!--<tr><td colspan="2" align="right">Lupa password ?</td></tr>-->
                    </table>
                </form>                
            </div>
            <div class="login-error"><?php echo (isset($login_error_msg)?$login_error_msg:'');?></div>
        </div>
    </div><div class="clr"></div>
    <div class="copyright">
        <span style='font-style:italic;'>Best view with Google Chrome and Mozilla Firefox 4+</span><br />
        <p>Copyright &copy; PT. Bank Rakyat Indonesia, Tbk. 2012</p>
    </div>
</body>
</html>