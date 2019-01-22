<?php 
require_once("./../funcs/database.class_new.php"); 
//require_once("./../funcs/functions.php"); 
//require_once("./../funcs/tools.php");
//require_once("./../funcs/constant.php"); 

define('SOURCE_HOST', 'localhost');
define('SOURCE_DB', 'bri_csr_lama');
define('SOURCE_USER', 'root');
define('SOURCE_PWD', 'password');

define('TARGET_HOST', 'localhost');
define('TARGET_DB', 'bri_csr');
define('TARGET_USER', 'root');
define('TARGET_PWD', 'password');

$db_source = new Database(SOURCE_DB, SOURCE_USER, SOURCE_PWD, SOURCE_HOST);
$db_target = new Database(TARGET_DB, TARGET_USER, TARGET_PWD, TARGET_HOST);

function is_migrated(Database $db_target, $hash){
    $sql = "SELECT * FROM migration_programs WHERE hash='$hash'";
    if ($db_target->get($sql)){
        return TRUE;
    }else{
        return FALSE;
    }
}

//get all programs on month JUNI 2015 FROM SOURCE
$program_month = 6;
$program_year = 2015;

$source_programs_sql = 'SELECT * FROM programs WHERE YEAR(creation_date)='.$program_year.' AND MONTH(creation_date)='. $program_month;
$source_programs = $db_source->get($source_programs_sql);
echo "Menemukan ".count ($source_programs).' programs di bulan '. $program_month.' - '. $program_year . PHP_EOL;

foreach ($source_programs as $sp){
    //check if already migrated
    $hash = md5($sp->type . $sp->name . $sp->creation_date);
    
    if (is_migrated($db_target, $hash)){
        echo $sp->name.  " has been Migrated" .PHP_EOL;
    }else{
        echo $sp->name . ' not migrated yet..migrating now...';
        
        //Migrate program
        $data = array(
            'type'          => $sp->type,
            'source'        => $sp->source,
            'name'          => $sp->name,
            'description'   => $sp->description,
            'potensi_bisnis'=> $sp->potensi_bisnis,
            'uker'          => $sp->uker,
            'uker_wilayah'  => $sp->uker_wilayah,
            'uker_cabang'   => $sp->uker_cabang,
            'pic'           => $sp->pic,
            'creation_date' => $sp->creation_date,
            'creation_by'   => $sp->creation_by,
            'state'         => $sp->state,
            'approval_date' => $sp->approval_date,
            'approval_by'   => $sp->approval_by,
            'budget'        => $sp->budget,
            'operational'   => $sp->operational,
            'benef_name'    => $sp->benef_name,
            'benef_address' => $sp->benef_address,
            'benef_phone'   => $sp->benef_phone,
            'benef_email'   => $sp->benef_email,
            'benef_orang'   => $sp->benef_orang,
            'benef_unit'    => $sp->benef_unit,
            'last_update_by'=> $sp->last_update_by
        );
        
        $inserted_program_id = $db_target->insert($data, 'programs');
        echo "New program migrated...try to migrate budget real used if exists.";
        
        $budget_real = $db_source->get("SELECT * FROM budget_real_used WHERE program=". $sp->id);
        if ($budget_real && count($budget_real)){
            echo " Update real budget used". PHP_EOL;
            foreach ($budget_real as $br){
                $budget = array(
                    'program'       => $inserted_program_id,
                    'caption'       => $br->caption,
                    'nominal'       => $br->nominal,
                    'creation_date' => $br->creation_date,
                    'creation_by'   => $br->creation_by,
                    'last_update'   => $br->last_update,
                    'last_update_by'=> $br->last_update_by
                );
                
                $db_target->insert($budget, 'budget_real_used');
            }
        }else{
            echo " No budget real used update". PHP_EOL;
        }
        
        //Update migration table
        $migration_status = array(
            'migrated'      => time(),
            'hash'          => $hash,
	    'source_program_id' => $sp->id,
	    'target_program_id' => $inserted_program_id
        );
        $db_target->insert($migration_status, 'migration_programs');
    }
}
