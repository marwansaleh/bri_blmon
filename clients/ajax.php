<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php"); 
require_once("./../funcs/constant.php"); 
require_once("./../funcs/db_config.php"); 

if (isset($_POST['input_function']))
    $function = $_POST['input_function'];
else
{
    echo 0;
    exit;
}
if (isset($_POST['param']))
    $param = $_POST['param'];

switch($function)
{
    case 'loadPrograms': loadPrograms($param); break;
    case 'loadKanwil': loadKanwil(); break;
    case 'loadKancab': loadKancab($param); break;
    case 'loadKancabByParent': loadKancabByParent($param); break;
    case 'savePrograms': savePrograms($param); break;
    case 'deleteDocRef': deleteDocRef($param); break;
    case 'updateProgramStatus': updateProgramStatus($param); break;
    case 'loadTasks': loadTasks($param); break;
    case 'saveTasks': saveTasks($param); break;
    case 'loadUker': loadUker($param); break;
    case 'saveUker': saveUker($param); break;
    case 'loadKabupaten': loadKabupaten($param); break;
    case 'loadReport': loadReport($param); break;
    case 'loadReportDetail': loadReportDetail($param); break;
    case 'loadProgramsByWilayah': loadProgramsByWilayah($param); break;
    case 'loadProgramsByCreator': loadProgramsByCreator($param); break;
    case 'updateAccess': updateAccess($param); break;
    case 'updateAccessAll': updateAccessAll($param); break;
    case 'deletePrograms': deletePrograms($param); break;
    case 'loadRealBudget': loadRealBudget($param); break;
    case 'saveRealBudget': saveRealBudget($param); break;
    case 'loadProgramsByPropinsi': loadProgramsByPropinsi($param); break;
    case 'accountUpdate': accountUpdate($param); break;
    case 'creditSaldo': creditSaldo($param); break;
    case 'updateSysVar': updateSysVar($param); break;
    case 'updateSysVarAll': updateSysVarAll($param); break;
    case 'loadLogs': loadLogs($param); break;
    case 'deleteLogs': deleteLogs($param); break;
    case 'loadUsers': loadUsers($param); break;
    case 'deleteUser': deleteUser($param); break;
    case 'loadAllKabupaten': loadAllKabupaten($param); break;
    case 'saveKabupaten': saveKabupaten($param); break;
    case 'deleteKabupaten': deleteKabupaten($param); break;
    case 'deleteTasks'; deleteTasks($param); break;
    case 'deleteUker': deleteUker($param); break;
    case 'deleteRealisation': deleteRealisation($param); break;
    case 'loadNews': loadNews($param); break;
    case 'saveNews': saveNews($param); break;
    case 'deleteNews': deleteNews($param); break;
    case 'loadAllBackups': loadAllBackups(); break;
    case 'createBackup': createBackup(); break;
    case 'deleteBackups': deleteBackups($param); break;
    case 'restoreFromBackup': restoreFromBackup($param); break;
    case 'loadProTypes': loadProTypes($param); break;
    case 'protypeUpdate': protypeUpdate($param); break;
    case 'deleteProtypes':deleteProtypes($param); break;
    case 'lookupPIC': lookupPIC($param); break;
    case 'lookupBeneficiaries': lookupBeneficiaries($param); break;
    case 'loadBeneficiary': loadBeneficiary($param); break;
    case 'updateSingleValue': updateSingleValue(); break;
    case 'loadAreas': loadAreas($param); break;
    case 'export_to_excel': export_to_excel($param); break;
    case 'export_filtered_programs': export_filtered_programs(); break;
    case 'export_filtered_wilayah': export_filtered_wilayah(); break;
    case 'download_backup': download_backup(); break;
    //RKAP
    case 'saveRKAP': saveRKAP($param); break;
    case 'loadRKAP': loadRKAP($param); break;
    case 'loadRKAP_Report': loadRKAP_Report($param); break;
    case 'deleteRKAP': deleteRKAP($param); break;
    case 'loadRKAPComponent': loadRKAPComponent(); break;
    case 'loadRKAPCategory': loadRKAPCategory(); break;
    case 'saveRKAPComponent': saveRKAPComponent($param); break;
    case 'deleteRKAPComponent': deleteRKAPComponent($param); break;
    case 'saveRKAPCategory': saveRKAPCategory($param);break;
    case 'deleteRKAPCategory': deleteRKAPCategory($param); break;
    case 'getProgramRealisationByTriwulan': getProgramRealisationByTriwulan($param); break;
    case 'getBenefTriwulan': getBenefTriwulan($param); break;
    default: echo 0;
}
//helper function to search if input is kanwil
function get_kanwil_id_by_searching($txt_search, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    $sql = "SELECT id FROM uker WHERE (uker LIKE '%".$txt_search."%')AND((tipe='KW')OR(tipe='KP'))";
    $result = $db_obj->execSQL($sql);
    $kanwil_id = array();
    if ($result)
    {
        foreach($result as $item)
            $kanwil_id[] = $item['id'];
        return $kanwil_id;
    }
    else
        return false;
}
function loadPrograms($page)
{
    $db_obj = new DatabaseConnection();
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $type= $_POST['type'];
    $state = $_POST['state'];
    $creation_year = isset($_POST['creation_year']) ? $_POST['creation_year'] : NULL;
    
    $num_of_recs = 7;
    //count all pages
    $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker=u.id)AND(k.id=u.kabupaten)AND(u.propinsi=pr.id)";
    if ($creation_year){
        $sql.=" AND (YEAR(p.creation_date)=$creation_year)";
    }
    if(isset($search_string))
    {        
        $sql.=" AND((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')
                OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')
				OR(p.nodin_putusan LIKE '%$search_string%')
				OR(p.nomor_registrasi LIKE '%$search_string%')
				OR(p.nomor_persetujuan LIKE '%$search_string%')";
        
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
        $sql.=")";
    }
    if ($type>0)
        $sql.=" AND(p.type=$type)";
    if ($state>=0) {
        $sql.=" AND(p.state=$state)";
    }
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)";
        
        if ($creation_year){
            $sql.=" AND (YEAR(p.creation_date)=$creation_year)";
        }
        if (isset($search_string))
        {
            $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                    OR(u.uker LIKE '%$search_string%')OR(k.kabupaten LIKE '%$search_string%')
                    OR(k.ibukota LIKE '%$search_string%')
                    OR(pr.propinsi LIKE '%$search_string%')
                    OR(pr.ibukota LIKE '%$search_string%')
                    OR(p.benef_name LIKE '%$search_string%')
					OR(p.nodin_putusan LIKE '%$search_string%')
					OR(p.nomor_registrasi LIKE '%$search_string%')
					OR(p.nomor_persetujuan LIKE '%$search_string%')";
            if ($kanwil_id_like)
                $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
            $sql.=")";
        }
        if ($type>0)
            $sql.= " AND(p.type=$type)";
        
        if ($state>=0) {
            $sql.=" AND(p.state=$state)";
        }
        
        $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC
                LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item)
            {
                $item['progress'] = number_format(program_progress($item['id'], $db_obj),2,",",".");
                $item['real_used'] = number_format(program_real_fund_used($item['id'], $db_obj),2,",",".");
                $item['view_by'] = get_user_info("ID");
                /*
                if ($item['uker_cabang']>0)
                {
                    $uker = get_uker_by_id($item['uker_cabang'], $db_obj);
                    if ($uker)
                        $item['uker'] = $uker['uker'];
                }
                 * 
                 */
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
    
}
function loadKanwil()
{
    $db_obj = new DatabaseConnection();
    $result = load_kanwil($db_obj);
    
    $data = array("found"=>0,"error"=>'', "items"=>array());
    if ($result){
        $data['items'] = $result;
        $data['found'] = count($result);
    }else{
        $data['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($data);
}
function loadKancab($wilayah)
{
    $db_obj = new DatabaseConnection();
    $result = load_kancab_by_wilayah($wilayah, $db_obj);
    
    $data = array("found"=>0,"error"=>'', "items"=>array());
    if ($result){
        $data['items'] = $result;
        $data['found'] = count($result);
    }else{
        $data['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($data);
}
function loadKancabByParent($wilayah)
{
    $db_obj = new DatabaseConnection();
    $result = load_kancab_by_parent($wilayah, $db_obj);
    
    $data = array("found"=>0,"error"=>'', "items"=>array());
    if ($result){
        $data['items'] = $result;
        $data['found'] = count($result);
    }else{
        $data['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($data);
}
function savePrograms($mode)
{
    $db_obj = new DatabaseConnection();
    
    $id = $_POST['id'];
    $name = sanitizeText($_POST['name']);
    $source = $_POST['source'];
    $description = mysql_real_escape_string($_POST['description']);
    $potensi_bisnis = mysql_real_escape_string($_POST['potensi_bisnis']);
    $type = $_POST['type'];
    $state = $_POST['state'];
    $budget = $_POST['budget'];
    $budget_original = $_POST['budget_original'];
    $operational = $_POST['operational'];
    $operational_original = $_POST['operational_original'];
    $benef_name = $_POST['benef_name'];
    $benef_address = $_POST['benef_address'];
    $benef_phone = $_POST['benef_phone'];
    $benef_email = $_POST['benef_email'];
    $benef_orang = $_POST['benef_orang'];
    $benef_unit = $_POST['benef_unit'];
    $pic = sanitizeText($_POST['pic']);
    $uker_cabang = $_POST['kancab'];
    $uker_wilayah = $_POST['kanwil'];
    if ($uker_cabang>0)
        $uker = $uker_cabang;
    else
        $uker = $uker_wilayah;
    if ($_POST['creation_date']!='')
        $creation_date = $_POST['creation_date'];
    else
        $creation_date = date("Y-m-d H:i:s");
    if ($_POST['approval_date']!='')
        $approval_date = $_POST['approval_date'];
    else
        $approval_date = NULL;
    $nodin_putusan = trim($_POST['nodin_putusan']);
    $nomor_persetujuan = trim($_POST['nomor_persetujuan']);
    $nomor_registrasi = trim($_POST['nomor_registrasi']);
    $nomor_bg = trim($_POST['nomor_bg']);
    if ($_POST['tgl_putusan']!='') {
        $tgl_putusan = $_POST['tgl_putusan'];
    }else{
        $tgl_putusan = NULL;
    }
    if ($_POST['tgl_register']!='') {
        $tgl_register = $_POST['tgl_register'];
    }else{
        $tgl_register = NULL;
    }
    
    $result = array("success"=>false, "error"=>"", "program_id"=>$id, "upload"=>false, "upload_info"=>array("id"=>0,"filename"=>"","filetype"=>""));
    if ($id==0)
    {
        $sql = "INSERT INTO programs 
                (name, source, description, potensi_bisnis, type, budget,operational,benef_name,benef_address,benef_phone,benef_email,benef_orang,benef_unit,pic,uker, uker_wilayah,uker_cabang,creation_date,creation_by,last_update_by,approval_by,nodin_putusan,nomor_persetujuan,nomor_registrasi,tgl_putusan,tgl_register,nomor_bg)VALUES
                ('$name',$source,'$description','$potensi_bisnis',$type,$budget,$operational,'$benef_name','$benef_address','$benef_phone','$benef_email',$benef_orang,$benef_unit,'$pic',$uker, $uker_wilayah,$uker_cabang,'$creation_date',".get_user_info("ID").",".get_user_info("ID").",".get_user_info("ID").",'$nodin_putusan','$nomor_persetujuan','$nomor_registrasi','$tgl_putusan,'$tgl_register','$nomor_bg')";
    }else{
        //check if already approve (state=1) and budget change
        //modified saldo also
        if ($state==1&&$budget!=$budget_original){
            $sql = "INSERT INTO saldo (trans_desc, trans_debet,trans_credit,trans_by)VALUES
                    ('Update budget',0,$budget_original,".get_user_info("ID")."),
                    ('Update budget',$budget,0,".get_user_info("ID").")";
            $db_obj->query($sql);
        }
        if($operational!=$operational_original)
        {
            $sql = "INSERT INTO saldo (trans_desc, trans_debet,trans_credit,trans_by)VALUES
                    ('Update operasional',0,$operational_original,".get_user_info("ID")."),
                    ('Update operasional',$operational_original,0,".get_user_info("ID").")";
            $db_obj->query($sql);
        }
        $sql = "UPDATE programs SET name='$name',source=$source,description='$description',potensi_bisnis='$potensi_bisnis',
                type=$type,budget=$budget,operational=$operational,benef_name='$benef_name',benef_address='$benef_address',                
                benef_phone='$benef_phone',benef_email='$benef_email',benef_orang=$benef_orang,benef_unit=$benef_unit,
                pic='$pic',uker=$uker,uker_wilayah=$uker_wilayah,uker_cabang=$uker_cabang,
                creation_date='$creation_date',approval_date='$approval_date',nodin_putusan='$nodin_putusan',
                nomor_persetujuan='$nomor_persetujuan',nomor_registrasi='$nomor_registrasi',
                tgl_putusan='$tgl_putusan',tgl_register='$tgl_register',nomor_bg='$nomor_bg',
                last_update_by=".get_user_info("ID")."
                WHERE id=$id";
    }
    
    if ($db_obj->query($sql))
    {
        //update result array
        $result['success']=true;
        if ($id==0) {
            $id = $db_obj->getLastId ();
            $result['program_id'] = $id;
            //update total budget
            $sql = "INSERT INTO saldo (trans_desc,trans_debet,trans_credit,trans_by)VALUES
                    ('Operasional cost',$operational,0,".get_user_info('ID').")";
            $db_obj->query($sql);
        }
        //create log
        logs(get_user_info("USERNAME"), "Update program [name:$name]", $db_obj);
        if (isset($_FILES['doc_reference']))
        {
            $result['upload'] = true;
            
            $file = $_FILES['doc_reference'];
            $allowable_ext = "doc,jpg,pdf";
            //check file extension
            $file_extension = strtolower(get_file_extension($file['name']));
            if (in_array($file_extension, explode(",",$allowable_ext))){
                $filename = time().".".$file_extension;
                if (move_uploaded_file($file['tmp_name'], "../doc_references/".$filename)){
                    $sql = "INSERT INTO doc_references 
                            (program,filename,filetype)VALUES
                            ($id,'$filename','$file_extension')";
                    $db_obj->query($sql);
                    
                    //update result array                    
                    $result['upload_info']['id'] = $db_obj->getLastId();
                    $result['upload_info']['filename'] = $filename;
                    $result['upload_info']['filetype'] = get_label_docref_type($file_extension);
                    $result['upload_info']['upload_date'] = date("Y-m-d H:i:s");
                    
                    //create log
                    logs(get_user_info("USERNAME"), "Upload dokumen referensi [program:$name][filename:$filename][type:$file_extension]", $db_obj);
                }else{
                    $result['error'] = "Gagal memindahkan file ke server";
                }
            }else $result['error'] = "File format $file_extension tidak didukung";
        }
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function deleteDocRef($doc_id)
{
    $db_obj = new DatabaseConnection();
    $sql = "SELECT filename FROM doc_references WHERE id=$doc_id";
    $filename = $db_obj->singleValueFromQuery($sql);
    if ($filename&&file_exists("../doc_references/".$filename))
    {
        unlink("../doc_references/".$filename);
        $sql = "DELETE FROM doc_references WHERE id=$doc_id";
        $db_obj->query($sql);
        
        echo 1;
        //create log
        logs(get_user_info("USERNAME"), "Hapus dokumen referensi [filename:$filename]", $db_obj);
    }else echo ('0Dokumen tidak ditemukan');
}
function updateProgramStatus($program_id)
{
    $db_obj = new DatabaseConnection();
    $status = $_POST['status'];
    if (isset($_POST['approval_date'])&&$_POST['approval_date']!='')
        $approval_date = $_POST['approval_date'];
    else
        $approval_date = date("Y-m-d H:i:s");
    
    $program = load_program($program_id, $db_obj);
    if ($program)
    {
        $program_caption = $program['name'];
        $program_budget = $program['budget'];
    }else{
        $program_caption = 'Unknown';
        $program_budget = 0;
    }
    
    if ($status==1)
    {   
        $sql = "UPDATE programs SET 
                state=$status,approval_date='$approval_date',approval_by=".get_user_info('ID')."
                WHERE id=$program_id";
    }else{
        $sql = "UPDATE programs SET 
                state=$status,approval_date=NULL,approval_by=".get_user_info('ID')."
                WHERE id=$program_id";
    }
    if ($db_obj->query($sql))
    {
        //update saldo
        if ($status==1) 
        {
            $trans_desc = "Approval";
            $trans_type = "trans_debet";
        }
        else
        {
            $trans_desc = "Membatalkan approval";
            $trans_type = "trans_credit";
        }
        
        $sql = "INSERT INTO saldo(trans_desc,".$trans_type.",trans_by)VALUES
                ('$trans_desc program $program_caption',$program_budget,".get_user_info('ID').")";
        $db_obj->query($sql);
        
        //create log
        logs(get_user_info("USERNAME"), "Update status approval program [program:$program_caption][status:$status]", $db_obj);
        
        echo 1;
    }
    else
        echo ("0".$db_obj->getLastError ());
}
function loadTasks($program_id)
{
    $db_obj = new DatabaseConnection();
    $page = $_POST['page'];
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
        $search_string = $_POST['search_str'];
    $num_of_recs = 10;
    //count all pages
    $sql = "SELECT COUNT(*) FROM tasks WHERE (program=$program_id)";
    if (isset($search_string))
    {
        $sql.=" AND((MATCH(task) AGAINST ('$search_string'))OR(task LIKE '%$search_string%'))";
    }
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT t.id, t.task, t.target, t.completed, ((t.completed/t.target)*100)AS progress, 
                DATE(t.creation_date) as creation_date,u.full_name as creation_by,
                DATE(t.last_update) as last_update, 
                t.creation_by as creation_by_id
                FROM tasks t, users u
                WHERE (t.program=$program_id)AND(t.creation_by=u.id)";
        if (isset($search_string))
            $sql.=" AND ((MATCH(task) AGAINST ('$search_string'))OR(task LIKE '%$search_string%'))";
        $sql.= " LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item){
                $item['target'] = number_format($item['target'],0,",",".");
                $item['completed'] = number_format($item['completed'],0,",",".");
                $item['progress'] = number_format($item['progress'],2,",",".");
                $item['view_by'] = get_user_info("ID");
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
    
}
function saveTasks($program_id)
{
    $db_obj = new DatabaseConnection();
    $id = $_POST['id'];
    $task = mysql_real_escape_string($_POST['task']);
    $target = $_POST['target'];
    $completed = $_POST['completed'];
    
    $result = array("success"=>false, "error"=>"", "task_id"=>$id, "upload"=>false, "upload_info"=>array("id"=>$id,"filename"=>"","filetype"=>""));
    if ($id==0)
    {
        $sql = "INSERT INTO tasks 
                (program, task, target, completed,creation_date,creation_by,last_update_by)VALUES
                ($program_id,'$task',$target,$completed,NOW(),".get_user_info("ID").",".get_user_info("ID").")";
    }else{
        $sql = "UPDATE tasks SET task='$task',target=$target,completed=$completed,
                last_update_by=".get_user_info("ID")."
                WHERE id=$id";
    }
    
    if ($db_obj->query($sql))
    {
        //update result array
        $result['success']=true;
        if ($id==0) {
            $id = $db_obj->getLastId ();
            $result['task_id'] = $id;
        }
        //create log
        logs(get_user_info("USERNAME"), "Update kegiatan [programid:$program_id][kegiatan:$task]", $db_obj);
        if (isset($_FILES['doc_reference']))
        {
            $result['upload'] = true;
            
            $file = $_FILES['doc_reference'];
            $allowable_ext = "doc,jpg,pdf";
            //check file extension
            $file_extension = strtolower(get_file_extension($file['name']));
            if (in_array($file_extension, explode(",",$allowable_ext))){
                $filename = time().".".$file_extension;
                if (move_uploaded_file($file['tmp_name'], "../doc_references/".$filename)){
                    $sql = "INSERT INTO doc_references 
                            (program,task,filename,filetype)VALUES
                            ($program_id,$id,'$filename','$file_extension')";
                    $db_obj->query($sql);
                    
                    //update result array                    
                    $result['upload_info']['id'] = $db_obj->getLastId();
                    $result['upload_info']['filename'] = $filename;
                    $result['upload_info']['filetype'] = get_label_docref_type($file_extension);
                    //create log
                    logs(get_user_info("USERNAME"), "Upload dokumen untuk kegiatan [name:$task][filename:$filename][type:$file_extension]", $db_obj);
                }else{
                    $result['error'] = "Gagal memindahkan file ke server";
                }
            }else $result['error'] = "File format $file_extension tidak didukung";
        }
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function loadUker($wilayah)
{    
    $page = $_POST['page'];
    $keyword = $_POST['keyword'];
    
    $db_obj = new DatabaseConnection();
    $num_of_recs = 10;
    
    if ($wilayah>0)
    {
        $sql = "SELECT COUNT(*)
                FROM uker
                WHERE (wilayah=$wilayah)";
    }else{
        $sql = "SELECT COUNT(*)
                FROM uker
                WHERE (wilayah<>-1)";
    }
    if ($keyword!='')
    {
        $sql.=" AND((uker LIKE '%$keyword%')OR(alamat LIKE '%$keyword%')OR(tipe='$keyword'))";
    }
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages,"start"=>$start, "items"=>array());
    
    if ($found)
    {    
        if ($wilayah>0)
        {
            $sql = "SELECT u.id, u.wilayah, u.cabang, u.uker, u.kode, u.tipe, u.alamat, u.telepon, u.fax, UPPER(p.propinsi) as propinsi, UPPER(k.ibukota) as kabupaten
                    FROM uker u, propinsi p, kabupaten k
                    WHERE (u.wilayah=$wilayah)AND(p.id=u.propinsi)AND(k.id=u.kabupaten)";
        }else{
            $sql = "SELECT u.id, u.wilayah, u.cabang, u.uker, u.kode, u.tipe, u.alamat, u.kota, u.telepon, u.fax, UPPER(p.propinsi) as propinsi, UPPER(k.ibukota) as kabupaten
                    FROM uker u, propinsi p, kabupaten k
                    WHERE (u.wilayah<>-1)AND(p.id=u.propinsi)AND(k.id=u.kabupaten)";
        }    
        if ($keyword!='')
        {
            $sql.=" AND((u.uker LIKE '%$keyword%')OR(u.alamat LIKE '%$keyword%')OR(u.tipe='$keyword'))";
        }
        //add control page
        $sql.="LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);
        
        if ($result){
            $data['items'] = $result;        
        }else{
            $data['error'] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
}
function loadKabupaten($propinsi)
{
    $db_obj = new DatabaseConnection();
    $db_obj = new DatabaseConnection();
    $result = load_kabupaten($propinsi,$db_obj);
    
    $data = array("found"=>0,"error"=>'', "items"=>array());
    if ($result){
        $data['items'] = $result;
        $data['found'] = count($result);
    }else{
        $data['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($data);
}
function get_kanwil_id($kanwil, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    $sql = "SELECT id FROM uker WHERE (wilayah=$kanwil)AND(tipe='KW')";
    $found = $db_obj->singleValueFromQuery($sql);
    if (!$found)
        $found = 1;
    
    return $found;
}
function saveUker($mode)
{
    $db_obj = new DatabaseConnection();
    $id = $_POST['id'];
    $tipe = $_POST['tipe'];
    $kanwil = $_POST['kanwil'];
    if ($tipe=='KP'){
        //check if Kantor Pusat exists because should be only one KP
        $sql = "SELECT COUNT(*) FROM uker WHERE tipe='KP'";
        $found = $db_obj->singleValueFromQuery($sql);
        if ($found>0){
            echo "0Kantor pusat sudah ada di database";
            exit;
        }else{
            $sql = "SELECT MAX(wilayah)+1 FROM uker";
            $kanwil = $db_obj->singleValueFromQuery($sql);
            $kancab = 0;
        }
    }
    if ($kanwil==0&&$tipe=='KW')
    {
        $sql = "SELECT MAX(wilayah)+1 FROM uker";
        $kanwil = $db_obj->singleValueFromQuery($sql);
    }
    $kancab = $_POST['kancab'];
    if ($kancab==0&&$tipe=='KC')
    {
        $sql = "SELECT MAX(cabang)+1 FROM uker WHERE (wilayah=$kanwil)";
        $kancab = $db_obj->singleValueFromQuery($sql);
    }
    $parent = 0;
    switch($tipe)
    {
        case 'KP':
        case 'KW': $parent = 0; break;
        case 'KANINS': 
        case 'KC': $parent = get_kanwil_id($kanwil, $db_obj); break;
        default: $parent = $kancab; break;
    }
    $uker = mysql_real_escape_string($_POST['uker']);
    $kode = $_POST['kode'];
    $alamat = mysql_real_escape_string($_POST['alamat']);
    $kabupaten = $_POST['kota'];
    $propinsi = $_POST['propinsi'];
    $telepon = mysql_real_escape_string($_POST['telepon']);
    $fax = mysql_real_escape_string($_POST['fax']);
    
    $result = array("success"=>false, "error"=>"", "id"=>$id);
    if ($id==0){
        $sql = "INSERT INTO uker (parent,wilayah,cabang,kode,uker,tipe,alamat,kabupaten,propinsi,telepon,fax)VALUES
                ($parent,$kanwil,$kancab,'$kode','$uker','$tipe','$alamat',$kabupaten,$propinsi,'$telepon','$fax')";
    }else{
        $sql = "UPDATE uker SET parent=$parent,wilayah=$kanwil,cabang=$kancab,
                kode='$kode',uker='$uker',tipe='$tipe',alamat='$alamat',
                kabupaten=$kabupaten,propinsi=$propinsi,telepon='$telepon',fax='$fax'
                WHERE id=$id";
    }
    if($db_obj->query($sql)){
        if ($id==0)
        {
            $id = $db_obj->getLastId();
            $result['id'] = $id;
        }
        $result['success']=true;
        //create log
        logs(get_user_info("USERNAME"), "Update uker [name:$uker]", $db_obj);
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    echo json_encode($result);
}
function loadReport($mode)
{
    switch($mode)
    {
        case 1: loadReportByWilayah(); break;
        case 2: loadReportByProvince();break;
    }    
}
function loadReportByWilayah()
{
	$unitVal = 1; // before 1000 (dalam ribuan)
    $db_obj = new DatabaseConnection();
    $month_from = $_POST['month_from'];
    $year_from = $_POST['year_from'];
    $month_to = $_POST['month_to'];
    $year_to = $_POST['year_to'];
    $fund_type = $_POST['fund_type']; //alokasi(0) or realisasi(1)
    $recs_perpage = get_sysvar_value("RECORDS_PER_REPORTPAGE");    
    $arr_data = array("found"=>0,"records"=>$recs_perpage,"bidang"=>array());
    //get all bidang
    $sql = "SELECT id, type FROM program_types";
    $bidangs = $db_obj->execSQL($sql);
    //create caption array
    foreach($bidangs as $bidang){
        $arr_data['bidang']['caption'][] = $bidang['type'];
    }
    //get all wilayah
    $sql = "SELECT id, uker, wilayah
            FROM uker
            WHERE(parent=0)AND((tipe='KW')OR(tipe='KP'))
            ORDER BY uker ASC";
    $kanwils = $db_obj->execSQL($sql);
    //add BUMN Perduli
    $kanwils[] = array("id"=>0,"uker"=>"BUMN PERDULI");
    
    $arr_data['found'] = $db_obj->getNumRecord()+1;
    //get all bidang for each wilayah
    $i=0;$page_break=0;
    $subtotal = array();
    $total = array();
    if ($kanwils)foreach($kanwils as $kanwil){
        $arr_data['bidang']['kanwil'][$i]['uker'] = $kanwil['uker']; 
        $arr_data['bidang']['kanwil'][$i]['tot_budget'] = 0; 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_orang'] = 0; 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_unit'] = 0;         
        
        //load data for each bidang
        $tot_budget = 0;
        $tot_benef_orang = 0;
        $tot_benef_unit = 0;
        foreach($bidangs as $bidang){
            $arr_bidang_detail = array("budget"=>0,"benef_orang"=>0, "benef_unit"=>0);            
            
            if ($fund_type==0) //alokasi / approved
            {
                $sql = "SELECT SUM(p.budget) as budget, SUM(p.benef_orang) as benef_orang,SUM(p.benef_unit) as benef_unit
                        FROM programs p
                        WHERE (p.state=1)AND(p.type=".$bidang['id'].")";
            }else{ //realisasi
                $sql = "SELECT SUM((SELECT SUM(nominal) FROM budget_real_used WHERE program=p.id)) as budget, SUM(p.benef_orang) as benef_orang,SUM(p.benef_unit) as benef_unit
                        FROM programs p
                        WHERE (p.state=1)AND(p.type=".$bidang['id'].")";
            }
            //check for BUMN Perduli
            if ($kanwil['id']>0)
            {
                $sql.="AND(p.source=0)AND (p.uker_wilayah=".$kanwil['id'].")";
            }else{
                $sql.="AND(p.source=1)";
            }
            
            if ($month_from!=$month_to)
                $sql.=" AND((MONTH(p.approval_date)>=$month_from)AND(MONTH(p.approval_date)<=$month_to))";
            else
                $sql.=" AND(MONTH(p.approval_date)=$month_from)";
            
            if ($year_from!=$year_to)
                $sql.=" AND((YEAR(p.approval_date)>=$year_from)AND(YEAR(p.approval_date)<=$year_to))";
            else
                $sql.=" AND(YEAR(p.approval_date)=$year_from)";
            
            $result = $db_obj->execSQL($sql);
            
            if ($result)
            {
                if ($result[0]['budget'])
                {
                    $budget = $result[0]['budget']/$unitVal; 
                    $tot_budget+=$budget;                    
                }else{
                    $budget=0;
                }
                if ($result[0]['benef_orang'])
                {
                    $benef_orang = $result[0]['benef_orang']; 
                    $tot_benef_orang+=$benef_orang;                    
                }else{
                    $benef_orang=0;
                }
                if ($result[0]['benef_unit'])
                {
                    $benef_unit = $result[0]['benef_unit'];
                    $tot_benef_unit+=$benef_unit;                    
                }else{
                    $benef_unit=0;
                }
                
                $arr_bidang_detail = array("budget"=>number_format($budget,0,',','.'),"benef_orang"=>number_format($benef_orang,0,',','.'),"benef_unit"=>number_format($benef_unit,0,',','.'));
                
                if (isset($subtotal[$bidang['id']]))
                {
                    $subtotal[$bidang['id']] = array(
                        "budget"=>$subtotal[$bidang['id']]['budget']+$budget,
                        "benef_orang"=>$subtotal[$bidang['id']]['benef_orang']+$benef_orang,
                        "benef_unit"=>$subtotal[$bidang['id']]['benef_unit']+$benef_unit);
                }else{
                    $subtotal[$bidang['id']] = array(
                        "budget"=>$budget,
                        "benef_orang"=>$benef_orang,
                        "benef_unit"=>$benef_unit);
                }
                if (isset($total[$bidang['id']]))
                {
                    $total[$bidang['id']] = array(
                        "budget"=>$total[$bidang['id']]['budget']+$budget,
                        "benef_orang"=>$total[$bidang['id']]['benef_orang']+$benef_orang,
                        "benef_unit"=>$total[$bidang['id']]['benef_unit']+$benef_unit);
                }else{
                    $total[$bidang['id']] = array(
                        "budget"=>$budget,
                        "benef_orang"=>$benef_orang,
                        "benef_unit"=>$benef_unit);
                }
            }
                
            $arr_data['bidang']['kanwil'][$i]['bidang'][] = $arr_bidang_detail;            
        }
        $arr_data['bidang']['kanwil'][$i]['tot_budget'] = number_format($tot_budget,0,",","."); 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_orang'] = number_format($tot_benef_orang,0,',','.'); 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_unit'] = number_format($tot_benef_unit,0,',','.'); 
        
        if (isset($subtotal['total_row']))
        {
            $subtotal['total_row'] = array(
                        "budget"=>$subtotal['total_row']['budget']+$tot_budget,
                        "benef_orang"=>$subtotal['total_row']['benef_orang']+$tot_benef_orang,
                        "benef_unit"=>$subtotal['total_row']['benef_unit']+$tot_benef_unit);
        }else{
            $subtotal['total_row'] = array(
                        "budget"=>$tot_budget,
                        "benef_orang"=>$tot_benef_orang,
                        "benef_unit"=>$tot_benef_unit);
        }
        if(isset($total['total_row']))
        {
            $total['total_row'] = array(
                        "budget"=>$total['total_row']['budget']+$tot_budget,
                        "benef_orang"=>$total['total_row']['benef_orang']+$tot_benef_orang,
                        "benef_unit"=>$total['total_row']['benef_unit']+$tot_benef_unit);
        }else{
            $total['total_row'] = array(
                        "budget"=>$tot_budget,
                        "benef_orang"=>$tot_benef_orang,
                        "benef_unit"=>$tot_benef_unit);
        }
        
        //set page break
        if ($i>0&&$i%$recs_perpage==0)
        {
            foreach($subtotal as $item)
            {
                $item['budget'] = number_format($item['budget'],0,",",".");
                $item['benef_orang'] = number_format($item['benef_orang'],0,",",".");
                $item['benef_unit'] = number_format($item['benef_unit'],0,",",".");
                $arr_data['bidang']['subtotal'][$page_break][] = $item;
            }
            $subtotal = array();
            $page_break++;
        }
        
        $i++;
    }
    foreach($total as $item)
    {
        $item['budget'] = number_format($item['budget'],0,",",".");
        $item['benef_orang'] = number_format($item['benef_orang'],0,",",".");
        $item['benef_unit'] = number_format($item['benef_unit'],0,",",".");
        $arr_data['bidang']['total'][] = $item;
    }
    echo json_encode($arr_data);
}
function loadReportByProvince()
{
	$unitVal = 1; // before 1000 (dalam ribuan)
    $db_obj = new DatabaseConnection();
    $month_from = $_POST['month_from'];
    $year_from = $_POST['year_from'];
    $month_to = $_POST['month_to'];
    $year_to = $_POST['year_to'];
    $fund_type = $_POST['fund_type']; //alokasi(0) or realisasi(1)
    $recs_perpage = get_sysvar_value("RECORDS_PER_REPORTPAGE");
    $arr_data = array("found"=>0,"records"=>$recs_perpage,"bidang"=>array());
    
    //get all bidang
    $sql = "SELECT id, type FROM program_types";
    $bidangs = $db_obj->execSQL($sql);
    //create caption array
    foreach($bidangs as $bidang){
        $arr_data['bidang']['caption'][] = $bidang['type'];
    }
    //get all wilayah
    $sql = "SELECT id, propinsi as uker
            FROM propinsi
            ORDER BY propinsi ASC";
    $provinces = $db_obj->execSQL($sql);
    //add BUMN Perduli
    $provinces[] = array("id"=>0,"uker"=>"BUMN PERDULI");
    
    $arr_data['found'] = $db_obj->getNumRecord()+1;
    //get all bidang for each wilayah
    $i=0;$page_break=0;
    $subtotal = array();
    $total = array();
    if ($provinces)foreach($provinces as $propinsi){
        $arr_data['bidang']['kanwil'][$i]['uker'] = $propinsi['uker'];       
        $arr_data['bidang']['kanwil'][$i]['tot_budget'] = 0; 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_orang'] = 0; 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_unit'] = 0; 
        //load data for each bidang
        $tot_budget = 0;
        $tot_benef_orang = 0;
        $tot_benef_unit = 0;
        foreach($bidangs as $bidang){
            $arr_bidang_detail = array("budget"=>0,"benef_orang"=>0, "benef_unit"=>0);
            
            if ($fund_type==0) //alokasi / approved
            {
                $sql = "SELECT SUM(p.budget) as budget, SUM(p.benef_orang) as benef_orang,SUM(p.benef_unit) as benef_unit
                        FROM programs p, uker u
                        WHERE (p.state=1)AND(p.type=".$bidang['id'].")AND(p.uker=u.id)";
            }else{ //realisasi
                $sql = "SELECT SUM((SELECT SUM(nominal) FROM budget_real_used WHERE program=p.id)) as budget, SUM(p.benef_orang) as benef_orang,SUM(p.benef_unit) as benef_unit
                        FROM programs p, uker u
                        WHERE (p.state=1)AND(p.type=".$bidang['id'].")AND(p.uker=u.id)";
            }
            
            //check for BUMN Perduli
            if ($propinsi['id']>0)
            {
                $sql.="AND(p.source=0)AND(u.propinsi=".$propinsi['id'].")";
            }else{
                $sql.="AND(p.source=1)";
            }
            
            if ($month_from!=$month_to)
                $sql.=" AND((MONTH(p.approval_date)>=$month_from)AND(MONTH(p.approval_date)<=$month_to))";
            else
                $sql.=" AND(MONTH(p.approval_date)=$month_from)";
            
            if ($year_from!=$year_to)
                $sql.=" AND((YEAR(p.approval_date)>=$year_from)AND(YEAR(p.approval_date)<=$year_to))";
            else
                $sql.=" AND(YEAR(p.approval_date)=$year_from)";
            
            $result = $db_obj->execSQL($sql);
            
            if ($result)
            {
                if ($result[0]['budget'])
                {
                    $budget = $result[0]['budget']/$unitVal; 
                    $tot_budget+=$budget;                    
                }else{
                    $budget=0;
                }
                if ($result[0]['benef_orang'])
                {
                    $benef_orang =$result[0]['benef_orang']; 
                    $tot_benef_orang+=$benef_orang;                    
                }else{
                    $benef_orang=0;
                }
                if ($result[0]['benef_unit'])
                {
                    $benef_unit = $result[0]['benef_unit']; 
                    $tot_benef_unit+=$benef_unit;                    
                }else{
                    $benef_unit=0;
                }
                
                $arr_bidang_detail = array(
                    "budget"=>number_format($budget,0,',','.'),
                    "benef_orang"=>number_format($benef_orang,0,',','.'),
                    "benef_unit"=>number_format($benef_unit,0,',','.'));
                if(isset($subtotal[$bidang['id']]))
                {
                    $subtotal[$bidang['id']] = array(
                        "budget"=>$subtotal[$bidang['id']]['budget']+$budget,
                        "benef_orang"=>$subtotal[$bidang['id']]['benef_orang']+$benef_orang,
                        "benef_unit"=>$subtotal[$bidang['id']]['benef_unit']+$benef_unit);
                }else{
                    $subtotal[$bidang['id']] = array(
                        "budget"=>$budget,
                        "benef_orang"=>$benef_orang,
                        "benef_unit"=>$benef_unit);
                }
                if(isset($total[$bidang['id']]))
                {
                    $total[$bidang['id']] = array(
                        "budget"=>$total[$bidang['id']]['budget']+$budget,
                        "benef_orang"=>$total[$bidang['id']]['benef_orang']+$benef_orang,
                        "benef_unit"=>$total[$bidang['id']]['benef_unit']+$benef_unit);
                }else{
                    $total[$bidang['id']] = array(
                        "budget"=>$budget,
                        "benef_orang"=>$benef_orang,
                        "benef_unit"=>$benef_unit);
                }
            }
                
            $arr_data['bidang']['kanwil'][$i]['bidang'][] = $arr_bidang_detail;
        }
        $arr_data['bidang']['kanwil'][$i]['tot_budget'] = number_format($tot_budget,0,",","."); 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_orang'] = number_format($tot_benef_orang,0,',','.'); 
        $arr_data['bidang']['kanwil'][$i]['tot_benef_unit'] = number_format($tot_benef_unit,0,',','.'); 
        
        if(isset($subtotal['total_row']))
        {
            $subtotal['total_row'] = array(
                        "budget"=>$subtotal['total_row']['budget']+$tot_budget,
                        "benef_orang"=>$subtotal['total_row']['benef_orang']+$tot_benef_orang,
                        "benef_unit"=>$subtotal['total_row']['benef_unit']+$tot_benef_unit);            
        }else{
            $subtotal['total_row'] = array(
                        "budget"=>$tot_budget,
                        "benef_orang"=>$tot_benef_orang,
                        "benef_unit"=>$tot_benef_unit);
        }
        if (isset($total['total_row']))
        {
            $total['total_row'] = array(
                        "budget"=>$total['total_row']['budget']+$tot_budget,
                        "benef_orang"=>$total['total_row']['benef_orang']+$tot_benef_orang,
                        "benef_unit"=>$total['total_row']['benef_unit']+$tot_benef_unit);
        }else{
            $total['total_row'] = array(
                        "budget"=>$tot_budget,
                        "benef_orang"=>$tot_benef_orang,
                        "benef_unit"=>$tot_benef_unit);
        }
        
        //set page break
        if ($i>0&&$i%$recs_perpage==0)
        {
            foreach($subtotal as $item)
            {
                $item['budget'] = number_format($item['budget'],0,",",".");
                $item['benef_orang'] = number_format($item['benef_orang'],0,",",".");
                $item['benef_unit'] = number_format($item['benef_unit'],0,",",".");
                $arr_data['bidang']['subtotal'][$page_break][] = $item;
            }
            $subtotal = array();
            $page_break++;
        }
        
        $i++;
    }
    foreach($total as $item)
    {
        $item['budget'] = number_format($item['budget'],0,",",".");
        $item['benef_orang'] = number_format($item['benef_orang'],0,",",".");
        $item['benef_unit'] = number_format($item['benef_unit'],0,",",".");
        $arr_data['bidang']['total'][] = $item;
    }
    echo json_encode($arr_data);
}
function loadReportDetail($mode)
{
    switch($mode)
    {
        case 0: loadReportDetailByWilayah(); break;
        case 1: loadReportDetailByProvince();break;
    }  
}
function loadReportDetailByWilayah()
{
    $db_obj = new DatabaseConnection();
    $month_from = $_POST['month_from'];
    $year_from = $_POST['year_from'];
    $month_to = $_POST['month_to'];
    $year_to = $_POST['year_to'];
    $area = $_POST['area'];
    
    if (get_sysvar_value("RECORDS_PER_REPORTDETAILPAGE"))
        $recs_perpage = get_sysvar_value("RECORDS_PER_REPORTDETAILPAGE");
    else
        $recs_perpage = 10;
    $arr_data = array("found"=>array(),"records"=>$recs_perpage,"bidang"=>array(),"kanwil"=>array());
    //get all bidang
    $sql = "SELECT id, type FROM program_types";
    $bidangs = $db_obj->execSQL($sql);
    //create caption array
    foreach($bidangs as $bidang){
        $arr_data['bidang'][] = $bidang['type'];
    }
    //get all wilayah
    $sql = "SELECT id, uker, wilayah
            FROM uker
            WHERE(parent=0)AND((tipe='KW')OR(tipe='KP'))";
    if ($area>0)
        $sql.="AND(id=$area)";
    $sql.=" ORDER BY uker ASC";
    $kanwils = $db_obj->execSQL($sql);
    //add BUMN Perduli if show all
    if ($area==0)
        $kanwils[] = array("id"=>0,"uker"=>"BUMN PERDULI");
    
    //save kanwil id and caption into array
    $arr_data['kanwil']['uker'] = $kanwils;
    
    $arr_data['found']['kanwil'] = $db_obj->getNumRecord()+1;
    
    foreach($kanwils as $kanwil)
    {        
        $sql = "SELECT p.id, p.name, p.pic, u.uker, u.tipe as 'jenis_uker', p.uker as 'uker_id',
                p.benef_name,  p.benef_address, p.benef_phone, p.benef_email, 
                p.benef_orang, p.benef_unit, 
                p.budget, p.operational, p.type, p.source,p.description,p.potensi_bisnis,
                DATE(p.approval_date) AS 'approval_date_en'
                FROM programs p, uker u
                WHERE (p.uker=u.id)
                AND(p.state=1)";
        if ($month_to > $month_from)
        {
            $sql.=" AND(MONTH(p.approval_date)>=$month_from)AND(MONTH(p.approval_date)<=$month_to)";
        }else{
            $sql.=" AND(MONTH(p.approval_date)=$month_from)";
        }
        if ($year_to > $year_from)
        {
            $sql.=" AND(YEAR(p.approval_date)>=$year_from)AND(YEAR(p.approval_date)<=$year_to)";
        }else{
            $sql.=" AND(YEAR(p.approval_date)=$year_from)";
        }
        //check BUMN Perduli
        if ($kanwil['id']>0)
        {
            $sql.="AND(p.uker_wilayah=".$kanwil['id'].")
                    AND(p.source=0)";
        }else{
            $sql.="AND(p.source=1)";
        }
        $sql.=" ORDER BY p.uker, p.approval_date DESC";
        
        $programs_kanwil = $db_obj->execSQL($sql);
        if ($programs_kanwil)
        {
            
            foreach($programs_kanwil as $programs_kanwil_item)
            {                
                $approval_date = date_create($programs_kanwil_item['approval_date_en']);
                $programs_kanwil_item['approval_date'] = date_format($approval_date, "d-m-Y");
                foreach($bidangs as $bidang)
                {
                    $bidang_detail = array();
                    if ($programs_kanwil_item['type']==$bidang['id'])
                    {
                        $bidang_detail['real'] = program_real_fund_used($programs_kanwil_item['id'], $db_obj);
                        $bidang_detail['benef_orang'] = $programs_kanwil_item['benef_orang'];
                        $bidang_detail['benef_unit'] = $programs_kanwil_item['benef_unit'];
                    }
                    else
                    {
                        $bidang_detail['real'] = 0;
                        $bidang_detail['benef_orang'] = 0;
                        $bidang_detail['benef_unit'] = 0;
                    }
                    //count/sum real fund used
                    if (isset($programs_kanwil_item['real'])){
                        $programs_kanwil_item['real'] += $bidang_detail['real'];
                    }else{
                        $programs_kanwil_item['real'] = $bidang_detail['real'];
                    }
                    $programs_kanwil_item['bidang'][] = $bidang_detail;
                }
                $arr_data['kanwil']['items'][$kanwil['id']][] = $programs_kanwil_item;
            }
        }else{
            $arr_data['kanwil']['items'][$kanwil['id']] = null;
        }
    }
    echo json_encode($arr_data);
}
function loadReportDetailByProvince()
{
    $db_obj = new DatabaseConnection();
    $month_from = $_POST['month_from'];
    $year_from = $_POST['year_from'];
    $month_to = $_POST['month_to'];
    $year_to = $_POST['year_to'];
    $area = $_POST['area'];
    
    if (get_sysvar_value("RECORDS_PER_REPORTDETAILPAGE"))
        $recs_perpage = get_sysvar_value("RECORDS_PER_REPORTDETAILPAGE");
    else
        $recs_perpage = 10;  
    $arr_data = array("found"=>array(),"records"=>$recs_perpage,"bidang"=>array(),"kanwil"=>array());
    //get all bidang
    $sql = "SELECT id, type FROM program_types";
    $bidangs = $db_obj->execSQL($sql);
    //create caption array
    foreach($bidangs as $bidang){
        $arr_data['bidang'][] = $bidang['type'];
    }
    //get all wilayah
    $sql = "SELECT id, propinsi as uker
            FROM propinsi";
    if ($area>0)
        $sql.=" WHERE(id=$area)";
    $sql.=" ORDER BY propinsi ASC";
    
    $provinces = $db_obj->execSQL($sql);
    //add BUMN Perduli only if show all
    if ($area==0)
        $provinces[] = array("id"=>0,"uker"=>"BUMN PERDULI");
    
    //save kanwil id and caption into array
    $arr_data['kanwil']['uker'] = $provinces;
    
    $arr_data['found']['kanwil'] = $db_obj->getNumRecord()+1;
    
    foreach($provinces as $province)
    {
        $sql = "SELECT p.id, p.name, p.pic, u.uker, u.tipe as 'jenis_uker', p.uker as 'uker_id',
                p.benef_name, p.benef_address, p.benef_phone, p.benef_email, 
                p.benef_orang, p.benef_unit, 
                p.budget, p.operational, p.type, p.description,p.potensi_bisnis,
                DATE(p.approval_date) AS 'approval_date_en'
                FROM programs p, uker u
                WHERE (p.uker=u.id)
                AND(p.state=1)";
        
        if ($month_to > $month_from)
        {
            $sql.=" AND(MONTH(p.approval_date)>=$month_from)AND(MONTH(p.approval_date)<=$month_to)";
        }else{
            $sql.=" AND(MONTH(p.approval_date)=$month_from)";
        }
        if ($year_to > $year_from)
        {
            $sql.=" AND(YEAR(p.approval_date)>=$year_from)AND(YEAR(p.approval_date)<=$year_to)";
        }else{
            $sql.=" AND(YEAR(p.approval_date)=$year_from)";
        }
        
        //check BUMN Perduli
        if ($province['id']>0)
        {
            $sql.="AND(p.source=0)AND(u.propinsi=".$province['id'].")";
        }else{
            $sql.="AND(p.source=1)";
        }
        $sql.=" ORDER BY p.uker, p.approval_date DESC";
        
        $programs_provinces = $db_obj->execSQL($sql);
        if ($programs_provinces)
        {
            
            foreach($programs_provinces as $programs_provinces_item)
            {                
                $approval_date = date_create($programs_provinces_item['approval_date_en']);
                $programs_provinces_item['approval_date'] = date_format($approval_date, "d-m-Y");
                foreach($bidangs as $bidang)
                {
                    $bidang_detail = array();
                    if ($programs_provinces_item['type']==$bidang['id'])
                    {
                        $bidang_detail['real'] = program_real_fund_used($programs_provinces_item['id'], $db_obj);
                        $bidang_detail['benef_unit'] = $programs_provinces_item['benef_unit'];
                        $bidang_detail['benef_orang'] = $programs_provinces_item['benef_orang'];
                    }
                    else
                    {
                        $bidang_detail['real'] = 0;
                        $bidang_detail['benef_unit'] = 0;
                        $bidang_detail['benef_orang'] = 0;
                    }
                    
                    if (isset($programs_provinces_item['real'])){
                        $programs_provinces_item['real'] += $bidang_detail['real'];
                    }else{
                        $programs_provinces_item['real'] = $bidang_detail['real'];
                    }
                    $programs_provinces_item['bidang'][] = $bidang_detail;
                }
                
                $arr_data['kanwil']['items'][$province['id']][] = $programs_provinces_item;
            }
        }else{
            $arr_data['kanwil']['items'][$province['id']] = null;
        }
    }
    echo json_encode($arr_data);
}
function loadProgramsByWilayah($page)
{
    $db_obj = new DatabaseConnection();
    $wilayah = $_POST['wilayah'];
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $num_of_recs = 7;
    $state = $_POST['state'];
    
    //count all pages
    if ($wilayah>0)
        $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker_wilayah=$wilayah)AND(p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)";
    else
        $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)";
    if (isset($search_string))
    {
        $sql.=" AND((MATCH(name,description) AGAINST ('$search_string'))OR(name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')
                OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')";
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
        $sql.=")";
    }
    if ($state>=0){
        $sql.=" AND(p.state=$state)";
    }
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)";
        if ($wilayah>0)
            $sql.= " AND(p.uker_wilayah=$wilayah)";
        
        if (isset($search_string))
        {
            $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                    OR(u.uker LIKE '%$search_string%')
                    OR(k.kabupaten LIKE '%$search_string%')
                    OR(k.ibukota LIKE '%$search_string%')
                    OR(pr.propinsi LIKE '%$search_string%')
                    OR(pr.ibukota LIKE '%$search_string%')
                    OR(p.benef_name LIKE '%$search_string%')";
            if ($kanwil_id_like)
                $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
            $sql.=")";
        }
        if ($state>=0){
            $sql.=" AND(p.state=$state)";
        }
        $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC
                LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item)
            {
                $item['progress'] = number_format(program_progress($item['id'], $db_obj),2,",",".");
                $item['real_used'] = number_format(program_real_fund_used($item['id'], $db_obj),2,",",".");
                $item['view_by'] = get_user_info("ID");
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
    
}
function loadProgramsByCreator($page)
{
    $db_obj = new DatabaseConnection();
    $creator = $_POST['person'];
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $num_of_recs = 7;
    //count all pages
    if ($creator>0)
        $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=$creator)";
    else
        $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)";
    if (isset($search_string))
    {
        $sql.=" AND((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')
                OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')";
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
        $sql.=")";
    }
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)";
        if ($creator>0)
            $sql.= " AND(p.creation_by=$creator)";
        
        if (isset($search_string))
        {
            $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                    OR(u.uker LIKE '%$search_string%')
                    OR(k.kabupaten LIKE '%$search_string%')
                    OR(k.ibukota LIKE '%$search_string%')
                    OR(pr.propinsi LIKE '%$search_string%')
                    OR(pr.ibukota LIKE '%$search_string%')
                    OR(p.benef_name LIKE '%$search_string%')";
            if ($kanwil_id_like)
                $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
            $sql.=")";
        }
        $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC
                LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item)
            {
                $item['progress'] = number_format(program_progress($item['id'], $db_obj),2,",",".");
                $item['real_used'] = number_format(program_real_fund_used($item['id'], $db_obj),2,",",".");
                $item['view_by'] = get_user_info("ID");
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
    
}
function loadProgramsByPropinsi($page)
{
    $db_obj = new DatabaseConnection();
    $propinsi = $_POST['propinsi'];
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $num_of_recs = 7;
    //count all pages
    if ($propinsi>0)
        $sql = "SELECT COUNT(*) 
                FROM programs p, uker u, kabupaten k, propinsi pr
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(u.propinsi=$propinsi)";
    else
        $sql = "SELECT COUNT(*) FROM programs p, uker u, kabupaten k, propinsi pr WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)";
    if (isset($search_string))
    {
        $sql.=" AND((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')
                OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')";
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
        $sql.=")";
    }
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)";
        if ($propinsi>0)
            $sql.= " AND(u.propinsi=$propinsi)";
        
        if (isset($search_string))
        {
            $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                    OR(u.uker LIKE '%$search_string%')
                    OR(k.kabupaten LIKE '%$search_string%')
                    OR(k.ibukota LIKE '%$search_string%')
                    OR(pr.propinsi LIKE '%$search_string%')
                    OR(pr.ibukota LIKE '%$search_string%')
                    OR(p.benef_name LIKE '%$search_string%')";
            if ($kanwil_id_like)
                $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";
        
            $sql.=")";
        }
        $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC
                LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item)
            {
                $item['progress'] = number_format(program_progress($item['id'], $db_obj),2,",",".");
                $item['real_used'] = number_format(program_real_fund_used($item['id'], $db_obj),2,",",".");
                $item['view_by'] = get_user_info("ID");
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
}
function updateSingleAccess($access_id, $access_name, $access_array, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    if (!is_array($access_array))
        return false;
    $sql = "UPDATE user_access SET supervisor=".$access_array[0].", staf=".$access_array[1]."
            WHERE id=".$access_id;
    
    if ($db_obj->query($sql))
    {
        //create log
        logs(get_user_info("USERNAME"), "Update hak akses [name:$access_name][supervisor:".$access_array[0].",staf:".$access_array[1]."]", $db_obj);
        return true;
    }
    else
        return false;
}
function updateAccess($access_id)
{
    $db_obj = new DatabaseConnection();
    $access_name = mysql_real_escape_string($_POST['var_name']);
    $access = explode(",",$_POST['var_value']);
    if (updateSingleAccess($access_id, $access_name, $access,$db_obj))
        echo 1;
    else
        echo 0;
}
function updateAccessAll($access_id)
{
    $db_obj = new DatabaseConnection();
    //use single update function to update all system variables
    $id_list = explode(",",$access_id);
    $access_name = explode("|",mysql_real_escape_string($_POST['var_name']));
    $access = explode(",",$_POST['var_value']);
    $error = array();
    for($i=0; $i<count($id_list);$i++){
        if (!updateSingleAccess($id_list[$i], $access_name[$i], explode("|",$access[$i]),$db_obj))
            $error [] = 'Gagal update hak akses [access:'.$access_name[$i].'][value:'.$access[$i].']';
    }
    if (count($error)>0)
        echo ('0'.implode(",",$error));
    else
        echo 1;
}
function deletePrograms($id_list)
{
    $db_obj = new DatabaseConnection();
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    //get budget and status
    //if status=1 (approve) create new transaction    
    $sql = "SELECT id, name, budget, state FROM programs
            WHERE id IN (".$id_list.")";
    $result = $db_obj->execSQL($sql);
    if ($result)
    {   
        //delete additional links data
        foreach($result as $program){
            //get any fund used (realisasi)
            $sql = "SELECT SUM(nominal) FROM budget_real_used WHERE (program=".$program['id'].")";
            $fund_used = $db_obj->singleValueFromQuery($sql);
            //if fund used > 0, create new credit transaction in table saldo_real
            if ($fund_used)
            {
                $sql = "INSERT INTO saldo_real(trans_desc, trans_credit, trans_by)VALUES
                        ('Refund: Hapus program ".$program['name']."',$fund_used,".get_user_info("ID").")";
                $db_obj->query($sql);
            }
            //remove the fund used record for this program
            $sql = "DELETE FROM budget_real_used WHERE (program=".$program['id'].")";
            $db_obj->query($sql);
            
            //update alokasi saldo if any and it is an approved programs
            if ($program['state']==1&&$program['budget']>0)
            {
                $sql = "INSERT INTO saldo(trans_desc, trans_credit, trans_by)VALUES
                        ('Refund: Hapus program ".$program['name']."',".$program['budget'].",".get_user_info("ID").")";
                $db_obj->query($sql);
            }
            
            //delete tasks if exists
            $sql = "DELETE FROM tasks WHERE program=".$program['id'];
            $db_obj->query($sql);
            
            //SELECT image for this program
            $sql = "SELECT filename FROM doc_references WHERE (program=".$program['id'].")";
            $doc_refs = $db_obj->execSQL($sql);
            if ($doc_refs)
            {
                foreach($doc_refs as $image)
                {
                    //only delete if file exists
                    if (file_exists("../doc_references/".$image['filename']))
                    {
                        if (!unlink("../doc_references/".$image['filename'])){
                            $return_array["error_message"][]="Gagal menghapus file ".$image['filename'];
                        }
                    }else{
                        $return_array["error_message"][]="File ".$image['filename']." tidak ditemukan";
                    }
                }
            }
            //DELETE image for this program
            $sql = "DELETE FROM doc_references WHERE (program=".$program['id'].")";
            $db_obj->query($sql);
            
            //delete the records of program
            $sql = "DELETE FROM programs WHERE id IN (".$id_list.")";
            if ($db_obj->query($sql))
            {
                $return_array["success_id"][]=$program['id'];
                //create log
                logs(get_user_info("USERNAME"), "Hapus program [name:".$program['name']."] dan seluruh detilnya", $db_obj);
            }
            else
            {
                $return_array["error_id"][]=$program['id'];
                $return_array["error_message"][]=$db_obj->getLastError();
            }
        }
    }
    
    echo json_encode($return_array);
}
function loadRealBudget($program_id)
{
    $db_obj = new DatabaseConnection();
    $page = $_POST['page'];
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
        $search_string = $_POST['search_str'];
    $num_of_recs = 10;
    //count all pages
    $sql = "SELECT COUNT(*) FROM budget_real_used WHERE (program=$program_id)";
    if (isset($search_string))
    {
        $sql.=" AND((MATCH(caption) AGAINST ('$search_string'))OR(caption LIKE '%$search_string%'))";
    }
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT r.id, r.caption, r.nominal, DATE(r.creation_date) as creation_date,
                u.full_name as creation_by,
                DATE(r.last_update) as last_update,
                r.creation_by as creation_by_id
                FROM budget_real_used r, users u
                WHERE (r.program=$program_id)AND(r.creation_by=u.id)";
        if (isset($search_string))
            $sql.=" AND ((MATCH(caption) AGAINST ('$search_string'))OR(caption LIKE '%$search_string%'))";
        $sql.= " LIMIT $start, $num_of_recs";
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            $arr = array();
            foreach($result as $item){
                $item['nominal'] = number_format($item['nominal'],2,",",".");
                $item['view_by'] = get_user_info("ID");
                $arr[] = $item;
            }
            $data['items'] = $arr;
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($data);
}
function saveRealBudget($program_id)
{
    $db_obj = new DatabaseConnection();
    $id = $_POST['id'];
    $caption = mysql_real_escape_string($_POST['caption']);
    $nominal = $_POST['nominal'];
    $nominal_original = $_POST['nominal_original'];
    
    $result = array("success"=>false, "error"=>"", "real_id"=>$id);
    if ($id==0)
    {
        $sql = "INSERT INTO budget_real_used 
                (program, caption, nominal, creation_date,creation_by,last_update_by)VALUES
                ($program_id,'$caption',$nominal,NOW(),".get_user_info("ID").",".get_user_info("ID").")";
    }else{
        $sql = "UPDATE budget_real_used SET caption='$caption',nominal=$nominal,
                last_update_by=".get_user_info("ID")."
                WHERE id=$id";
    }
    
    if ($db_obj->query($sql))
    {        
        //update result array
        $result['success']=true;
        if ($id==0)
            $id = $db_obj->getLastId ();
        $result['real_id'] = $id;
        //create log
        logs(get_user_info("USERNAME"), "Update realisasi anggaran[nama:$caption][nilai:$nominal]", $db_obj);
        //update saldo realisasi
        if ($nominal_original>0)
        {
            $sql = "INSERT INTO saldo_real (trans_desc, trans_debet,trans_credit,trans_by)VALUES
                    ('Update realisasi',0,$nominal_original,".get_user_info("ID")."),
                    ('Update realisasi',$nominal,0,".get_user_info("ID").")";
        }else{
            $sql = "INSERT INTO saldo_real (trans_desc, trans_debet,trans_credit,trans_by)VALUES
                    ('Update realisasi',$nominal,0,".get_user_info("ID").")";
        }
        $db_obj->query($sql);
        
        //check if number of realisation no more than approved
        if (program_real_fund_used($program_id, $db_obj)>  program_real_fund_used($program_id, $db_obj))
            $result['error'] = "Dana realisasi lebih besar dari yang disetujui";
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function accountUpdate($id)
{
    $db_obj = new DatabaseConnection();
    $user_name = $_POST['user_name'];
    $full_name = $_POST['full_name'];
    $position = $_POST['position'];
    if (isset($_POST['access']))
        $access = $_POST['access'];
    else
        $access = $_POST['access_ori'];
    $password = $_POST['password'];
    
    if (strlen($password)>0)
    {
        $password = password_rehash($password);
    }
    $result = array("success"=>false, "error"=>"", "id"=>$id, "upload"=>false, "filename"=>"");
    
    if ($id==0)
    {
        if (strlen($password)>0)
        {
            $sql = "INSERT INTO users (user_name, password, full_name, position, 
                    access, login_status, created_on)VALUES
                    ('$user_name','$password','$full_name','$position',
                    $access, 0, NOW())";
        }else{
            $result['error'] = 'Password untuk user tidak boleh kosong';
        }
    }else{
        if (strlen($password)>0)
        {
            $sql = "UPDATE users SET user_name='$user_name',full_name='$full_name', position='$position', 
                    access=$access, password='$password'
                    WHERE id=$id";
        }else{
            $sql = "UPDATE users SET user_name='$user_name', full_name='$full_name', position='$position', 
                    access=$access
                    WHERE id=$id";
        }
    }
    if ($result['error']==''){
        if ($db_obj->query($sql))
        {            
            $result['success'] = true;
            if ($id==0)
            {
                $id = $db_obj->getLastId();
                $result['id'] = $id;
            }
            
            if($id!= get_user_info("ID"))
                logs(get_user_info("USERNAME"), "Update account lain[username:$user_name]", $db_obj);
            else
                logs(get_user_info("USERNAME"), "Update account sendiri", $db_obj);
            
            if (isset($_FILES['avatar']))
            {
                $result['upload'] = true;

                $file = $_FILES['avatar'];
                $allowable_ext = "jpg";
                //check file extension
                $file_extension = strtolower(get_file_extension($file['name']));
                if (in_array($file_extension, explode(",",$allowable_ext))){
                    $filename = time().".".$file_extension;
                    if (move_uploaded_file($file['tmp_name'], "../customs/profile/photo/".$filename)){
                        //resize image
                        if (!resize_image("../customs/profile/photo/".$filename, "../customs/profile/photo/".$filename, 70, 80, 100))
                            $result['error'] = "Gagal melakukan resize image";
                        //update result array
                        $result['filename'] = PROFILE_URL.$filename;
                        
                        //remove old foto file (not default.jpg) if exists
                        $sql = "SELECT avatar FROM users WHERE id=$id";
                        $avatar = $db_obj->singleValueFromQuery($sql);
                        if ($avatar!='' && $avatar!='default.jpg')
                        {
                            if (file_exists("../customs/profile/photo/".$avatar))
                                unlink("../customs/profile/photo/".$avatar);
                        }
                        $sql = "UPDATE users SET avatar='$filename' WHERE id=$id";
                        $db_obj->query($sql);
                        if ($id==  get_user_info("ID"))
                            $_SESSION['BRI_CSR']['USERS']['AVATAR'] = $filename;
                        //create log
                        logs(get_user_info("USERNAME"), "Upload photo profil [user:$user_name][filename:$filename][type:$file_extension]", $db_obj);
                    }else{
                        $result['error'] = "Gagal memindahkan file ke server";
                    }
                }else $result['error'] = "File format $file_extension tidak didukung";
            }
        }else{
            $result['error'] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($result);
}
function creditSaldo()
{
    $db_obj = new DatabaseConnection();
    
    $trans_desc = mysql_real_escape_string($_POST['trans_desc']);
    $trans_credit = $_POST['trans_credit'];
    
    $sql = "INSERT INTO saldo (trans_desc, trans_credit, trans_by)VALUES
           ('$trans_desc',$trans_credit,".get_user_info("ID").")";
    
    $db_obj->query($sql);
    
    $sql = "INSERT INTO saldo_real (trans_desc, trans_credit, trans_by)VALUES
           ('$trans_desc',$trans_credit,".get_user_info("ID").")";
    
    $db_obj->query($sql);
    logs(get_user_info("USERNAME"), "Tambah saldo kredit [nama:$trans_desc][nilai:$trans_credit]", $db_obj);
    echo 1;
}
function updateSingleSystemValue($var_id, $var_name, $new_value, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj)$db_obj = new DatabaseConnection();
    
    $sql = "UPDATE sysvars SET var_value='".$new_value."'
            WHERE id=".$var_id;
    
    if ($db_obj->query($sql))
    {
        //update session if exist
        $_SESSION['BRI_CSR']['SYSVARS'][$var_name] = $new_value;
        logs(get_user_info("USERNAME"), "Update sytem variable [var:$var_name][value:$new_value]", $db_obj);
        return true;
    }
    else
    {
        return false;
    }
}
function updateSysVar($var_id)
{
    $db_obj = new DatabaseConnection();
    $var_name = mysql_real_escape_string($_POST['var_name']);
    $new_val = mysql_real_escape_string(strip_tags($_POST['var_value']));
    if(updateSingleSystemValue($var_id, $var_name, $new_val, $db_obj))
    {
        echo 1;        
    }
    else
        echo ('0'.$db_obj->getLastError ());
    
    sleep(3);
}
function updateSysVarAll($var_id_list)
{
    $db_obj = new DatabaseConnection();
    $id_list = explode(",",$var_id_list);
    $var_name_list =  explode("|",  mysql_real_escape_string($_POST['var_name']));
    $new_value_list = explode("|", mysql_real_escape_string($_POST['var_value']));
    //use single update function to update all system variables
    $error = array();
    for($i=0; $i<count($id_list);$i++){
        if (!updateSingleSystemValue($id_list[$i], $var_name_list[$i], strip_tags($new_value_list[$i]),$db_obj))
            $error [] = 'Gagal update sysvar [var_name:'.$var_name_list[$i].'][value:'.$new_value_list[$i].']';
    }
    if (count($error)>0)
        echo ('0'.implode(",",$error));
    else
        echo 1;
}
function loadLogs($page)
{
    $db_obj = new DatabaseConnection();
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
        $search_string = $_POST['search_str'];
    
    $num_of_recs = 9;
    //count all pages
    $sql = "SELECT COUNT(*) FROM logs WHERE (1=1)";
    if(isset($search_string))
        $sql.=" AND((MATCH(page,request,action) AGAINST ('$search_string'))OR(username LIKE '%$search_string%'))";
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT id,log_date,ip_address,username,page,request,action
                FROM logs WHERE (1=1)";
        if (isset($search_string))
            $sql.=" AND((MATCH(page,request,action) AGAINST ('$search_string'))OR(username LIKE '%$search_string%'))";
        
        $sql.= " ORDER BY log_date DESC
                LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            foreach($result as $item)
            {
                $data['items'][] = $item;
            }
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    echo json_encode($data);
}
function deleteLogs($id_list)
{
    $db_obj = new DatabaseConnection();
    $sql = "DELETE FROM logs WHERE id IN(".$id_list.")";
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    if ($db_obj->query($sql))
    {
        $return_array['success_id'] = explode(",",$id_list);
        logs(get_user_info("USERNAME"), "Hapus logs [id:$id_list]", $db_obj);
    }
    else
        $return_array["error_message"]=$db_obj->getLastError();
    
    echo json_encode($return_array);
}
function loadUsers($page)
{
    $db_obj = new DatabaseConnection();
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
        $search_string = $_POST['search_str'];
    
    $num_of_recs = 9;
    //count all pages
    $sql = "SELECT COUNT(*) FROM users";
    if(isset($search_string))
        $sql.=" WHERE((user_name LIKE '%$search_string%')OR(full_name LIKE '%$search_string%')OR(position LIKE '%$search_string%'))";
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT u.id,u.user_name,u.full_name,u.position, UCASE(ut.type) as access,
                u.login_status, u.last_login, u.last_ip, u.created_on, u.avatar
                FROM users u, user_types as ut 
                WHERE (u.access=ut.id)";
        if (isset($search_string))
            $sql.=" AND((u.user_name LIKE '%$search_string%')OR(u.full_name LIKE '%$search_string%')OR(u.position LIKE '%$search_string%'))";
        
        $sql.= " ORDER BY u.access
                LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            foreach($result as $item)
            {
                $item['programs'] = number_format(count_programs($item['id'], -1, $db_obj),0,',','.');
                $data['items'][] = $item;
            }
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    echo json_encode($data);
}
function deleteUser($id)
{
    $db_obj = new DatabaseConnection();
    //check if user has program. if yes, deny delete
    $sql = "SELECT COUNT(*) FROM programs WHERE (creation_by=$id)OR(last_update_by=$id)OR(approval_by=$id)";
    $tot_program = $db_obj->singleValueFromQuery($sql);
    if ($tot_program==0)
    {
        //check if user has task
        $sql = "SELECT COUNT(*) FROM tasks WHERE (creation_by=$id)OR(last_update_by=$id)";
        $tot_task = $db_obj->singleValueFromQuery($sql);
        if ($tot_task==0)
        {
            //delete news and info created by this user
            $sql = "SELECT id FROM news WHERE news_by=$id";
            $news_id = $db_obj->execSQL($sql);
            if ($news_id)
            {
                $parent_id = array();
                foreach($news_id as $item)
                    $parent_id[] = $item['id'];
                $sql = "DELETE FROM news WHERE (news_by=$id)OR(parent IN(".implode(",",$parent_id)."))";
            }
            else
                $sql = "DELETE FROM news WHERE news_by=$id";
            $db_obj->query($sql);
            
            //check if has avatar other than default.jpg
            $sql = "SELECT avatar FROM users WHERE id=$id";
            $avatar = $db_obj->singleValueFromQuery($sql);
            if ($avatar&&$avatar!='default.jpg')
            {
                if (file_exists("../customs/profile/photo/".$avatar))
                    unlink("../customs/profile/photo/".$avatar);
            }
            $sql = "DELETE FROM users WHERE (id=".$id.")";
            if ($db_obj->query($sql))
            {
                logs(get_user_info("USERNAME"), "Hapus user [id:$id]", $db_obj);
                echo 1;
            }
            else
                echo ('0'.$db_obj->getLastError());
        }else{
            echo '0User tidak dapat dihapus karena memiliki kegiatan yang berasosiasi dengannya.';
        }
    }else{
        echo '0User tidak dapat dihapus karena memiliki program yang berasosiasi dengannya.';
    }
}
function loadAllKabupaten($page)
{
    $db_obj = new DatabaseConnection();
    
    if (isset($_POST['keyword'])&&$_POST['keyword']!='')
        $search_string = $_POST['keyword'];
    $propinsi = $_POST['propinsi'];
    
    $num_of_recs = 10;
    //count all pages
    $sql = "SELECT COUNT(*) FROM kabupaten k WHERE (1=1)";
    if ($propinsi>0)
        $sql.= " AND(k.propinsi=$propinsi)";
    if(isset($search_string))
        $sql.=" AND((k.kabupaten LIKE '%$search_string%')OR(k.ibukota  LIKE '%$search_string%'))";
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT k.id,k.kabupaten, k.ibukota as ibukab, k.luas, k.populasi, k.web,
                p.propinsi, p.ibukota as ibuprop
                FROM kabupaten k, propinsi p WHERE (k.propinsi=p.id)";
        if ($propinsi>0)
            $sql.= " AND(k.propinsi=$propinsi)";
        if (isset($search_string))
            $sql.=" AND((k.kabupaten LIKE '%$search_string%')OR(k.ibukota  LIKE '%$search_string%'))";
        
        $sql.= " ORDER BY k.propinsi, k.kabupaten
                LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            foreach($result as $item)
            {
                $item['luas'] = number_format($item['luas'],3,",",".");
                $item['populasi'] = number_format($item['populasi'],0,",",".");
                $data['items'][] = $item;
            }
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    echo json_encode($data);
}
function saveKabupaten($id)
{
    $db_obj = new DatabaseConnection();
    $kabupaten = mysql_real_escape_string($_POST['kabupaten']);
    $ibukota = mysql_real_escape_string($_POST['ibukota']);
    $propinsi = $_POST['propinsi'];
    $luas = mysql_real_escape_string($_POST['luas']);
    $populasi = mysql_real_escape_string($_POST['populasi']);
    $web = mysql_real_escape_string($_POST['web']);
    
    $result = array("success"=>false, "error"=>"", "id"=>$id);
    if ($id==0){
        $sql = "INSERT INTO kabupaten (propinsi,kabupaten,ibukota,luas,populasi,web)VALUES
                ($propinsi,'$kabupaten','$ibukota',$luas,$populasi,'$web')";
    }else{
        $sql = "UPDATE kabupaten SET propinsi=$propinsi, kabupaten='$kabupaten',
                ibukota='$ibukota', luas=$luas, populasi=$populasi, web='$web'
                WHERE id=$id";
    }
    if($db_obj->query($sql)){
        if ($id==0)
        {
            $id = $db_obj->getLastId();
            $result['id'] = $id;
        }
        $result['success']=true;
        //create log
        logs(get_user_info("USERNAME"), "Update kabupaten [name:$kabupaten]", $db_obj);
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    echo json_encode($result);
}
function deleteKabupaten($id_list)
{
    $db_obj = new DatabaseConnection();
    
    $sql = "DELETE FROM kabupaten WHERE id IN ($id_list)";
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    if ($db_obj->query($sql))
    {
        $return_array['success_id'] = explode(",",$id_list);
        logs(get_user_info("USERNAME"), "Hapus kabupaten [id:$id_list]", $db_obj);
    }
    else
        $return_array["error_message"]=$db_obj->getLastError();
    
    echo json_encode($return_array);
}
function deleteTasks($id_list)
{
    $db_obj = new DatabaseConnection();
    
    //check if has doc ref
    //SELECT image for this program
    $sql = "SELECT filename FROM doc_references WHERE (task IN (".$id_list."))";
    $doc_refs = $db_obj->execSQL($sql);
    if ($doc_refs)
    {
        foreach($doc_refs as $image)
        {
            //only delete if file exists
            if (file_exists("../doc_references/".$image['filename']))
            {
                if (!unlink("../doc_references/".$image['filename'])){
                    $return_array["error_message"][]="Gagal menghapus file ".$image['filename'];
                }
            }else{
                $return_array["error_message"][]="File ".$image['filename']." tidak ditemukan";
            }
        }
    }
    //DELETE image for this program
    $sql = "DELETE FROM doc_references WHERE (task IN (".$id_list."))";
    $db_obj->query($sql);
    
    $sql = "DELETE FROM tasks WHERE id IN ($id_list)";
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    if ($db_obj->query($sql))
    {
        $return_array['success_id'] = explode(",",$id_list);
        logs(get_user_info("USERNAME"), "Hapus kegiatan [id:$id_list]", $db_obj);
    }
    else
        $return_array["error_message"]=$db_obj->getLastError();
    
    echo json_encode($return_array);
}
function deleteUker($id_list)
{
    $db_obj = new DatabaseConnection();
    $sql = "DELETE FROM uker WHERE id IN(".$id_list.")";
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    if ($db_obj->query($sql))
    {
        $return_array['success_id'] = explode(",",$id_list);
        logs(get_user_info("USERNAME"), "Hapus unit kerja [id:$id_list]", $db_obj);
    }
    else
        $return_array["error_message"]=$db_obj->getLastError();
    
    echo json_encode($return_array);
}
function deleteRealisation($id_list)
{
    $db_obj = new DatabaseConnection();
    $return_array = array("success_id"=>array(),"error_id"=>array(),"error_message"=>array());
    //get any fund used (realisasi)
    $sql = "SELECT SUM(nominal) FROM budget_real_used WHERE id IN ($id_list)";
    $fund_used = $db_obj->singleValueFromQuery($sql);
    //if fund used > 0, create new credit transaction in table saldo_real
    if ($fund_used)
    {
        $sql = "INSERT INTO saldo_real(trans_desc, trans_credit, trans_by)VALUES
            ('Refund: Hapus program ".$id_list."',$fund_used,".get_user_info("ID").")";
        $db_obj->query($sql);
    }
    //remove the fund used record for this program
    $sql = "DELETE FROM budget_real_used WHERE id IN ($id_list)";
    if ($db_obj->query($sql)){
        $return_array['success_id'] = explode(",",$id_list);
        logs(get_user_info("USERNAME"), "Hapus unit kerja [id:$id_list]", $db_obj);
    }else
        $return_array["error_message"]=$db_obj->getLastError();
    
    echo json_encode($return_array);
}
function loadNews($page)
{
    $db_obj = new DatabaseConnection();
    
    if (isset($_POST['keyword'])&&$_POST['keyword']!='')
        $search_string = $_POST['keyword'];
    
    $num_of_recs = 10;
    //count all pages
    $sql = "SELECT COUNT(*) FROM news n, users u WHERE (n.news_by=u.id)";
    if(isset($search_string))
        $sql.=" AND((MATCH(n.news_text) AGAINST ('$search_string'))OR(u.full_name LIKE '%$search_string%'))";
    
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array(),"parents"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT n.id, n.parent, n.news_by as news_by_id, u.full_name as news_by, u.avatar,
                n.news_date, n.news_text FROM news n, users u WHERE (n.news_by=u.id)";
        if (isset($search_string))
            $sql.=" AND((MATCH(n.news_text) AGAINST ('$search_string'))OR(u.full_name LIKE '%$search_string%'))";
        
        $sql.= " ORDER BY n.news_date DESC
                LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);

        if ($result)
        {            
            foreach($result as $item)
            {
                $item['news_text'] = $item['news_text'];
                $item['view_by'] = get_user_info("ID");
                $data['parents'][$item['parent']] []= $item['id'];
                $data['items'][$item['id']] = $item;
            }
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    echo json_encode($data);
}
function saveNews($parent)
{
    $db_obj = new DatabaseConnection();
    $news_text = $_POST['news_text'];
    
    $result = array("success"=>false, "error"=>"", "items"=>array());
    
    $sql = "INSERT INTO news (parent,news_by,news_text)VALUES
            ($parent,".get_user_info("ID").",'".mysql_escape_string(nl2br($news_text))."')";
    if ($db_obj->query($sql))
    {
        $result['success'] = true;
        $result['items']['id'] = $db_obj->getLastId();
        $result['items']['parent'] = $parent;
        $result['items']['date']  = date("Y-m-d H:i:s");
        $result['items']['by'] = get_user_info("FULLNAME");
        $result['items']['text'] = nl2br($news_text);
        $result['items']['avatar'] = get_user_info("AVATAR");
        $result['items']['view_by'] = get_user_info("ID");
        
        logs(get_user_info("USERNAME"), "Kirim info", $db_obj);
    }else{
        $result['error'] = $db_obj->getLastError();;
    }
    
    echo json_encode($result);
}
function deleteNews($id)
{
    $db_obj = new DatabaseConnection();
    $sql = "DELETE FROM news WHERE (id=$id)OR(parent=$id)";
    if ($db_obj->query($sql)){
        logs(get_user_info("USERNAME"), "Hapus news &amp; info [id:$id]", $db_obj);
        echo 1;
    }else{
        echo ('0'.$db_obj->getLastError());
    }
}
function loadAllBackups()
{
    $backup_folder = "../db_backup/";
    $files = file_list($backup_folder, "sql");
    
    $data = array("found"=>count($files),"items"=>array());
    if (count($files>0))
    {
        foreach($files as $item)
        {
            $filename = explode('.', $item['name']);
            $item['id'] = $filename[0];
            $item['creation_date'] = date("Y-m-d H:i:s", $filename[0]);
            $item['size_kb'] = number_format(($item['size']/1024),2,",",".");
            
            $data['items'] [] = $item;
        }
        
    }
    echo json_encode($data);
}
function createBackup()
{
    $backup_folder = "../db_backup/";
    //check if folder exists, if not, create one
    if (!file_exists($backup_folder))
        mkdir($backup_folder, 0775);
    
    $filename = time().'.sql';
    $result = exec("mysqldump -h ".DB_HOST." -u ".DB_USER." -p".DB_PWD." ".DB_NAME." > $backup_folder$filename");
    if (strlen(trim($result))>0)
        echo trim($result);
    else
        echo '';
}
function deleteBackups($id_list)
{
    $id_list = explode(",", $id_list);
    $backup_folder = "../db_backup/";
    $ext = ".sql";
    $result = array("success_id"=>array(),"error"=>"");
    foreach($id_list as $file)
    {
        if (file_exists($backup_folder.$file.$ext))
        {
            if (unlink($backup_folder.$file.$ext))
            {
                $result['success_id'][] = $file;
            }else{
                $result['error'][] = 'Gagal menghapus file '.$file.$ext;
            }
        }else{
            $result['error'][] = 'File '.$file.$ext.' tidak dapat ditemukan';
        }
    }
    
    echo json_encode($result);
}
function restoreFromBackup($filename)
{
    $backup_folder = "../db_backup/";
    if(file_exists($backup_folder.$filename))
    {
        exec("mysql -h ".DB_HOST." -u ".DB_USER." -p".DB_PWD." ".DB_NAME." < $backup_folder$filename");
    }else{
        echo "Error. File not exists";
    }
}
function loadProTypes($page)
{
    $db_obj = new DatabaseConnection();
    
    $num_of_recs = 9;
    $sql = "SELECT COUNT(*) FROM program_types";
    $found = $db_obj->singleValueFromQuery($sql);
    $pages = ceil($found / $num_of_recs);
    $start = $num_of_recs * $page;
    
    $data = array("found"=>$found,"error"=>'',"pages"=> $pages, "start"=>$start, "items"=>array());
    
    if ($found>0)
    {
        $sql = "SELECT pt.id, pt.type, pt.creation_date, u.full_name as creation_by,
                pt.last_update, u.full_name as last_update_by, 
                (SELECT COUNT(*) FROM programs p WHERE p.type=pt.id) AS program
                FROM program_types pt, users u
                WHERE (pt.creation_by=u.id)AND(pt.last_update_by=u.id)
                LIMIT $start, $num_of_recs";
        
        $result = $db_obj->execSQL($sql);

        if ($result)
        {
            foreach($result as $item)
            {
                $item['program'] = number_format($item['program'],0,',','.');
                $data['items'][] = $item;
            }
        }else{
            $data["found"] = 0;
            $data["error"] = $db_obj->getLastError();
        }
    }
    echo json_encode($data);
}
function protypeUpdate($id)
{
    $db_obj = new DatabaseConnection();
    $type = mysql_escape_string(sanitizeText($_POST['type']));
    if ($id==0)
        $creation_by = get_user_info("FULLNAME");
    else
        $creation_by = $_POST['creation_by'];
    if ($id==0)
        $creation_date = date("Y-m-d H:i:s");
    else
        $creation_date = $_POST['creation_date'];
    
    $result = array("success"=>false, "error"=>"", "items"=>array());
    
    if ($id==0)
    {
        $sql = "INSERT INTO program_types (type,creation_date,creation_by,last_update,last_update_by)VALUES
                ('$type',NOW(),".get_user_info("ID").",NOW(),".get_user_info("ID").")";
    }else{
        $sql = "UPDATE program_types SET type='$type',last_update=NOW(),last_update_by=".get_user_info("ID")."
                WHERE id=$id";
    }
    if ($db_obj->query($sql))
    {
        $result['success'] = true;
        if ($id==0)
            $id = $db_obj->getLastId();
            
        $result['items']['id'] = $id;
        $result['items']['type'] = $type;
        $result['items']['creation_by'] = $creation_by;
        $result['items']['creation_date'] = $creation_date;
        $result['items']['last_update'] = date("Y-m-d H:i:s");
        $result['items']['last_update'] = get_user_info("FULLNAME");
        
        logs(get_user_info("USERNAME"), "Update bidang program [name$type]", $db_obj);
    }else{
        $result['error'] = $db_obj->getLastError();;
    }
    
    echo json_encode($result);
}
function deleteProtypes($id_list)
{
    $db_obj = new DatabaseConnection();
    
    //remove the program types
    $sql = "DELETE FROM program_types WHERE id IN ($id_list)";
    if ($db_obj->query($sql)){        
        logs(get_user_info("USERNAME"), "Hapus bidang program [id:$id_list]", $db_obj);
        echo 1;
    }else
        echo ('0'.$db_obj->getLastError());
}
function lookupPIC($input_str)
{
    $db_obj = new DatabaseConnection();
    
    $num_of_recs = 15;
    $data = array("found"=>0,"error"=>'', "items"=>array());
    //count all pages
    $sql = "SELECT DISTINCT pic
                FROM programs WHERE (pic LIKE '%$input_str%')
                ORDER BY pic
                LIMIT $num_of_recs";
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $data['found'] = $db_obj->getNumRecord();
        $data['items'] = $result;
    }else{
        $data["found"] = 0;
        $data["error"] = $db_obj->getLastError();
    }
    echo json_encode($data);
}
function lookupBeneficiaries($input_str)
{
    $db_obj = new DatabaseConnection();
    
    $num_of_recs = 15;
    $data = array("found"=>0,"error"=>'', "items"=>array());
    //count all pages
    $sql = "SELECT DISTINCT benef_name, benef_address, benef_phone, benef_email
                FROM programs WHERE (benef_name LIKE '%$input_str%')
                ORDER BY benef_name
                LIMIT $num_of_recs";
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $data['found'] = $db_obj->getNumRecord();
        $data['items'] = $result;
    }else{
        $data["found"] = 0;
        $data["error"] = $db_obj->getLastError();
    }
    echo json_encode($data);
}
function loadBeneficiary($program_id)
{
    $db_obj = new DatabaseConnection();
    $arr_data = array("found"=>0,"items"=>array());
    $sql = "SELECT benef_name, benef_address, benef_phone, benef_email
            FROM programs
            WHERE id=$program_id";
    $result = $db_obj->execSQL($sql);
    if ($result)
    {
        $arr_data['found'] = 1;
        $arr_data['items'] = $result[0];
    }else{
        $arr_data['found'] = 0;
    }
    
    echo json_encode($arr_data);
}
function updateSingleValue()
{
    $table = $_POST['table'];
    $field_name = $_POST['field_name'];
    $field_id_val = $_POST['field_id_val'];
    $field_type = $_POST['field_type'];
    if ($field_type==0)
        $field_value = "'".$_POST['field_value']."'";
    else
    {
        $field_value = str_replace('.', '', $_POST['field_value']);
        $field_value = str_replace(',', '.', $field_value);
    }
    
    $db_obj = new DatabaseConnection();
    $sql = "UPDATE $table SET $field_name=$field_value WHERE id=$field_id_val";
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}
function loadAreas($mode)
{
    $db_obj = new DatabaseConnection();
    $arr_result = array("found"=>0, "items"=>array());
    if ($mode==0)
        $areas = load_kanwil($db_obj);
    else
        $areas = load_propinsi($db_obj);
    
    if ($areas)
    {
        $arr_result['found'] = count($areas);
        $arr_result['items'] = $areas;
    }
    
    echo json_encode($arr_result);
    
}
function export_to_excel($filename)
{
    if (isset($_POST['content']))
        $content = $_POST['content'];
    else
        $content = "<table><tr><td>Data not found</td></tr></table>";
    
    /*
    $mime = "application/vnd.ms-excel";
    header("Content-type: ".$mime);
    header("Content-Disposition: attachment; filename=$filename");
     * 
     */
    if (file_put_contents(APP_BASE_PATH."temp/".$filename, $content))
        echo 1;
    else
        echo 0;
}
function export_filtered_programs()
{
    set_time_limit(0);
    $db_obj = new DatabaseConnection();
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $type= $_POST['type'];
    $state = $_POST['state'];
    $year_creation = isset($_POST['creation_year']) ? $_POST['creation_year'] : NULL;
    
    $sql = "SELECT p.id, p.source,p.type,pt.type as type_name, p.name, p.description,p.potensi_bisnis, p.pic, p.uker_wilayah, p.uker_cabang,
            p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_address,p.benef_phone, p.benef_email, p.benef_orang, p.benef_unit, 
            DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
            DATE(p.approval_date) as approval_date, p.budget,p.nodin_putusan,p.nomor_persetujuan,p.nomor_registrasi,
            p.tgl_putusan,p.tgl_register,p.nomor_bg,
            p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
            FROM programs p, uker u, users us, propinsi pr, kabupaten k, program_types pt
            WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.type=pt.id)";
    if ($year_creation){
        $sql.= " AND(YEAR(p.creation_date)=$year_creation)";
    }
    if (isset($search_string))
    {
        $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')
                OR(p.nodin_putusan LIKE '%$search_string%')
		OR(p.nomor_registrasi LIKE '%$search_string%')
		OR(p.nomor_persetujuan LIKE '%$search_string%')";
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";

        $sql.=")";
    }
    if ($type>0) {
        $sql.= " AND(p.type=$type)";
    }

    if ($state>=0) {
        $sql.=" AND(p.state=$state)";
    }

    $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC";
    $result = $db_obj->fetch_obj($sql);

    $arr = array();
    if ($result)
    {
        foreach($result as $item)
        {
            $item->progress = program_progress($item->id, $db_obj);
            $item->real_used = program_real_fund_used($item->id, $db_obj);
            $arr[] = $item;
        }
    }
    
    require APP_BASE_PATH . 'funcs/PHPExcel.php';
    /*
     * Start writing excel file
     */
    $Excel = new PHPExcel();
    $Excel->getProperties()->setCreator('PT. Bank Rakyat Indonesia, Tbk.')
            ->setLastModifiedBy('Div. Corporate Secretary')
            ->setTitle('Data Program Bina Lingkungan PT. Bank Rakyat Indonesia');
    
    //create header
    $Excel->setActiveSheetIndex(0);
    $Excel->getActiveSheet()->setShowGridlines(TRUE);

    //Set Title
    $Excel->getActiveSheet()->setCellValue('A1', 'DAFTAR PROGRAM BINA LINGKUNGAN PT. BANK RAKYAT INDONESIA, TBK.');
    
    $row = 3;
    $col = 'A';
    
    // Buat Nama Kolom
    $Excel->getActiveSheet()
            ->setCellValue($col++.$row, 'NO.')
            ->setCellValue($col++.$row, 'PROGRAM_ID')
            ->setCellValue($col++.$row, 'NAMA_PROGRAM')
            ->setCellValue($col++.$row, 'KELOMPOK_PROGRAM')
            ->setCellValue($col++.$row, 'BIDANG_ID')
            ->setCellValue($col++.$row, 'NAMA_BIDANG')
            ->setCellValue($col++.$row, 'BESAR_ANGGARAN')
            ->setCellValue($col++.$row, 'DANA_OPERASIONAL')
            ->setCellValue($col++.$row, 'UNIT_KERJA')
	    ->setCellValue($col++.$row, 'WILAYAH')
            ->setCellValue($col++.$row, 'KABUPATEN')
            ->setCellValue($col++.$row, 'PROVINSI')
            ->setCellValue($col++.$row, 'PENERIMA_MANFAAT')
            ->setCellValue($col++.$row, 'ALAMAT_PENERIMA')
            ->setCellValue($col++.$row, 'TELEPON_PENERIMA')
            ->setCellValue($col++.$row, 'EMAIL_PENERIMA')
            ->setCellValue($col++.$row, 'JLH_ORANG')
            ->setCellValue($col++.$row, 'JLH_UNIT')
            ->setCellValue($col++.$row, 'TGL_PEMBUATAN')
            ->setCellValue($col++.$row, 'DESKRIPSI')
            ->setCellValue($col++.$row, 'POTENSI')
            ->setCellValue($col++.$row, 'REALISASI')
            ->setCellValue($col++.$row, 'PROGRESS')
            ->setCellValue($col++.$row, 'STATUS')
            ->setCellValue($col++.$row, 'NODIN_PUTUSAN')
            ->setCellValue($col++.$row, 'TGL_PUTUSAN')
            ->setCellValue($col++.$row, 'NOMOR_PERSETUJUAN')
            ->setCellValue($col++.$row, 'TGL_PERSETUJUAN')
            ->setCellValue($col++.$row, 'NOMOR_REGISTER')
            ->setCellValue($col++.$row, 'TGL_REGISTER')
            ->setCellValue($col++.$row, 'NOMOR_BG')
            ->setCellValue($col++.$row, 'PIC');
    
    // ISI DATA PROGRAM
    if (count($arr)) {
	$kanwil_arr = get_kanwil_arr($db_obj);

        foreach ($arr as $item) {
            $col = 'A'; $row++;
            
            $Excel->getActiveSheet()
                    ->setCellValue($col++.$row, $row-1)
                    ->setCellValue($col++.$row, $item->id)
                    ->setCellValue($col++.$row, $item->name)
                    ->setCellValue($col++.$row, $item->source==0?'BRI Perduli':'BUMN Perduli')
                    ->setCellValue($col++.$row, $item->type)
                    ->setCellValue($col++.$row, $item->type_name)
                    ->setCellValue($col++.$row, $item->budget)
                    ->setCellValue($col++.$row, $item->operational)
                    ->setCellValue($col++.$row, $item->uker)
		    ->setCellValue($col++.$row, $kanwil_arr[$item->uker_wilayah])
                    ->setCellValue($col++.$row, $item->kabupaten)
                    ->setCellValue($col++.$row, $item->propinsi)
                    ->setCellValue($col++.$row, $item->benef_name)
                    ->setCellValue($col++.$row, $item->benef_address)
                    ->setCellValue($col++.$row, $item->benef_phone)
                    ->setCellValue($col++.$row, $item->benef_email)
                    ->setCellValue($col++.$row, $item->benef_orang)
                    ->setCellValue($col++.$row, $item->benef_unit)
                    ->setCellValue($col++.$row, $item->creation_date)
                    ->setCellValue($col++.$row, $item->description)
                    ->setCellValue($col++.$row, $item->potensi_bisnis)
                    ->setCellValue($col++.$row, $item->real_used)
                    ->setCellValue($col++.$row, $item->progress)
                    ->setCellValue($col++.$row, $item->state==0?'NO':'YES')
                    ->setCellValue($col++.$row, $item->nodin_putusan)
                    ->setCellValue($col++.$row, $item->tgl_putusan)
                    ->setCellValue($col++.$row, $item->nomor_persetujuan)
                    ->setCellValue($col++.$row, $item->approval_date)
                    ->setCellValue($col++.$row, $item->nomor_registrasi)
                    ->setCellValue($col++.$row, $item->tgl_register)
                    ->setCellValue($col++.$row, $item->nomor_bg)
                    ->setCellValue($col++.$row, $item->pic);
        }
    }

    $ExcelWriter = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
    $filename = 'program_exported_'.date('Y-m-d-His').'.xlsx';
    if (is_writable(sys_get_temp_dir())){
        $filename_full_path = sys_get_temp_dir() .'/'. $filename;
    }else{
        $filename_full_path = APP_BASE_PATH. 'temp/'.$filename;
    }
    
    $return = new stdClass();
    $return->status = FALSE;
    try{
        $ExcelWriter->save($filename_full_path);
        $return->status = TRUE;
        $return->filename = urlencode(base64_encode($filename_full_path));
    } catch (Exception $ex) {
        $return->message = $ex->getMessage();
    }


    echo json_encode($return);
}
function export_filtered_wilayah()
{
    set_time_limit(0);
    $db_obj = new DatabaseConnection();
    if (isset($_POST['search_str'])&&$_POST['search_str']!='')
    {
        $search_string = $_POST['search_str'];
        //get all kanwil for searching
        $kanwil_id_like = get_kanwil_id_by_searching($search_string, $db_obj);
    }
    $wilayah = $_POST['wilayah'];
    $type= $_POST['type'];
    $state = $_POST['state'];
    $year_creation = isset($_POST['creation_year']) ? $_POST['creation_year'] : NULL;
    
    $sql = "SELECT p.id, p.source,p.type,pt.type as type_name, p.name, p.description,p.potensi_bisnis, p.pic, p.uker_wilayah, p.uker_cabang,
            p.state, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_address,p.benef_phone, p.benef_email, p.benef_orang, p.benef_unit, 
            DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
            DATE(p.approval_date) as approval_date, p.budget,p.nodin_putusan,p.nomor_persetujuan,p.nomor_registrasi,
            p.tgl_putusan, tgl_register, nomor_bg,
            p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
            FROM programs p, uker u, users us, propinsi pr, kabupaten k, program_types pt
            WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.type=pt.id)";
    if ($wilayah>0){
        $sql.=" AND(p.uker_wilayah=$wilayah)";
    }
    if ($year_creation){
        $sql.= " AND(YEAR(p.creation_date)=$year_creation)";
    }
    if (isset($search_string))
    {
        $sql.=" AND ((MATCH(p.name,p.description) AGAINST ('$search_string'))OR(p.name LIKE '%$search_string%')
                OR(u.uker LIKE '%$search_string%')OR(k.kabupaten LIKE '%$search_string%')
                OR(k.ibukota LIKE '%$search_string%')
                OR(pr.propinsi LIKE '%$search_string%')
                OR(pr.ibukota LIKE '%$search_string%')
                OR(p.benef_name LIKE '%$search_string%')
                OR(p.nodin_putusan LIKE '%$search_string%')
		OR(p.nomor_registrasi LIKE '%$search_string%')
		OR(p.nomor_persetujuan LIKE '%$search_string%')";
        if ($kanwil_id_like)
            $sql.= "OR(p.uker_wilayah IN (". implode(",",$kanwil_id_like)."))";

        $sql.=")";
    }
    if ($type>0) {
        $sql.= " AND(p.type=$type)";
    }

    if ($state>=0) {
        $sql.=" AND(p.state=$state)";
    }

    $sql.= " ORDER BY p.creation_date DESC, p.approval_date DESC";
    $result = $db_obj->fetch_obj($sql);

    $arr = array();
    if ($result)
    {
        foreach($result as $item)
        {
            $item->progress = program_progress($item->id, $db_obj);
            $item->real_used = program_real_fund_used($item->id, $db_obj);
            $arr[] = $item;
        }
    }
    
    require APP_BASE_PATH . 'funcs/PHPExcel.php';
    /*
     * Start writing excel file
     */
    $Excel = new PHPExcel();
    $Excel->getProperties()->setCreator('PT. Bank Rakyat Indonesia, Tbk.')
            ->setLastModifiedBy('Div. Corporate Secretary')
            ->setTitle('Data Program Bina Lingkungan PT. Bank Rakyat Indonesia');
    
    //create header
    $Excel->setActiveSheetIndex(0);
    $Excel->getActiveSheet()->setShowGridlines(TRUE);

    //Set Title
    $Excel->getActiveSheet()->setCellValue('A1', 'DAFTAR PROGRAM BINA LINGKUNGAN PT. BANK RAKYAT INDONESIA, TBK.');
    
    $row = 3;
    $col = 'A';
    
    // Buat Nama Kolom
    $Excel->getActiveSheet()
            ->setCellValue($col++.$row, 'NO.')
            ->setCellValue($col++.$row, 'PROGRAM_ID')
            ->setCellValue($col++.$row, 'NAMA_PROGRAM')
            ->setCellValue($col++.$row, 'KELOMPOK_PROGRAM')
            ->setCellValue($col++.$row, 'BIDANG_ID')
            ->setCellValue($col++.$row, 'NAMA_BIDANG')
            ->setCellValue($col++.$row, 'BESAR_ANGGARAN')
            ->setCellValue($col++.$row, 'DANA_OPERASIONAL')
            ->setCellValue($col++.$row, 'UNIT_KERJA')
	    ->setCellValue($col++.$row, 'WILAYAH')
            ->setCellValue($col++.$row, 'KABUPATEN')
            ->setCellValue($col++.$row, 'PROVINSI')
            ->setCellValue($col++.$row, 'PENERIMA_MANFAAT')
            ->setCellValue($col++.$row, 'ALAMAT_PENERIMA')
            ->setCellValue($col++.$row, 'TELEPON_PENERIMA')
            ->setCellValue($col++.$row, 'EMAIL_PENERIMA')
            ->setCellValue($col++.$row, 'JLH_ORANG')
            ->setCellValue($col++.$row, 'JLH_UNIT')
            ->setCellValue($col++.$row, 'TGL_PEMBUATAN')
            ->setCellValue($col++.$row, 'DESKRIPSI')
            ->setCellValue($col++.$row, 'POTENSI')
            ->setCellValue($col++.$row, 'REALISASI')
            ->setCellValue($col++.$row, 'PROGRESS')
            ->setCellValue($col++.$row, 'STATUS')
            ->setCellValue($col++.$row, 'NODIN_PUTUSAN')
            ->setCellValue($col++.$row, 'TGL_PUTUSAN')
            ->setCellValue($col++.$row, 'NOMOR_PERSETUJUAN')
            ->setCellValue($col++.$row, 'TGL_PERSETUJUAN')
            ->setCellValue($col++.$row, 'NOMOR_REGISTER')
            ->setCellValue($col++.$row, 'TGL_REGISTER')
            ->setCellValue($col++.$row, 'NOMOR_BG')
            ->setCellValue($col++.$row, 'PIC');
    
    // ISI DATA PROGRAM
    if (count($arr)) {
	$kanwil_arr = get_kanwil_arr($db_obj);

        foreach ($arr as $item) {
            $col = 'A'; $row++;
            
            $Excel->getActiveSheet()
                    ->setCellValue($col++.$row, $row-1)
                    ->setCellValue($col++.$row, $item->id)
                    ->setCellValue($col++.$row, $item->name)
                    ->setCellValue($col++.$row, $item->source==0?'BRI Perduli':'BUMN Perduli')
                    ->setCellValue($col++.$row, $item->type)
                    ->setCellValue($col++.$row, $item->type_name)
                    ->setCellValue($col++.$row, $item->budget)
                    ->setCellValue($col++.$row, $item->operational)
                    ->setCellValue($col++.$row, $item->uker)
		    ->setCellValue($col++.$row, $kanwil_arr[$item->uker_wilayah])
                    ->setCellValue($col++.$row, $item->kabupaten)
                    ->setCellValue($col++.$row, $item->propinsi)
                    ->setCellValue($col++.$row, $item->benef_name)
                    ->setCellValue($col++.$row, $item->benef_address)
                    ->setCellValue($col++.$row, $item->benef_phone)
                    ->setCellValue($col++.$row, $item->benef_email)
                    ->setCellValue($col++.$row, $item->benef_orang)
                    ->setCellValue($col++.$row, $item->benef_unit)
                    ->setCellValue($col++.$row, $item->creation_date)
                    ->setCellValue($col++.$row, $item->description)
                    ->setCellValue($col++.$row, $item->potensi_bisnis)
                    ->setCellValue($col++.$row, $item->real_used)
                    ->setCellValue($col++.$row, $item->progress)
                    ->setCellValue($col++.$row, $item->state==0?'NO':'YES')
                    ->setCellValue($col++.$row, $item->nodin_putusan)
                    ->setCellValue($col++.$row, $item->tgl_putusan)
                    ->setCellValue($col++.$row, $item->nomor_persetujuan)
                    ->setCellValue($col++.$row, $item->approval_date)
                    ->setCellValue($col++.$row, $item->nomor_registrasi)
                    ->setCellValue($col++.$row, $item->tgl_register)
                    ->setCellValue($col++.$row, $item->nomor_bg)
                    ->setCellValue($col++.$row, $item->pic);
        }
    }

    $ExcelWriter = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
    $filename = 'program_exported_'.date('Y-m-d-His').'.xlsx';
    if (is_writable(sys_get_temp_dir())){
        $filename_full_path = sys_get_temp_dir() .'/'. $filename;
    }else{
        $filename_full_path = APP_BASE_PATH. 'temp/'.$filename;
    }
    
    $return = new stdClass();
    $return->status = FALSE;
    try{
        $ExcelWriter->save($filename_full_path);
        $return->status = TRUE;
        $return->filename = urlencode(base64_encode($filename_full_path));
    } catch (Exception $ex) {
        $return->message = $ex->getMessage();
    }


    echo json_encode($return);
}
function download_backup(){
    //get files
    $backup_files = explode(",", $_POST['ids']);
    
    $filename = 'backupdb_'.date('Y-m-d-his').'.zip';
    if (is_writable(sys_get_temp_dir())){
        $filename_full_path = sys_get_temp_dir() .'/'. $filename;
    }else{
        $filename_full_path = APP_BASE_PATH. 'temp/'.$filename;
    }
    
    $return = new stdClass();
    $return->status = FALSE;
    try{
        
        $zip = new ZipArchive();

        if ($zip->open($filename_full_path, ZipArchive::CREATE)===TRUE) {
            if ($backup_files && count($backup_files)) {
                foreach ($backup_files as $bf){
                    $zip->addFile(APP_BASE_PATH .'db_backup/'.$bf.'.sql','/'.$bf.'.sql');
                }
            }
            
            $zip->close();

            $return->status = TRUE;
            $return->numfiles = $zip->numFiles;
            $return->filename = urlencode(base64_encode($filename_full_path));
        } else {
            $return->message = "Failed create zip backup file";
        }

        
    } catch (Exception $ex) {
        $return->message = $ex->getMessage();
    }


    echo json_encode($return);
}
function saveRKAP($id)
{
    $db_obj = new DatabaseConnection();
    $result = array("success"=>0,"error"=>'',"id"=>$id);
    
    $created_by = get_user_info("ID");
    $last_update_by = get_user_info("ID");
    
    $tahun = $_POST['tahun'];
    $triwulan_1 = $_POST['triwulan_1'];
    $triwulan_2 = $_POST['triwulan_2'];
    $triwulan_3 = $_POST['triwulan_3'];
    $triwulan_4 = $_POST['triwulan_4'];
    $real_1 = $_POST['real_1'];
    $real_2 = $_POST['real_2'];
    $real_3 = $_POST['real_3'];
    $real_4 = $_POST['real_4'];
    $component = $_POST['component'];
    
    if ($id==0)
    {
        //must check if same component exists in same year
        $sql = "SELECT COUNT(*) FROM rkap WHERE(tahun=$tahun)AND(component=$component)";
        if ($db_obj->singleValueFromQuery($sql)>0)
            $result['error'] = "Komponen yang sama sudah ada di tahun yang sama";
        else
        {
            $sql = "INSERT INTO rkap (tahun,triwulan_1,triwulan_2,triwulan_3,triwulan_4,real_1,real_2,real_3,real_4,component,created_on,created_by,last_update,last_update_by)VALUES
                    ($tahun,$triwulan_1,$triwulan_2,$triwulan_3,$triwulan_4,$real_1,$real_2,$real_3,$real_4,$component,NOW(),$created_by,NOW(),$last_update_by)";
        }
    }else{
        //must check if same component exists in same year
        $sql = "SELECT COUNT(*) FROM rkap WHERE(id<>$id)AND(tahun=$tahun)AND(component=$component)";
        if ($db_obj->singleValueFromQuery($sql)>0)
            $result['error'] = "Komponen yang sama sudah ada di tahun yang sama";
        else
        {
            $sql = "UPDATE rkap SET tahun=$tahun,triwulan_1=$triwulan_1,triwulan_2=$triwulan_2,
                    triwulan_3=$triwulan_3,triwulan_4=$triwulan_4,real_1=$real_1,real_1=$real_1,
                    real_2=$real_2,real_3=$real_3,real_4=$real_4,component=$component,
                    last_update=NOW(),last_update_by=$last_update_by
                    WHERE (id=$id)";
        }
    }
    if ($result['error']=='')
    {
        if ($db_obj->query($sql))
        {
            if ($id==0)
                $result['id'] = $db_obj->getLastId();
            $result['success'] = 1;

            logs(get_user_info("USERNAME"), "Update RKAP [id:$id]", $db_obj);        
        }else{
            $result['error'] = $db_obj->getLastError();
        }
    }
    
    echo json_encode($result);
}
function loadRKAP($year)
{
    $db_obj = new DatabaseConnection();
    $result = array("found"=>0,"items"=>array(),"error"=>'',"year"=>$year);
    
    $sql = "SELECT ra.id, ra.tahun, ra.triwulan_1, ra.triwulan_2, 
            ra.triwulan_3, ra.triwulan_4, ra.real_1, ra.real_2,
            ra.real_3, ra.real_4, 
            co.category, co.caption, UCASE(ca.caption) as cat_caption
            FROM rkap as ra, rkap_component as co, rkap_category as ca
            WHERE(ra.component=co.id)AND(co.category=ca.id)";
    if ($year>0)
        $sql.=" AND(ra.tahun=$year)";
    $sql.= " ORDER BY ra.tahun, ca.sort, co.sort";
    
    $rkap = $db_obj->execSQL($sql);
    if ($rkap)
    {
        $result['found'] = count($rkap);
        $result['items'] = $rkap;
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function loadRKAP_Report($year)
{
    $db_obj = new DatabaseConnection();
    $result = array("found"=>0,"items"=>array(),"error"=>'',"year"=>$year,"total"=>array());
    
    $triwulan = $_POST['triwulan'];
    
    $sql = "SELECT ra.id, ra.tahun, ra.triwulan_4 as rkap, co.tag, co.tag_value,
            ra.triwulan_1, ra.triwulan_2, ra.triwulan_3, ra.triwulan_4, 
            ra.real_1, ra.real_2, ra.real_3, ra.real_4, 
            co.category, co.caption, UCASE(ca.caption) as cat_caption,
            ca.tag as cat_tag
            FROM rkap as ra, rkap_component as co, rkap_category as ca
            WHERE(ra.component=co.id)AND(co.category=ca.id)";
    if ($year>0)
        $sql.=" AND(ra.tahun=$year)";
    $sql.= " ORDER BY ra.tahun, ca.sort, co.sort";
    
    $rkap = $db_obj->execSQL($sql);
    if ($rkap)
    {
        $result['found'] = count($rkap);
        //$result['items'] = $rkap;
        
        $total = array();
        $rkap_total = array();
        $category_check = 0;
        foreach ($rkap as $item)
        {                               
            if ($category_check!=$item['category'])
            {
                if ($category_check!=0)
                    $rkap_total[] = $total;
                //set total value for each category
                //Inisiasi variable
                $total = array("rkap"=>0,"triwulan_1"=>0, "triwulan_2"=>0, "triwulan_3"=>0, "triwulan_4"=>0);
                for($i=1;$i<=$triwulan;$i++)
                    $total['real_'.$i] = 0;
                $total['persen_1'] = 0; $total['persen_2'] = 0;
                
                $category_check = $item['category'];
            }
            if ($item['cat_tag']==3) //for saldo dana has its own calculation
            {
                $total['rkap'] = $rkap_total[0]['rkap']-$rkap_total[1]['rkap'];
                $total['triwulan_1'] = $rkap_total[0]['triwulan_1']-$rkap_total[1]['triwulan_1'];
                $total['triwulan_2'] = $rkap_total[0]['triwulan_2']-$rkap_total[1]['triwulan_2'];
                $total['triwulan_3'] = $rkap_total[0]['triwulan_3']-$rkap_total[1]['triwulan_3'];
                $total['triwulan_4'] = $rkap_total[0]['triwulan_4']-$rkap_total[1]['triwulan_4'];
                for($i=1;$i<=$triwulan;$i++)
                    $total['real_'.$i] = $rkap_total[0]['real_'.$i] - $rkap_total[1]['real_'.$i];
            }else{
                $total['rkap'] += $item['rkap'];
                $total['triwulan_1'] += $item['triwulan_1'];
                $total['triwulan_2'] += $item['triwulan_2'];
                $total['triwulan_3'] += $item['triwulan_3'];
                $total['triwulan_4'] += $item['triwulan_4'];
                for($i=1;$i<=$triwulan;$i++)
                    $total['real_'.$i] += $item['real_'.$i];
                
            }
            //add persentase pencapaian in head item
            if($total['triwulan_'.$triwulan]>0)
                $total['persen_1'] = ($total['real_'.$triwulan]/$total['triwulan_'.$triwulan])*100;
            else
                $total['persen_1'] = 0;
            if($total['triwulan_4']>0)
                $total['persen_2'] = ($total['real_'.$triwulan]/$total['triwulan_4'])*100;
            else
                $total['persen_2'] = 0;
            if ($item['tag']==3)
            {
                if ($item['tag_value']==0)
                    $persentase = 70;
                else
                    $persentase = 30;
                for($i=1;$i<=$triwulan;$i++)
                    $item['real_'.$i] = ceil($persentase*$rkap_total[0]['real_'.$i]/100)-getRealisationByTriwulan($year,$i,$item['tag_value'],$db_obj);
            }
            //add persentase pencapaian for each item
            if ($item['triwulan_'.$triwulan]>0)
                $item['persen_1'] = ($item['real_'.$triwulan]/$item['triwulan_'.$triwulan])*100;
            else
                $item['persen_1'] = 0;
            if($item['triwulan_4']>0)
                $item['persen_2'] = ($item['real_'.$triwulan]/$item['triwulan_4'])*100;
            else
                $item['persen_2'] = 0;
            
            $result['items'][] = $item;
        }
        //add last total count
        $rkap_total [] = $total;
        
        //add to return value
        $result['total'] = $rkap_total;
        
        //create data for BRI Peduli tag=2, value=0;
        $sql = "SELECT id, type FROM program_types ORDER BY type";
        $bri_peduli = $db_obj->execSQL($sql);
        if ($bri_peduli)
        {
            $result['program_types'] = $bri_peduli;
            $realisasi_types = array();
            
            foreach($bri_peduli as $item)
            {
                $realisasi_item = array();
                //get realisasi foreach program type
                for($i=1;$i<=$triwulan;$i++)
                {
                    $max_month = $i*3;
                    $sql = "SELECT SUM((SELECT SUM(nominal) FROM budget_real_used WHERE program=p.id))
                            FROM programs p
                            WHERE (p.state=1)AND(p.type=".$item['id'].")AND(p.source=0)
                            AND((MONTH(p.approval_date)>=1)AND(MONTH(p.approval_date)<=$max_month)AND(YEAR(p.approval_date)=$year))";
                    $value = $db_obj->singleValueFromQuery($sql);
                    if ($value)
                        $realisasi_item[] = $value;
                    else
                        $realisasi_item[] = 0;
                }
                $realisasi_types[] = $realisasi_item;
            }
            $result['program_types_real'] = $realisasi_types;
        }
        
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function deleteRKAP($id_list)
{
    $db_obj = new DatabaseConnection();
    
    $sql = "DELETE FROM rkap WHERE (id IN (".$id_list."))";
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}
function loadRKAPComponent()
{
    $db_obj = new DatabaseConnection();
    $result = array("found"=>0, "error"=>'', "items"=>array());
    
    $sql = "SELECT co.id, co.category, co.caption, ca.caption as cat_caption,
            co.sort, co.tag, co.tag_value, ca.tag as cat_tag
            FROM rkap_component as co, rkap_category as ca
            WHERE (co.category = ca.id)
            ORDER BY ca.sort, co.sort";
    
    $components = $db_obj->execSQL($sql);
    if ($components){
        $result['found'] = count($components);
        $result['items'] = $components;
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function loadRKAPCategory()
{
    $db_obj = new DatabaseConnection();
    $result = array("found"=>0, "error"=>'', "items"=>array());
    
    $sql = "SELECT id, caption, sort, tag
            FROM rkap_category
            ORDER BY sort";
    
    $categories = $db_obj->execSQL($sql);
    if ($categories){
        $result['found'] = count($categories);
        $result['items'] = $categories;
    }else{
        $result['error'] = $db_obj->getLastError();
    }
    
    echo json_encode($result);
}
function saveRKAPComponent($id)
{
    $db_obj = new DatabaseConnection();
    
    $category = $_POST['category'];
    $caption = strip_tags($_POST['caption']);
    $tag = $_POST['tag'];
    $tag_value = $_POST['tag_value'];
    $sort = $_POST['sort'];
    
    if ($id==0)
    {
        $sql = "INSERT INTO rkap_component (category,caption,tag,tag_value,sort)VALUES
                ($category,'$caption',$tag,$tag_value,$sort)";
    }else{
        $sql = "UPDATE rkap_component SET category=$category,caption='$caption',tag=$tag,tag_value=$tag_value,sort=$sort
                WHERE (id=$id)";
    }
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}
function deleteRKAPComponent($id)
{
    $db_obj = new DatabaseConnection();
    
    $sql = "DELETE FROM rkap_component
            WHERE (id=$id)";
    
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}
function saveRKAPCategory($id)
{
    $db_obj = new DatabaseConnection();
    $caption = strip_tags($_POST['caption']);
    $tag = $_POST['tag'];
    $sort = $_POST['sort'];
    
    if ($id==0)
    {
        $sql = "INSERT INTO rkap_category (caption,tag,sort)VALUES
                ('$caption',$tag,$sort)";
    }else{
        $sql = "UPDATE rkap_category SET caption='$caption',tag=$tag,sort=$sort
                WHERE (id=$id)";
    }
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}
function deleteRKAPCategory($id)
{
    $db_obj = new DatabaseConnection();
    
    $sql = "DELETE FROM rkap_category
            WHERE (id=$id)";
    
    if ($db_obj->query($sql))
        echo 1;
    else
        echo ('0'.$db_obj->getLastError());
}

function getProgramRealisationByTriwulan($year)
{
    $db_obj = new DatabaseConnection();
    
    $triwulan = $_POST['triwulan'];
    $source = $_POST['source'];
        
    $real_value = getRealisationByTriwulan($year, $triwulan, $source, $db_obj);
    
    echo $real_value;
}
function getBenefTriwulan($year)
{
    $db_obj = new DatabaseConnection();
    
    $triwulan = $_POST['triwulan'];
    $source = $_POST['source'];
        
    $real_value = getBenefByTriwulan($year, $triwulan, $source, $db_obj);
    
    echo $real_value;
}
?>

