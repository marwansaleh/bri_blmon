<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

check_login();

$qs = str_ireplace(FOLDER_PREFIX, "", $_SERVER["REQUEST_URI"]);
$qs = explode("/", $qs);
array_shift($qs);

//check security uri, must do in every page
//to avoid http injection
$max_parameter_alllowed = 2;
security_uri_check($max_parameter_alllowed, $qs);

//Create database Object
$db_obj = new DatabaseConnection();
//check if any ID defined, otherwise stop execution
if (isset($qs[1])){
    $id = $qs[1];
    $sql = "SELECT p.id, pt.type, p.source, p.name, p.description, 
            u.uker as uker_wilayah, u.alamat, u.telepon, u.fax, p.pic,
            DATE(p.creation_date)AS creation_date, r.full_name as creation_by,
            p.state, DATE(p.approval_date) AS approval_date, p.budget, 
            (SELECT SUM(nominal) FROM budget_real_used WHERE program=$id) AS budget_real,
            p.benef_name,p.benef_address,p.benef_phone,p.benef_email,p.benef_orang, 
            p.benef_unit, p.last_update
            FROM programs p, uker u, users r, program_types pt
            WHERE (p.id=$id)AND(p.uker_wilayah=u.id)AND(p.creation_by=r.id)AND(p.type=pt.id)";
    $program = $db_obj->execSQL($sql);
    if (!$program){
        exit($db_obj->getLastError()." Fatal error, data could not be loaded. <a href='#' onclick='window.close();'>Click</a> to close this window");
    }
}else{
    exit("Fatal error, ID not define. <a href='#' onclick='window.close();'>Click</a> to close this window");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script language="javascript" type="text/javascript" src="customs/js/tabs.js"></script>
<script language="javascript" type="text/javascript" src="customs/js/jquery.form.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        
    })
</script>
<style type="text/css" media="print,screen">
    table.detail-view
    {
        width: 680px;
        margin-left: auto;
        margin-right: auto;
        float: none;
        border: solid 1px #ccc;
        border-spacing: 0;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    table.detail-view th {
        background-color: #f8ad3c;
        font-size: 14px;
        padding: 7px;
        border-bottom: solid 1px #ccc;
    }
    table.detail-view td {
        padding: 7px;
        border-left: solid 1px #ccc;
    }
    table.detail-view td:first-child {border-left: none;}
    table.detail-view td table {
        width: 100%;
        border-spacing: 0;
    }    
</style>
<style type="text/css" media="print">
    .no-print{
        display: none;
        text-align: center;
    }
</style>
</head>

<body>
    <table class="detail-view">
        <tr>
            <th>NAMA PROGRAM</th>
        </tr>
        <tr>
            <td><h2><?php echo ($program[0]['name']); ?></h2></td>
        </tr>
        <tr>
            <th>DESKRIPSI PROGRAM</th>
        </tr>
        <tr>
            <td><?php echo ($program[0]['description']); ?></td>
        </tr>
    </table>
    <table class="detail-view">
        <tr>
            <th>BIDANG</th>
            <th>TIPE</th>
            <th>PIC</th>
        </tr>
        <tr>
            <td><?php echo $program[0]['type'];?></td>
            <td><?php echo ($program[0]['source']==0?"BRI PERDULI":"BUMN PERDULI");?></td>
            <td><?php echo $program[0]['pic'];?></td>
        </tr>
    </table>
    <table class="detail-view">
        <tr>
            <th>PROGRESS (%)</th>
            <th>ALOKASI / APPROVED (Rp)</th>
            <th>REALISASI (Rp)</th>
        </tr>
        <tr>
            <td align='right'><?php echo number_format(program_progress($program[0]['id'],$db_obj),2,",",".");?></td>
            <td align='right'><?php echo number_format($program[0]['budget'],2,",",".");?></td>
            <td align='right'><?php echo number_format(program_real_fund_used($program[0]['id'],$db_obj),2,",",".");?></td>
        </tr>
    </table>
    <table class="detail-view">
        <tr>
            <th>DIBUAT TANGGAL</th>
            <th>DIBUAT OLEH</th>
            <th>LAST UPDATE</th>
        </tr>
        <tr>
            <td align="center"><?php echo $program[0]['creation_date'];?></td>
            <td><?php echo strtoupper($program[0]['creation_by']);?></td>
            <td align="center"><?php echo $program[0]['last_update'];?></td>
        </tr>
    </table>
    <table class="detail-view">
        <tr>
            <th>NAMA UNIT KERJA</th>
            <th>ALAMAT</th>
            <th>TELPON</th>
            <th>FAX</th>
        </tr>
        <tr>
            <td><?php echo $program[0]['uker_wilayah'];?></td>
            <td><?php echo $program[0]['alamat'];?></td>
            <td><?php echo $program[0]['telepon'];?></td>
            <td><?php echo $program[0]['fax'];?></td>
        </tr>
    </table>
    <h3 style="text-align: center;">Penerima Manfaat</h3>
    <table class="detail-view">
        <tr>
            <th>NAMA PENERIMA</th>
            <th>ALAMAT PENERIMA</th>
            <th>TELPON</th>
            <th>EMAIL</th>
        </tr>
        <tr>
            <td><?php echo $program[0]['benef_name'];?></td>
            <td><?php echo $program[0]['benef_address'];?></td>
            <td><?php echo $program[0]['benef_phone'];?></td>
            <td><?php echo $program[0]['benef_email'];?></td>
        </tr>
    </table>
    <h3 style="text-align: center;">Detail Kegiatan</h3>
    <table class="detail-view">
        <tr>
            <th>NAMA KEGIATAN</th>
            <th>TARGET</th>
            <th>COMPLETED</th>
            <th>PROGRESS (%)</th>
            <th>LAST UPDATE</th>
        </tr>
        <?php
        $sql = "SELECT task, target, completed, 
                ((completed/target)*100)AS progress, 
                DATE(last_update) AS last_update
                FROM tasks WHERE program=$id";
        $tasks = $db_obj->execSQL($sql);
        if ($tasks)foreach($tasks as $task){
        ?>
        <tr>
            <td><?php echo ($task['task']); ?></td>
            <td align='right''><?php echo number_format($task['target'],0,",","."); ?></td>
            <td align='right''><?php echo number_format($task['completed'],0,",","."); ?></td>
            <td align='right''><?php echo number_format($task['progress'],2,",","."); ?></td>
            <td align="center"><?php echo ($task['last_update']); ?></td>
        </tr>
        <?php }?>
    </table>
    <h3 style="text-align: center;">Dokumentasi</h3>
    <table class="detail-view">
        <tr>
            <th>NAMA FILE</th>
            <th>TYPE</th>
            <th>ACTION</th>
        </tr>
        <?php
        $sql = "SELECT filename, filetype
                FROM doc_references WHERE program=$id";
        $docs = $db_obj->execSQL($sql);
        if ($docs)foreach($docs as $doc){
        ?>
        <tr>
            <td><?php echo ($doc['filename']); ?></td>
            <td align="center"><?php echo strtoupper($doc['filetype']); ?></td>
            <td align="center"><a href="view_docref?file=<?php echo $doc['filename'];?>" target="_blank">Lihat</a></td>
        </tr>
        <?php }?>
    </table>
    <p class="no-print">
        <button id="btn_print" name="btn_print" onclick="window.print();">PRINT</button>
        <button id="btn_close" name="btn_close" onclick="window.close();">CLOSE</button>
    </p>
</body>
</html>
