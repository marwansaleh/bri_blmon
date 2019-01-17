<?php
require_once("./../funcs/database.class_new.php"); 
require_once("./../funcs/db_config.php"); 

$db_obj = new Database();

$subject = $_GET['query'];

switch($subject){
    case 'program_types': get_program_types($db_obj); break;
    case 'programs': get_programs($db_obj); break;
    case 'program_detail': get_program_detail($db_obj); break;
    case 'kanwil': get_kanwil($db_obj); break;
    case 'uker_detail': get_uker($db_obj); break;
    case 'uker_list': get_uker_list($db_obj); break;
    case 'kabupaten_list': get_kabupaten_list($db_obj); break;
    case 'propinsi_list': get_propinsi_list($db_obj); break;
    case 'propinsi': loadProvinces($db_obj); break;
    case 'uker': loadUnitKerja($db_obj); break;
    case 'kabupaten': loadKabupatenKota($db_obj); break;
    case 'laporan_bulanan' : monthlyReport($db_obj); break;
    case 'laporan_tahunan' : yearlyReport($db_obj); break;
    default: error_404();
}

function _send_output($data, $http_code=200){
    if ($http_code != 200){
        http_response_code($http_code);
    }
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header('Content-type: application/json');
    
    echo json_encode($data);
}

function error_404(){
    $data = new stdClass();
    $data->status = 'Error';
    $data->error_message = 'Page Not Found';
    
    _send_output($data, 404);
}

function loadProvinces(Database $db_obj){
    if (!$db_obj) $db_obj = new Database();
    $return = new stdClass();
    $sql = "SELECT * FROM propinsi";
    $return = $db_obj->get($sql);
    _send_output($return);
}

function loadUnitKerja(Database $db_obj){
    if (!$db_obj) $db_obj = new Database();
    $return = new stdClass();
    $sql = "SELECT * FROM uker";
    $return = $db_obj->get($sql);
    _send_output($return);
}

function loadKabupatenKota(Database $db_obj){
    if (!$db_obj) $db_obj = new Database();
    $return = new stdClass();
    $sql = "SELECT a.propinsi namapropinsi,b.kabupaten,b.ibukota,b.luas,b.populasi,b.web FROM propinsi a JOIN kabupaten b ON(a.id=b.propinsi)";
    $return = $db_obj->get($sql);
    _send_output($return);
}

function get_program_types(Database $db_obj=NULL){
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; 
    
    $offset = ($page-1) * $limit;
    
    $return = new stdClass();
    $return->status = 0;
    $return->page = $page;
    $return->limit = $limit;
    $return->size = 0;
    $return->totalSize = 0;
    $return->totalPages = 0;
    $return->dataList = NULL;
    
    if (!$db_obj) $db_obj = new Database();
    
    $sql = 'SELECT COUNT(*) AS total FROM program_types';
    $totalSize = (int) $db_obj->get_single_row($sql)->total;
    $return->totalSize = $totalSize;
    
    $totalPages = ceil($totalSize / $limit);
    $return->totalPages = $totalPages;
    
    if ($totalSize > 0){
        $sql = 'SELECT id, type FROM program_types LIMIT '.$offset.','.$limit;
        $return->dataList = $db_obj->get($sql);
        $return->size = count($return->dataList);
    }
    
    _send_output($return);
}

function monthlyReport(Database $db_obj=NULL){
    $bulan = $_GET['bulan'];
    $tahun = $_GET['tahun'];
    $sql = 'SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
            p.state as approved_status, p.uker as uker_id, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
            DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
            DATE(p.approval_date) as approval_date, p.budget,
            p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
            FROM programs p, uker u, users us, propinsi pr, kabupaten k
            WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.state=1)
            AND MONTH(creation_date)="'.$bulan.'" AND YEAR(creation_date)="'.$tahun.'"
            ORDER BY p.approval_date desc';

    $return = $db_obj->get($sql);    
    _send_output($return);
}

function yearlyReport(Database $db_obj=NULL){
    $tahun = $_GET['tahun'];
    $sql = 'SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
            p.state as approved_status, p.uker as uker_id, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
            DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
            DATE(p.approval_date) as approval_date, p.budget,
            p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
            FROM programs p, uker u, users us, propinsi pr, kabupaten k
            WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.state=1)
            AND YEAR(creation_date)="'.$tahun.'"
            ORDER BY p.approval_date desc';

    $return = $db_obj->get($sql);    
    _send_output($return);
}

