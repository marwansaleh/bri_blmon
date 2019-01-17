<?php 
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

//load user access
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Page Not Found</h1>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <p>Maaf. Halaman yang anda cari tidak dapat ditemukan. 
                Silahkan gunakan <em>Menu Link</em> di atas untuk
                melihat fitur yang anda inginkan.</p>
            <p>Jika menurut anda permasalahan ada di sistem atau fitur
            yang anda inginkan belum ada, silahkan hubungi melalui email ke
            <strong><a href="mailto:amazzura.biz@gmail.com">Webmaster</a></strong>
            atau <strong>mobile phone (+62) 8121443323</strong>.</p>
        </div>
    </div>
    <?php echo document_footer();?>
</body>
</html>