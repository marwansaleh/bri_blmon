<?php
require_once("./../funcs/functions.php");
require_once("./../funcs/constant.php");

if (logout())
{
    header("location: login");
    exit;
}
else
{
    echo "Gagal melalukan proses logout";
    exit;
}
?>
