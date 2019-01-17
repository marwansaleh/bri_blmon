<?php
if (isset($_GET['filename']))
{
    $filename = base64_decode(urldecode($_GET['filename']));
    
    $content = file_get_contents($filename);
    if ($content)
    {
        $mime = "application/zip";
        header("Content-type: ".$mime);
        header("Content-Disposition: attachment; filename=". basename($filename));
        echo $content;
    }
}
?>
