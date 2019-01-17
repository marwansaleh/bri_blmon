<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

$sql = "SELECT id,uker,parent from uker where (kabupaten=propinsi)";
echo "Load id for all uker with empty kabupaten or propinsi...".PHP_EOL;
$uker = $db_obj->execSQL($sql);
print("Found ".$db_obj->getNumRecord());
foreach($uker as $item)
{
    print($item['uker']." updating...");
    $sql = "SELECT kabupaten, propinsi FROM uker WHERE id=".$item['parent'];
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $sql = "UPDATE uker SET kabupaten=".$result[0]['kabupaten'].", propinsi=".$result[0]['propinsi']."
                WHERE id=".$item['id'];
        if ($db_obj->query($sql))
            print("OK".PHP_EOL);
        else
            print("FAILED".PHP_EOL);
    }
    flush_me();
}
echo PHP_EOL."--------------------------------------------------------------------------------------FINISH";
?>