function get_programs(Database $db_obj=NULL){
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;    
    $type = isset($_GET['type']) ? (int) $_GET['type'] : NULL;
    $kanwil = isset($_GET['kanwil']) ? explode(',', $_GET['kanwil']) : NULL;
    
    $offset = ($page-1) * $limit;
    
    $return = new stdClass();
    $return->status = 0;
    $return->page = $page;
    $return->limit = $limit;
    $return->size = 0;
    $return->totalSize = 0;
    $return->totalPages = 0;
    $return->dataList = NULL;
    
    if (!$db_obj) $db_obj = new Database();
    //count total data of programs
    $sql = 'SELECT COUNT(*) as total FROM programs p WHERE (p.state=1)';
    if ($type){
        $sql.= ' AND (p.type='.$type.')';
    }
    if ($kanwil){
        $sql.= ' AND';
        if (count($kanwil) > 1)
        {
            $kanwil_filter = array();
            foreach($kanwil as $filter){
                $kanwil_filter [] = '(p.uker_wilayah='.$filter.')';
            }
            $sql.= '(' . implode('OR', $kanwil_filter) . ')';
        }else
        {
            $sql.= '(p.uker_wilayah='.$kanwil[0].')';
        }
    }
    
    $totalSize = (int)$db_obj->get_single_row($sql)->total;
    $return->totalSize = $totalSize;
    
    $totalPages = ceil($totalSize / $limit);
    $return->totalPages = $totalPages;
    
    if ($totalSize > 0){
        $sql = 'SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state as approved_status, p.uker as uker_id, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.state=1)';
        
        if ($type){
            $sql.= ' AND (p.type='.$type.')';
        }
        if ($kanwil){
            $sql.= ' AND';
            if (count($kanwil) > 1)
            {
                $kanwil_filter = array();
                foreach($kanwil as $filter){
                    $kanwil_filter [] = '(p.uker_wilayah='.$filter.')';
                }
                $sql.= '(' . implode('OR', $kanwil_filter) . ')';
            }else
            {
                $sql.= '(p.uker_wilayah='.$kanwil[0].')';
            }
        }
        
        $sql.= ' ORDER BY p.approval_date desc LIMIT '.$offset.','.$limit;
        
        $return->dataList = $db_obj->get($sql);
        $return->size = count($return->dataList);
        $return->status = 1;
    }
    
    _send_output($return);
}

function get_program_detail(){
    $program_id = isset($_GET['id']) ? intval($_GET['id']) : NULL;
    
    $return = new stdClass();
    $return->status = 0;
    if (!$program_id){
        $return->message = 'Not enough required parameter';
        _send_output($return);
        
        exit;
    }
    
    if (!$db_obj) $db_obj = new Database();
    $sql = 'SELECT p.id, p.name, p.description, p.pic, p.uker_wilayah, p.uker_cabang,
                p.state as approved_status, p.uker as uker_id, u.uker, pr.propinsi, k.kabupaten, p.benef_name,p.benef_orang, p.benef_unit, 
                DATE(p.creation_date) as creation_date, us.full_name as creation_by, 
                DATE(p.approval_date) as approval_date, p.budget,
                p.operational, p.creation_by as creation_by_id, u.propinsi as propinsi_id
                FROM programs p, uker u, users us, propinsi pr, kabupaten k
                WHERE (p.uker=u.id)AND(u.propinsi=pr.id)AND(u.kabupaten=k.id)AND(p.creation_by=us.id)AND(p.state=1)';
    $sql.= ' AND (p.id='.$program_id.')';
        
    $item = $db_obj->get_single_row($sql);
    if ($item){
        $return->status = 1;
        $return->item = $item;
    }else{
        $return->message = 'No program found';
    }
    
    _send_output($return);
}

function get_kanwil(Database $db_obj=NULL){
    $uker_wilayah_id = isset($_GET['id']) ? $_GET['id']: NULL;
    
    if (!$db_obj) $db_obj = new Database();
    
    if ($uker_wilayah_id){
        //check if this is kanwil
        $result = _get_uker($uker_wilayah_id, $db_obj);
        if ($result->tipe != 'KW'){
            $result = new stdClass();
            $result->error = 1;
            $result->message = 'Data is not found';
        }
    }else{
        $result = _get_kanwils($db_obj);
    }
    _send_output($result);
}

function get_uker(Database $db_obj){
    $uker_id = isset($_GET['id']) ? $_GET['id'] : NULL;
    if (!$uker_id){
        $result = new stdClass();
        $result->status = 'Error';
        $result->message = 'Uker is not found';
    }else{
        $result = _get_uker($uker_id, $db_obj);
    }
    
    _send_output($result);
}

