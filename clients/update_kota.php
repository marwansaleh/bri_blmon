<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

print("SELECT id, kota FROM uker WHERE kota_id=0.....<br />");
$sql = "SELECT id, kota FROM uker WHERE kota_id=0";
$kota = $db_obj->execSQL($sql);
print("Get ".$db_obj->getNumRecord()." records<br />");
print("Start iterating table.....<br />");
$updated_rec = 0;

if ($kota)foreach($kota as $item){
    print("SELECT id FROM kabupaten table WHERE has same kota name.....<br />");
    $sql = "SELECT id FROM kabupaten WHERE (kabupaten LIKE '%".$item['kota']."%')OR(ibukota LIKE '%".$item['kota']."%')";
    $id_kab = $db_obj->singleValueFromQuery($sql);    
    if ($id_kab>0){
        print("Found id ".$id_kab."....now update table uker...<br />");
        $sql = "UPDATE uker SET kota_id=".$id_kab." WHERE id=".$item['id'];
        if ($db_obj->query($sql))
        {
            $updated_rec++;
            print("Success update uker table.....<br />");
        }
    }else{
        print("ERROR id kab not found ...<br />");
    }
    flush_me();
}
print("Total success updated record in uker table.....".$updated_rec." records");


?>
