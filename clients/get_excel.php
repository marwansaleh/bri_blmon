<?php
if (isset($_GET['filename']))
{
    $filename = $_GET['filename'];
    
    $content = file_get_contents("../temp/".$filename);
    if ($content)
    {
        $mime = "application/vnd.ms-excel";
        header("Content-type: ".$mime);
        header("Content-Disposition: attachment; filename=$filename");
        echo $content;
    }
}
?>
