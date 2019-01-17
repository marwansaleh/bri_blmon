<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();
//update uker if cabang=0
print("Checking uker field in table of programs...");
$sql = "SHOW COLUMNS FROM programs";
$columns = $db_obj->execSQL($sql);
$exists = false;
if ($columns)
{
    foreach($columns as $column)
    {
        if ($column['Field']=='uker')
        {
            $exists = true;
            print("exist!...<br />");
            break;
        }
    }
}

if (!$exists)
{
    print("Not exist!...creating the field...");
    $sql = "ALTER TABLE programs ADD uker  INT(11) NOT NULL DEFAULT 1 AFTER description";
    if ($db_obj->query($sql))
    {
        $exists = true;
        print("success created <br />");
    }
    else
        print("failed <br />");
}
if (!$exists)
    exit("Sorry. Process stopped because field 'uker' not exists");

$sql = "SELECT id, uker_wilayah, uker_cabang FROM programs WHERE (uker=0)OR (uker=1)";
$programs = $db_obj->execSQL($sql);
$total = $db_obj->getNumRecord();
$success = 0;
if ($programs)
{
    print("Updating $total programs...<br />");
    foreach($programs as $program)
    {
        if ($program['uker_cabang']>0)
            $sql = "UPDATE programs SET uker=".$program['uker_cabang'];
        else
            $sql = "UPDATE programs SET uker=".$program['uker_wilayah'];
        $sql.=" WHERE id=".$program['id'];
        if ($db_obj->query($sql))
            $success++;
    }
}
/*
$sql = "UPDATE programs SET uker=uker_wilayah WHERE (uker=0)AND(uker_cabang=0)";
if ($db_obj->query($sql))
    print("Updating programs where cabang=0...success<br />");
else
    print("Updating programs where cabang=0...failed<br />");
//update program if cabang !=0
$sql = "UPDATE programs SET uker=uker_cabang WHERE (uker=0)AND(uker_cabang>0)";
if ($db_obj->query($sql))
    print("Updating programs where cabang>0...success<br />");
else
    print("Updating programs where cabang>0...failed<br />");
*/
print("Success updated $success programs");
?>
