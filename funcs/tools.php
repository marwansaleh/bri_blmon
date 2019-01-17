<?php
/*
 * These line below is security check 
 */
/*
 * Password Salt
 * Make sure stay untouch or you can't login
 */
define ("SALT","$#123BG*");
function password_rehash($password)
{
    $hash = SALT.md5($password);
    $hash = md5($hash);
    
    return $hash;
}
function get_random_string($length = 6) 
{
    $validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ";
    $validCharNumber = strlen($validCharacters);
 
    $result = "";
 
    for ($i = 0; $i < $length; $i++) {
        $index = mt_rand(0, $validCharNumber - 1);
        $result .= $validCharacters[$index];
    }
 
    return $result;
}
function is_valid_password($password)
{
    //password must contain min 6-20 chars with combination alpha numeric
    return preg_match( "/^((?=.*\d)(?=.*[a-zA-Z]).{6,20})$/", $password);
}
//Function to get Visitor IP Address
function get_ip()
{
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return htmlspecialchars($ip);
}
//Function to get name of page requested in the URL
function cur_page_name($with_extension=true) 
{
    $page_name = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    if (!$with_extension)
    {
        $page_name = explode(".",$page_name);
        $page_name = $page_name[0];
    }
    return $page_name;
}
//Get all request string from URI 
function client_request()
{
    return $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']." [Method:".$_SERVER['REQUEST_METHOD'].'][Client:'.$_SERVER['HTTP_USER_AGENT'].']';
}
//Function to return date in Indonesia format
function get_indonesian_date($date="",$include_time=false)
{
    $day_name = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
    $month_name = array("Januari","Pebruari","Maret","April","Mei","Juni",
                        "Juli","Agustus","September","Oktober","Nopember","Desember"
                    );
    if ($date=="")
        $date = getdate ();
    else
        $date = getdate (strtotime ($date));
    
    $s = "";
    $s.= $day_name[$date["wday"]].", ";
    $s.= $date["mday"]." ";
    $s.= $month_name[$date["mon"]-1]." ";
    $s.= $date['year'];
    if ($include_time)
    {
        $s.= " ".$date['hours'].":".$date['minutes'].":".$date['seconds'];
    }
    
    return $s;
}
function get_indonesian_month($month)
{
    $month_name = array(1=>"Januari","Pebruari","Maret","April","Mei","Juni",
                        "Juli","Agustus","September","Oktober","Nopember","Desember"
                    );
    return $month_name[$month];
}
function get_indonesian_time($datetime="", $suffix="WIB")
{
    if ($datetime=="")
        $datetime = getdate ();
    else
        $datetime = getdate (strtotime ($datetime));
    
    $s = $datetime['hours'].":".$datetime['minutes'].":".$datetime['seconds'].$suffix;
    return $s;
}
/*
 * function get_extension
 * @param string filename
 * @return string extension if found
 */
function get_file_extension($filename)
{
    $filename = explode(".",$filename);
    if (count($filename)>1)
        return ($filename[count($filename)-1]);
    else
        return "unknown";
}
/*
 * function remove_space to remove all space
 */
function remove_spaces($subject)
{
    $str = preg_replace( '/\s+/', '', $subject );
    return $str;
}
function file_list($folder,$extensions="jpg,png")
{
    $allowable_ext = explode(",",$extensions);
    $files = array();
    if ($handle = opendir($folder)) {        
        while (false !== ($filename = readdir($handle))) {
            //must call this function before calling filesize function
            clearstatcache();
            if (in_array(get_file_extension($filename),$allowable_ext))
            {
                $info['name'] = $filename;
                $info['extension'] = get_file_extension($filename);
                $info['size'] = filesize($folder.$filename);
                $files[] = $info;
            }
        }
        closedir($handle);
    }
    if (count($files)>0)
        return $files;
    else
        return false;
}
function get_label_docref_type($type)
{
    $array = array("jpg"=>"Image","doc"=>"MS Word","pdf"=>"Adobe PDF");
    if (isset($array[$type]))
        return $array[$type];
    else
        return "unknown";
}
/*
 * Recursively delete directory
 */
function rrmdir($dir) 
{
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}
function sanitizeText($text) 
{ 
    $text = str_replace("<", "&lt;", $text); 
    $text = str_replace(">", "&gt;", $text); 
    $text = str_replace("\"", "&quot;", $text); 
    $text = str_replace("'", "&#039;", $text); 
    $text = str_replace("&", "&amp;", $text); 
    
    // it is recommended to replace 'addslashes' with 'mysql_real_escape_string' or whatever db specific fucntion used for escaping. However 'mysql_real_escape_string' is slower because it has to connect to mysql. 
    $text = addslashes($text); 

    return $text; 
} 
function time_since($original) 
{
    // array of time period chunks
    $chunks = array(
        array(60 * 60 * 24 * 365 , 'year'),
        array(60 * 60 * 24 * 30 , 'month'),
        array(60 * 60 * 24 * 7, 'week'),
        array(60 * 60 * 24 , 'day'),
        array(60 * 60 , 'hour'),
        array(60 , 'minute'),
    );
    
    $count = 0;
    $today = time(); /* Current unix time  */
    $since = $today - strtotime($original);
    
    // $j saves performing the count function each time around the loop
    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        
        // finding the biggest chunk (if the chunk fits, break)
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }
    
    $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
    
    if ($i + 1 < $j) {
        // now getting the second item
        $seconds2 = $chunks[$i + 1][0];
        $name2 = $chunks[$i + 1][1];
        
        // add second item if it's greater than 0
        if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
            $print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}";
        }
    }
    $print.=' ago';
    return $print;
}
function flush_me ()
{
    echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){            
        @ob_flush();
        @flush();
        @ob_end_flush();
    }    
    @ob_start();
}
function resize_image($src_name, $targ_name, $targ_w, $targ_h, $quality=75)
{
    $result = false;
    list($src_width,$src_height) = getimagesize($src_name);
    //only resize if the size not match
    if ($src_width!=$targ_w||$src_height!=$targ_h)
    {
        $file_ext = get_file_extension($src_name);
	if ($file_ext=='jpg')
            $img_r = imagecreatefromjpeg($src_name);
        else if ($file_ext=='png')
            $img_r = imagecreatefrompng($src_name);
		
	/*resource imagecreatetruecolor ( int $width , int $height )*/
	$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
	
        $result = imagecopyresampled( $dst_r, $img_r,
                            0, 0,
                            0, 0,
                            $targ_w, $targ_h,
                            $src_width,$src_height
        );
        if (!$result) return false;
        
	if ($file_ext=='jpg'||$file_ext=='jpeg')
	{
            /*bool imagejpeg ( resource $image [, string $filename [, int $quality ]] )*/
            $result = imagejpeg($dst_r, $targ_name, $quality); //write to disk
	}
        
	else if ($file_ext=='png')
	{
            /*bool imagepng ( resource $image [, string $filename [, int $quality [, int $filters ]]] )*/
            /* compresi 0=best,9=worst */
            $quality = ceil(9-(($quality/100)*9));
            $result = imagepng ($dst_r,$targ_name, $quality);
	}
	imagedestroy($dst_r); 
	imagedestroy($img_r); 
		
	chmod($targ_name,0775);	
    }else $result = true;
    
    return $result;
}
?>
