<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$qs = str_ireplace(FOLDER_PREFIX, "", $_SERVER["REQUEST_URI"]);
$qs = explode("/", $qs);
array_shift($qs);

//check security uri, must do in every page
//to avoid http injection
$max_parameter_alllowed = 1;
security_uri_check($max_parameter_alllowed, $qs);

//Create database Object
if (isset($_GET['file']))
{
    $filename = $_GET['file'];
    $extension = get_file_extension($filename);
    $filename = "../doc_references/".$filename;
    if (!file_exists($filename))
        exit("Maaf. file yang anda cari tidak ditemukan");
    
    
    
    
    switch($extension){
        case "jpg": showImage($filename); break;
        case "pdf": showPDF($filename); break;
        case "doc": showMSDoc($filename); break;
    }
    
    
}else{
    exit("File tidak terdefinisi");
}
function showImage($filename)
{
    header('Content-Type: image/jpeg');

    $img = LoadJpeg($filename);

    imagejpeg($img);
    imagedestroy($img);
}
function LoadJpeg($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefromjpeg($imgname);

    /* See if it failed */
    if(!$im)
    {
        /* Create a black image */
        $im  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

        /* Output an error message */
        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
    }

    return $im;
}

function showPDF($filename)
{
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=file.pdf");
    header("Content-Length: " . filesize($filename));
    @readfile($filename);
}
function showMSDoc($filename)
{
    header("Content-type: application/MS-Word");
    header("Content-Disposition: inline; filename=file.doc");
    header("Content-Length: " . filesize($filename));
    @readfile($filename);
}
?>


