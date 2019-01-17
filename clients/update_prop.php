<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

print("SELECT id, kota_id FROM uker WHERE kota_id<>0.....<br />");
$sql = "SELECT id, kota_id FROM uker WHERE (kota_id<>0)AND(propinsi=0) ";
$kota = $db_obj->execSQL($sql);
print("Get ".$db_obj->getNumRecord()." records<br />");
print("Start iterating table.....<br />");
$updated_rec = 0;

if ($kota)foreach($kota as $item){
    print("SELECT propinsi FROM kabupaten table WHERE has same kota name.....<br />");
    $sql = "SELECT propinsi FROM kabupaten WHERE (id=".$item['kota_id'].")";
    $id_prop = $db_obj->singleValueFromQuery($sql);    
    if ($id_prop>0){
        print("Found id ".$id_prop."....now update table uker...<br />");
        $sql = "UPDATE uker SET propinsi=".$id_prop." WHERE id=".$item['id'];
        if ($db_obj->query($sql))
        {
            $updated_rec++;
            print("Success update uker table.....<br />");
        }
    }else{
        print("ERROR id prop not found ...<br />");
    }
    flush_me();
}
print("Total success updated record in uker table.....".$updated_rec." records");

function flush_me (){
    echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){            
        @ob_flush();
        @flush();
        @ob_end_flush();
    }    
    @ob_start();
}
?>