function get_uker_list(Database $db_obj=NULL){
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;   
    $parent_id = isset($_GET['parent']) ? $_GET['parent'] : NULL;
    
    $offset = ($page-1) * $limit;
    
    $return = new stdClass();
    $return->status = 0;
    $return->page = $page;
    $return->limit = $limit;
    $return->size = 0;
    $return->totalSize = 0;
    $return->totalPages = 0;
    $return->dataList = NULL;
    
    if (!$db_obj) $db_obj = new Database();
    //count total data of programs
    $sql = 'SELECT COUNT(*) as total FROM uker';
    if ($parent_id){
        $sql.= ' WHERE (parent='.$parent_id.')';
    }
    
    $totalSize = (int)$db_obj->get_single_row($sql)->total;
    $return->totalSize = $totalSize;
    
    $totalPages = ceil($totalSize / $limit);
    $return->totalPages = $totalPages;
    
    if ($totalSize > 0){
        $sql = 'SELECT u.id, u.parent, u.wilayah, u.cabang, u.kode, u.uker, u.tipe, 
                u.alamat, u.kota, u.kabupaten as kabupaten_id, k.kabupaten, 
                u.propinsi as propinsi_id, p.propinsi, u.telepon, u.fax
                FROM uker u 
                LEFT JOIN kabupaten k
                INNER JOIN propinsi p
                ON k.propinsi=p.id
                ON u.kabupaten=k.id';
        
        if ($parent_id){
            $sql.= ' WHERE (u.parent='.$parent_id.')';
        }
        
        $sql.= ' LIMIT '.$offset.','.$limit;
        
        $return->dataList = $db_obj->get($sql);
        $return->size = count($return->dataList);
        $return->status = 1;
    }
    
    _send_output($return);
}

function get_kabupaten_list(Database $db_obj=NULL){
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;  
    
    $offset = ($page-1) * $limit;
    
    $return = new stdClass();
    $return->status = 0;
    $return->page = $page;
    $return->limit = $limit;
    $return->size = 0;
    $return->totalSize = 0;
    $return->totalPages = 0;
    $return->dataList = NULL;
    
    if (!$db_obj) $db_obj = new Database();
    //count total data of programs
    $sql = 'SELECT COUNT(*) as total FROM kabupaten';
    
    $totalSize = (int)$db_obj->get_single_row($sql)->total;
    $return->totalSize = $totalSize;
    
    $totalPages = ceil($totalSize / $limit);
    $return->totalPages = $totalPages;
    
    if ($totalSize > 0){
        $sql = 'SELECT * FROM kabupaten';
        
        $sql.= ' LIMIT '.$offset.','.$limit;
        
        $return->dataList = $db_obj->get($sql);
        $return->size = count($return->dataList);
        $return->status = 1;
    }
    
    _send_output($return);
}

function get_propinsi_list(Database $db_obj=NULL){
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;  
    
    $offset = ($page-1) * $limit;
    
    $return = new stdClass();
    $return->status = 0;
    $return->page = $page;
    $return->limit = $limit;
    $return->size = 0;
    $return->totalSize = 0;
    $return->totalPages = 0;
    $return->dataList = NULL;
    
    if (!$db_obj) $db_obj = new Database();
    //count total data of programs
    $sql = 'SELECT COUNT(*) as total FROM propinsi';
    
    $totalSize = (int)$db_obj->get_single_row($sql)->total;
    $return->totalSize = $totalSize;
    
    $totalPages = ceil($totalSize / $limit);
    $return->totalPages = $totalPages;
    
    if ($totalSize > 0){
        $sql = 'SELECT * FROM propinsi';
        
        $sql.= ' LIMIT '.$offset.','.$limit;
        
        $return->dataList = $db_obj->get($sql);
        $return->size = count($return->dataList);
        $return->status = 1;
    }
    
    _send_output($return);
}

/** helper function **/
function _get_uker($uker_id, Database $db_obj){
    $sql = 'SELECT u.id, u.parent, u.wilayah, u.cabang, u.kode, u.uker, u.tipe, 
                u.alamat, u.kota, u.kabupaten as kabupaten_id, k.kabupaten, 
                u.propinsi as propinsi_id, p.propinsi, u.telepon, u.fax
                FROM uker u 
                LEFT JOIN kabupaten k
                INNER JOIN propinsi p
                ON k.propinsi=p.id
                ON u.kabupaten=k.id 
            WHERE u.id='. $uker_id;
    
    return $db_obj->get_single_row($sql);
}

function _get_kanwils(Database $db_obj){
    $sql = 'SELECT * FROM uker WHERE tipe=\'KW\'';
    
    return $db_obj->get($sql);
}
?>
