<?php
require_once("database.class.php");
require_once("tools.php");
require_once("constant.php");

session_save_path(sys_get_temp_dir());
session_start();

load_sysvars();

function page_header($page_title="")
{
    $s="";
    if ($page_title=="" &&  get_sysvar_value("APP_TITLE"))
        $page_title = get_sysvar_value ("APP_TITLE");
    $s.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
    $s.="<base href=\"". BASE_URL."\" />";
    $s.="<link rel=\"shortcut icon\" href=\"favicon.gif\" />";
    $s.="<title>".$page_title."</title>";
    $s.= get_user_style_files();
    $s.="<script language=\"javascript\" type=\"text/javascript\" src=\"customs/js/jquery-1.12.4.min.js\"></script>";
    $s.="<script language=\"javascript\" type=\"text/javascript\" src=\"customs/js/jquery-mask/jquery.mask.min.js\"></script>";
    $s.="<script language=\"javascript\" type=\"text/javascript\" src=\"customs/js/jquery-ui-1.11.4/jquery-ui.min.js\"></script>";
    
    $s.="<script language=\"javascript\" type=\"text/javascript\" src=\"customs/js/main.js\"></script>";
    
    return $s;
}
function document_header($access=NULL)
{
    $s="";
    $s.="<div id=\"panel-mainmenu\">";
        $s.="<div class=\"app-title\"></div>";
        $s.="<ul id=\"mainmenu\">";
            $s.="<li><a href=\".\">DASHBOARD</a></li>";
            $s.="<li><a href=\"rkap\" title='Rencana Anggaran'>RKAP</a></li>";
            $s.="<li><a href=\"programs\" title='Daftar program bantuan'>PROGRAMS</a></li>";
            $s.="<li><a href=\"areas\" title='Daftar program bantuan per target area'>TARGET AREA</a></li>";
            $s.="<li><a href=\"news\" title='Berbagi info dan berita'>NEWS &amp; INFO</a></li>";
            
            //start from the last
            //User active            
            $s.="<li class=\"right head-link\">";
                $s.= get_user_info("USERNAME");
                $s.="<div class=\"arrow-bottom\"></div>";
                $s.="<div class=\"submenu\">";
                    $s.="<ul class=\"submenu\">";
                        $s.="<li><a href=\"profile\">Edit Profil</a></li>";
                        if ($access && userHasAccess($access, "BUDGET_CREATE"))
                            $s.="<li><a href=\"budget\">Tambah saldo</a></li>";
                        if ($access && userHasAccess($access, "MANAGE_PROGRAM_TYPES"))
                            $s.="<li><a href=\"protypes\">Program Bidang</a></li>";
                        
                        //manage accounts                       
                        if ($access && userHasAccess($access, "ACCOUNT_MANAGE"))
                            $s.="<li><a href=\"accounts\">Kelola Akun</a></li>";
                        //access right
                        if ($access && userHasAccess($access, "ACCOUNT_ACCESS_RIGHTS"))
                            $s.="<li><a href=\"access\">Access Rights</a></li>";
                        //system variables                        
                        if ($access && userHasAccess($access, "MANAGE_SYSVAR"))
                            $s.="<li><a href=\"sysvars\">System Variables</a></li>";
                        //DATABASE Backup                      
                        if ($access && userHasAccess($access, "DATABASE_BACKUP"))
                            $s.="<li><a href=\"dbbackup\">Backup DB</a></li>";
                        //Log application
                        if ($access && userHasAccess($access, "MANAGE_LOG"))
                            $s.="<li><a href=\"logs\">Log Aplikasi</a></li>";
                        $s.="<li><a href=\"logout\">Logout</a></li>";
                    $s.="</ul>";
                $s.="</div>";
            $s.="</li>";
            //Basis data
            $s.="<li class=\"right head-link\">";
                $s.= "Basis Data";
                $s.="<div class=\"arrow-bottom\"></div>";
                $s.="<div class=\"submenu\">";
                    $s.="<ul class=\"submenu\">";
                        $s.="<li><a href=\"uker\">Unit Kerja</a></li>";
                        $s.="<li><a href=\"prop_kab\">Kabupaten/Kota</a></li>";
                    $s.="</ul>";
                $s.="</div>";
            $s.="</li>";
            //Report
            $s.="<li class=\"right head-link\">";
                $s.= "Reports";
                $s.="<div class=\"arrow-bottom\"></div>";
                $s.="<div class=\"submenu\">";
                    $s.="<ul class=\"submenu\">";
                        $s.="<li><a href=\"reports_wilayah\">Per Wilayah</a></li>";
                        $s.="<li><a href=\"reports_propinsi\">Per Propinsi</a></li>";
                        $s.="<li><a href=\"reports_detail\">Laporan Detail</a></li>";
                        $s.="<li><a href=\"reports_rkap\">RKAP (Form:8)</a></li>";
                    $s.="</ul>";
                $s.="</div>";
            $s.="</li>";
        $s.="</ul>";
    $s.="</div>";
    $s.="<div class=\"clr\"></div>";
    
    return $s;
}
function document_footer()
{
    $s="";
    $s.="<div class=\"clr\"></div>";
    $s.="<div class=\"copyright\">";
        $s.="<span style='font-style:italic;'>Best view with Google Chrome and Mozilla Firefox 4+</span><br />";
        $s.="<p>Copyright &copy; PT. Bank Rakyat Indonesia, Tbk. 2011</p>";
    $s.="</div>";
    $s.="<div class=\"stripe-orange\"></div>";
    
    return $s;
}
function get_user_style_files()
{
    $folder=  "customs/style/templates/";
    if (get_sysvar_value("USER_STYLE_NAME"))
        $folder.=get_sysvar_value ("USER_STYLE_NAME")."/";
    else
        $folder.= "default/";
    
    $extensions = "css";
    $file_list = file_list("../".$folder, $extensions);
    //start iterate style files
    $s="<link href=\"customs/js/jquery-ui-1.11.4/jquery-ui.min.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />";
    
    if ($file_list)
    {
        foreach($file_list as $css)
        {
            $s.="<link href=\"".$folder."/".$css['name']."\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />";
        }
    }
    
    return $s;
}
function check_login()
{
    if (!isLoggedin())
    {
        header("location:".ABSTRACT_BASE."login");
        exit;
    }
}
function isLoggedin()
{
    if (!isset($_SESSION['BRI_BL']['USERS']['LOGGED_IN'])||$_SESSION['BRI_BL']['USERS']['LOGGED_IN']!=true)
        return false;
    else if ($_SESSION['BRI_BL']['USERS']['LOGGED_IN']==true)
        return true;
}
function login($username, $password, DatabaseConnection $db_obj=NULL, &$message="")
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    //check username & password match in database
    $sql = "SELECT u.id, u.user_name, u.full_name, u.position, u.access, t.type, u.login_status, 
            u.last_login, u.last_ip,u.created_on, u.avatar
            FROM users u, user_types t
            WHERE (u.access=t.id)AND(STRCMP(u.user_name, '$username')=0)AND(u.password='".  password_rehash($password)."')";
    
    $result = $db_obj->execSQL($sql);
    //check if account exist
    if ($db_obj->getNumRecord()==0||!$result)
    {
        $message = "Maaf. Username dan password anda tidak sesuai";
        //create log
        logs("visitor","Mencoba login error: ".$message, $db_obj);
    }else{
        //check if someone else has loggedin using same account
        //if ($result[0]['login_status']==1&&$result[0]['last_ip']!= get_ip())
        if ($result[0]['login_status']==1)
        {
            $message = "Maaf. Seseorang telah login menggunakan account yang sama menggunakan IP Address:".  $result[0]['last_ip'];
            $message.= " Klik <a href='forcelogin?logid=".$result[0]['id']."&security=".md5(SEC_FORCE_LOGIN)."'>link</a> ini untuk memaksa login baru anda";
            //create log
            logs("visitor","Mencoba login error: ".$message, $db_obj);
        }else{
            //update new loggedin user
            login_session_update($result[0], $db_obj);
            return true;
        }
    }
    
    return false;
}
function login_session_update($result_user_array, DatabaseConnection $db_obj=NULL)
{
    //create new session for loggedin user
    $_SESSION['BRI_BL']['USERS']['LOGGED_IN'] = true;
    $_SESSION['BRI_BL']['USERS']['ID'] = $result_user_array['id'];
    $_SESSION['BRI_BL']['USERS']['ACCESS'] = $result_user_array['access'];
    $_SESSION['BRI_BL']['USERS']['TYPE'] = $result_user_array['type'];
    $_SESSION['BRI_BL']['USERS']['USERNAME'] = $result_user_array['user_name'];
    $_SESSION['BRI_BL']['USERS']['USERACCESS'] = $result_user_array['access'];
    $_SESSION['BRI_BL']['USERS']['FULLNAME'] = $result_user_array['full_name'];
    $_SESSION['BRI_BL']['USERS']['POSITION'] = $result_user_array['position'];
    $_SESSION['BRI_BL']['USERS']['AVATAR'] = $result_user_array['avatar'];
    if($result_user_array['last_login']!==NULL)
        $_SESSION['BRI_BL']['USERS']['LASTLOGIN'] = $result_user_array['last_login'];
    else
        $_SESSION['BRI_BL']['USERS']['LASTLOGIN'] = $result_user_array['created_on'];
    $_SESSION['BRI_BL']['USERS']['IPADDRESS'] = get_ip();
    
    //update last login date to NOW and login_status to 1 (login)
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "UPDATE users SET last_login=NOW(), 
            login_status=1, last_ip='". get_ip()."',last_activity=NOW()
            WHERE id=".$result_user_array['id'];
    $db_obj->query($sql);
            
    //create log
    logs($result_user_array['user_name'],"Berhasil login",$db_obj);
    
    //create tag to start activity
    //to maintain the validitiy ofuser session
    $_SESSION['BRI_BL']['USERS']['LAST_ACTIVITY'] = time();
}
function logout($message="",DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj=new DatabaseConnection ();
    
    //get username from session variable
    $username = get_user_info("USERNAME");
    
    //create log
    if (!$message) $message = "Logout from system";
    
    logs($username, $message, $db_obj);
    
    
    //Update database that login_status=0
    $sql = "UPDATE users SET login_status=0 WHERE user_name='$username'";
    $db_obj->query($sql);
    
    //clean all session variable
    $_SESSION['BRI_BL'] = array();
    session_destroy();
    //session_unset();
    return true;
}
function security_uri_check($max_parameter_allowed, $clean_uri_array)
{
    /*** SECURITY CHECK MUST EXISTS IN ALL PAGES ***/    
    //if any input in the URL more than expected
    //Send Error Page Not Found to header (we do not want more input)
    if ((isset($clean_uri_array[$max_parameter_allowed])&&$clean_uri_array[$max_parameter_allowed]!='')
        ||(count($clean_uri_array)>($max_parameter_allowed+1)))
    {
        //header("HTTP/1.0 404 Not Found");
        
        goto_filenotfound();
        exit;
    }    
}
function goto_filenotfound()
{
    $url = BASE_URL."filenotfound";
    header("location: ".$url);
    exit;
}
function get_user_info($info)
{
    if (isset($_SESSION['BRI_BL']['USERS'][$info]))
        return $_SESSION['BRI_BL']['USERS'][$info];
    else
        return false;
}
function get_program_types(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj)$db_obj=new DatabaseConnection ();
    
    $sql = "SELECT id, type FROM program_types
            ORDER BY type";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result;
    else
        return false;
}
//Saldo
function get_last_saldo($date="", $type=SALDO_ALOCATION,DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    if ($type==SALDO_ALOCATION)
        $table_name = "saldo";
    else
        $table_name = "saldo_real";
    if ($date=="")
    {
        $sql = "SELECT SUM(trans_credit)-sum(trans_debet) FROM $table_name";
    }else{
        $sql = "SELECT SUM(trans_credit)-sum(trans_debet) FROM $table_name
                WHERE DATE(trans_date)<='$date')";
    }
    $saldo = $db_obj->singleValueFromQuery($sql);
    return $saldo;
}
function get_last_used_money($date="", $type=SALDO_ALOCATION, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    if ($type==SALDO_ALOCATION)
    {
        $sql = "SELECT SUM(budget) AS debet FROM programs WHERE(state=1)";
        if ($date!="")
            $sql.=" AND(DATE(approval_date)<='$date')";
    }
    else
    {
        $sql = "SELECT SUM(br.nominal) AS debet FROM budget_real_used br, programs p WHERE(br.program=p.id)AND(p.state=1)";
        if ($date!="")
            $sql.=" AND(DATE(creation_date)<='$date')";
    }
    
    $debet = $db_obj->singleValueFromQuery($sql);
    return $debet;
}
function count_programs($user_id=0, $approve_type=-1, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    
    $sql = "SELECT COUNT(*) FROM programs 
           WHERE(1=1)";
    
    if ($user_id>0)
        $sql.= "AND(creation_by=$user_id)";
    switch($approve_type)
    {
        case 0: $sql.= "AND(state=0)"; break;
        case 1: $sql.= "AND(state=1)"; break;
    }
    
    $total = $db_obj->singleValueFromQuery($sql);
    
    return $total;
}
function load_propinsi_by_city($kota, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    $sql = "SELECT DISTINCT UPPER(propinsi)  as propinsi
            FROM propinsi 
            WHERE (kabupaten LIKE '%$kota%')OR(ibu_kab LIKE '%$kota%')";
    
    return $db_obj->execSQL($sql);
}
function load_propinsi(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    $sql = "SELECT id, UPPER(propinsi) as propinsi
            FROM propinsi
            ORDER BY propinsi";
    
    return $db_obj->execSQL($sql);
}
function load_kabupaten($propinsi=0, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    if($propinsi>0)
    {
        $sql = "SELECT id, UPPER(ibukota) as ibukota,UPPER(kabupaten) as kabupaten
                FROM kabupaten
                WHERE (propinsi=$propinsi)
                ORDER BY kabupaten";
    }else{
        $sql = "SELECT id, UPPER(ibukota) as ibukota,UPPER(kabupaten) as kabupaten
                FROM kabupaten
                ORDER BY kabupaten";
    }
    
    return $db_obj->execSQL($sql);
}
//kanwil
function load_kanwil(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT id, wilayah, uker,parent,tipe 
            FROM uker
            WHERE(parent=0)AND((tipe='KW')OR(tipe='KP'))
            ORDER BY uker ASC";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result;
    else
        return false;
}
function get_kanwil_arr(DatabaseConnection $db_obj=NULL){
    $kanwils = load_kanwil($db_obj);

    $new_arr = array();
    foreach($kanwils as $kanwil){
	$new_arr[$kanwil['id']] = $kanwil['uker'];
    }
    return $new_arr;
}
function load_kancab_by_parent($kanwil, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT DISTINCT id, cabang, uker,parent FROM uker 
            WHERE(tipe='KC')AND(parent=".$kanwil.")
            ORDER BY id";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result;
    else
        return false;
}
function load_kancab_by_wilayah($kanwil, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT DISTINCT id, cabang, uker,parent FROM uker 
            WHERE(tipe='KC')AND(wilayah=".$kanwil.")
            ORDER BY id";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result;
    else
        return false;
}
function get_uker_by_id($id, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT id, wilayah,cabang,parent,uker,alamat,kota,telepon,fax
            FROM uker
            WHERE(id=$id)";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result[0];
    else
        return false;
}
function get_uker_parent_by_id($id, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT parent
            FROM uker
            WHERE(id=$id)";
    $parent = $db_obj->singleValueFromQuery($sql);
    if ($parent)
    {
        $result = get_uker_by_id($parent, $db_obj);
        if ($result)
            return $result[0];
        else
            return false;
    }else
        return false;
}
function get_parent_id($id, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT parent FROM uker 
            WHERE(id=".$id.")";
    return $db_obj->singleValueFromQuery($sql);
}
function load_uker_types(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT id, type, caption FROM uker_type";
    return $db_obj->execSQL($sql);
}
function load_programs(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT id,name FROM programs
            ORDER BY creation_date DESC, name";
    return $db_obj->execSQL($sql);
}
function load_program($program_id,DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT * FROM programs
            WHERE id=$program_id";
    $result = $db_obj->execSQL($sql);
    if ($result)
        return $result[0];
    else
        return false;
}
function get_program_name_by_id($program, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT name FROM programs 
            WHERE(id=".$program.")";
    return $db_obj->singleValueFromQuery($sql);
}
function program_progress($program, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $progress = 0;
    $sql = "SELECT ((SUM(completed)/SUM(target))*100) AS progress
            FROM tasks 
            WHERE program=$program";
    $result = $db_obj->singleValueFromQuery($sql);
    if ($result)
        $progress = $result;
    
    return $progress;
}
function program_approved_budget($program, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $dana_approved = 0;
    $sql = "SELECT budget
            FROM programs
            WHERE id=$program";
    $result = $db_obj->singleValueFromQuery($sql);
    if ($result)
        $dana_approved = $result;
    
    return $dana_approved;
}
function program_real_fund_used($program, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $dana_realisasi = 0;
    $sql = "SELECT SUM(nominal) AS total
            FROM budget_real_used 
            WHERE program=$program";
    $result = $db_obj->singleValueFromQuery($sql);
    if ($result)
        $dana_realisasi = $result;
    
    return $dana_realisasi;
}
//Function to create log in database for every request by visitor
function logs($username="visitor",$action="", DatabaseConnection $db_obj=NULL)
{
    $ip_address = get_ip();
    $page = cur_page_name();
    $request = client_request();
    $session_id = session_id();
    
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    
    $sql = "INSERT INTO logs (ip_address,username,session_id,page,request,action)
            VALUES('$ip_address','$username','$session_id','$page','$request','$action')";
    $db_obj->query($sql);
}
function count_user_loggedin($current_user="", DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj=new DatabaseConnection ();
    $sql= "SELECT COUNT(*) FROM users 
            WHERE (login_status=1)";
    if ($current_user)
        $sql.=" AND(user_name<>'$current_user')";
    return $db_obj->singleValueFromQuery($sql);
}
function get_user_loggedin($limit=3, $include_caller=false, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj)$db_obj = new DatabaseConnection ();
    $sql = "SELECT id, user_name, full_name, last_login, avatar FROM users
            WHERE (login_status=1)";
    if (!$include_caller)
        $sql.=" AND(id<>".get_user_info ("ID").")";
    $sql.=" LIMIT 0,$limit";
    return $db_obj->execSQL($sql);
}
function update_user_session()
{
    $db_obj = new DatabaseConnection();
    if (get_sysvar_value("USER_SESSION_IDLE"))
    {
        $time_spare = ceil(get_sysvar_value("USER_SESSION_IDLE") * 60); //(in minutes)
    }else{
        $time_spare = 1200; //(20 minutes)
    }
    
    if (isLoggedin()&&isset($_SESSION['BRI_BL']['USERS']['LAST_ACTIVITY'])) 
    {
        if (time() - $_SESSION['BRI_BL']['USERS']['LAST_ACTIVITY'] > $time_spare) 
        {
            // last request was more than 30 minates ago
            if (logout("Logout from system because admin session has expired"))
            {
                header("location: ".ABSTRACT_BASE."login");
                exit;
            }
        }else{
            $_SESSION['BRI_BL']['USERS']['LAST_ACTIVITY'] = time(); // update last activity time stamp
            $sql = "UPDATE users SET login_status=1, last_activity=NOW() WHERE (id=".get_user_info("ID").")";            
            $db_obj->query($sql);
        }
    }
    
    
    //check other user only once
    //if (!isset($_SESSION['BRI_BL']['USERS']['CHECKALL']))
    //{
        if (isLoggedin())
        {
            $sql = "UPDATE users SET login_status=0
                    WHERE (id<>".get_user_info("ID").")AND(login_status=1)
                    AND (TO_SECONDS(NOW())-TO_SECONDS(last_activity) > $time_spare )";
        }else{
            $sql = "UPDATE users SET login_status=0
                    WHERE (login_status=1)
                    AND (TO_SECONDS(NOW())-TO_SECONDS(last_activity) > $time_spare )";
        }
        $db_obj->query($sql);
        
        $_SESSION['BRI_BL']['USERS']['CHECKALL'] = true;
    //}
    
}
function get_sysvar_value($key)
{
    if (isset($_SESSION['BRI_BL']['SYSVARS'][$key]))
        return $_SESSION['BRI_BL']['SYSVARS'][$key];
    else
        return false;
}
function load_sysvars(DatabaseConnection $db_obj=NULL)
{
    if (!isset($_SESSION['BRI_BL']['SYSVARS']['LOADED']))
    {
        if (!$db_obj) $db_obj = new DatabaseConnection ();
        $sql = "SELECT var, var_value FROM sysvars";
        $result = $db_obj->execSQL($sql);
        if ($result)
        {
            foreach($result as $item)
            {
                $_SESSION['BRI_BL']['SYSVARS'][$item['var']]=$item['var_value'];
            }
        }
        $_SESSION['BRI_BL']['SYSVARS']['LOADED']=true;
    }
}
//function to clear logs that old
function clear_old_log(DatabaseConnection $db_obj=NULL)
{
    if (!isset($_SESSION['BRI_BL']['LOG_CLEARED']))
    {
        if (!$db_obj) $db_obj = new DatabaseConnection ();
        
        //get the log old age
        if (!get_sysvar_value("LOG_AGE_OLD"))
        {
            $sql = "SELECT var_value FROM sysvars WHERE (var='LOG_OLD_AGE')";
            $age_old = $db_obj->singleValueFromQuery($sql);
        }else $age_old = get_sysvar_value("LOG_AGE_OLD");
        
        //save to file before delete
        if (save_deleted_logs($age_old, $db_obj))
        {
            //delete old logs from databases;
            $sql = "DELETE FROM logs WHERE (DATE(log_date) < DATE_ADD(CURDATE(), INTERVAL -$age_old DAY))";
            $db_obj->query($sql);
            $_SESSION['BRI_BL']['LOG_CLEARED'] = true;
        }
    }
}
function save_deleted_logs($age_old, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj=new DatabaseConnection ();
    $sql = "SELECT id,log_date,ip_address,username,page,request,action
            FROM logs 
            WHERE (DATE(log_date) < DATE_ADD(CURDATE(), INTERVAL -$age_old DAY))
            ORDER BY log_date DESC";
    
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $folder = APP_BASE_PATH."old_logs";
        $filename = time()."-"."deleted-logs";
        //check if folder exists, if not, create one
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0775)){
                return FALSE;
            }
        }
        
        $s="";
        foreach ($result as $item)
        {
            $s.= $item['log_date']."#";
            $s.= $item['ip_address']."#";
            $s.= $item['username']."#";
            $s.= $item['page']."#";
            $s.= $item['request']."#";
            $s.= $item['action']."\n";
        }
        if (file_put_contents($folder."/".$filename, $s))
            return true;
        else{
            //exit('Gagal menyimpan file logs yang akan dihapus');
            return false;            
        }
    }else{
        return false;
    }
}
function clean_garbage_session_files()
{
    $time_spare = 60*60*24; //(24 hours)
    $deleted_files = 0;
    $session_dir = sys_get_temp_dir() . '/';
    if (!isset($_SESSION['BRI_BL']['GARBAGE']))
    {
        if ($handle = opendir($session_dir)) {
            while (false !== ($filename = readdir($handle))) {
                clearstatcache();
                if (time() - filemtime($session_dir.$filename) > $time_spare)
                {
                    @unlink($session_dir.$filename);
                    $deleted_files++;
                }
            }

        }
        $_SESSION['BRI_BL']['GARBAGE'] = true;
    }
    return $deleted_files;
}
function loadUserAccess(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    $tablename = "user_access";
    $user_type = $_SESSION['BRI_BL']['USERS']['TYPE'];
    if ($user_type=='admin'){
        $sql = "SELECT access
                FROM $tablename 
                ORDER BY access,id";
    }else{
        $sql = "SELECT access, $user_type as 'access_value' 
                FROM $tablename 
                ORDER BY access,id";
    }
	
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $user_var = array();
	foreach	($result as $item)
	{
            if ($user_type=='admin')
                $user_var[$item['access']] = 1;
            else
                $user_var[$item['access']] = $item['access_value'];
	}
		
	return $user_var;
    }
    else return false;
}
function userHasAccess(array $access,$var)
{
    //always return true if access variable type not defined
    if (isset($access[$var]))
        return 	($access[$var]==1);
    else
        return true;
}
function getRealisationByTypeTriwulan($year,$triwulan,$program_type,$source=0,DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    
    $sql = "SELECT SUM((SELECT SUM(nominal) FROM budget_real_used WHERE program=p.id))
            FROM programs p
            WHERE (p.state=1)AND(p.source=$source)AND(p.type=$program_type)";
    
    switch($triwulan){
        case 1: $sql.=" AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=3)AND(YEAR(p.approval_date)=$year))"; break;
        case 2: $sql.=" AND((MONTH(p.approval_date)>=4)AND(MONTH(p.approval_date)<=6)AND(YEAR(p.approval_date)=$year))"; break;
        case 3: $sql.=" AND((MONTH(p.approval_date)>=7)AND(MONTH(p.approval_date)<=9)AND(YEAR(p.approval_date)=$year))"; break;
        case 4: $sql.=" AND((MONTH(p.approval_date)>=10)AND(MONTH(p.approval_date)<=12)AND(YEAR(p.approval_date)=$year))"; break;
    }
    
    $real_value = $db_obj->singleValueFromQuery($sql);
    
    if ($real_value)
        return $real_value;
    else
        return 0;
}
function getRealisationByTriwulan($year,$triwulan,$source=0,DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    
    $sql = "SELECT SUM((SELECT SUM(nominal) FROM budget_real_used WHERE program=p.id))
            FROM programs p
            WHERE (p.state=1)AND(p.source=$source)";
    
    switch($triwulan){
        case 1: $sql.=" AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=3)AND(YEAR(p.approval_date)=$year))"; break;
        case 2: $sql.=" AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=6)AND(YEAR(p.approval_date)=$year))"; break;
        case 3: $sql.=" AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=9)AND(YEAR(p.approval_date)=$year))"; break;
        case 4: $sql.=" AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=12)AND(YEAR(p.approval_date)=$year))"; break;
    }
    
    $real_value = $db_obj->singleValueFromQuery($sql);
    
    if ($real_value)
        return $real_value;
    else
        return 0;
}
function getBenefByTriwulan($year,$triwulan,$benef_type=0,DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    
    $field_benef = ($benef_type==0?'benef_orang':'benef_unit');
    $sql = "SELECT SUM($field_benef)
            FROM programs
            WHERE (state=1)";
    
    switch($triwulan){
        case 1: $sql.=" AND((MONTH(approval_date)>=1)AND(MONTH(approval_date)<=3)AND(YEAR(approval_date)=$year))"; break;
        case 2: $sql.=" AND((MONTH(approval_date)>=1)AND(MONTH(approval_date)<=6)AND(YEAR(approval_date)=$year))"; break;
        case 3: $sql.=" AND((MONTH(approval_date)>=1)AND(MONTH(approval_date)<=9)AND(YEAR(approval_date)=$year))"; break;
        case 4: $sql.=" AND((MONTH(approval_date)>=1)AND(MONTH(approval_date)<=12)AND(YEAR(approval_date)=$year))"; break;
    }
    
    $value = $db_obj->singleValueFromQuery($sql);
    
    if ($value)
        return $value;
    else
        return 0;
}
clean_garbage_session_files();
//do clear if not done yet
clear_old_log();
//load system variables
//load_sysvars();
//Update session fo active users
update_user_session();
?>