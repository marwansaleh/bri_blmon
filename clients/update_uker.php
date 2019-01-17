<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

echo "Load id for all wilayah...".PHP_EOL;
$sql = "SELECT DISTINCT id, wilayah, uker FROM uker WHERE (tipe='KW')";
$wilayah  = $db_obj->execSQL($sql);
echo "Update parent for KC & KANINS...".PHP_EOL;
flush();
foreach($wilayah as $item){
    $sql = "UPDATE uker SET parent=".$item['id']." WHERE (wilayah=".$item['wilayah'].")AND((tipe='KC')OR(tipe='KANINS'))";
    $db_obj->query($sql);
    echo "-- update KC / KANINS wilayah=".$item['uker']." -->affected rows:".$db_obj->getNumRecord().PHP_EOL;    
    //load all cabang id from this wilayah
    echo PHP_EOL.PHP_EOL. "Load cabang id for wilayah=".$item['uker']."...".PHP_EOL;
    $sql = "SELECT id, cabang, uker FROM uker WHERE (wilayah=".$item['wilayah'].")AND(tipe='KC')";
    $cabang = $db_obj->execSQL($sql);
    echo "update parent for KK and KCP in this wilayah and cabang".PHP_EOL;
    flush();
    foreach($cabang as $cabang_id){
        $sql = "UPDATE uker SET parent=".$cabang_id['id']." 
                WHERE ((wilayah=".$item['wilayah'].")AND(cabang=".$cabang_id['cabang']."))AND((tipe='KK')OR(tipe='KCP'))";
        $db_obj->query($sql);
        echo "-- update KCP / KK wilayah=".$item['uker']."& cabang=".$cabang_id['uker']." -->affected rows:".$db_obj->getNumRecord().PHP_EOL;
        flush();
    }
}
echo PHP_EOL."--------------------------------------------------------------------------------------FINISH";
?>
