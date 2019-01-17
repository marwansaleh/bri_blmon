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
$max_parameter_alllowed = 4;
security_uri_check($max_parameter_alllowed, $qs);

$db_obj = new DatabaseConnection();
//load user access
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">    
    $(document).ready(function(){
        loadReport(2);
        
        $('li#btn_home').click(function(){
            window.location = "./";
        })
        $('li#btn_go').click(function(){
            if ($('select#month_from').val()>$('select#month_to').val())
                alert('Pilihan bulan tidak valid');
            else if ($('select#year_from').val()>$('select#year_to').val())
                alert("Pilihan tahun tidak valid");
            else
                loadReport(2);
        })
        $('li#btn_print').click(function(){
            var month_from = $('select#month_from').val();
            var year_from = $('select#year_from').val();
            var month_to = $('select#month_to').val();
            var year_to = $('select#year_to').val();
            var fund_type = $('select#fund_type').val();
            
            var wnd = window.open("reports_print/2/"+month_from+","+month_to+"/"+year_from+","+year_to+"/"+fund_type,"Print");
            wnd.focus();
        })
        $('li#btn_export').click(function(){            
            var table_content = $('div.content:last').html();
            var filename = "report_propinsi.xls";
            
            
            $('div#my-loader').show();
            $.post('ajax',{input_function:'export_to_excel',param:filename,content:table_content},function(result){
                $('div#my-loader').hide();
                if (parseInt(result)==1){
                    var wnd = window.open("get_excel?filename="+filename,"EXPORTED");
                }else{
                    alert("Gagal mengekspor data ke excel");
                }
            })
        })
    })
    function loadReport(mode)
    {    
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadReport',param:mode,
            month_from:$('select#month_from').val(),
            year_from:$('select#year_from').val(),
            month_to:$('select#month_to').val(),
            year_to:$('select#year_to').val(),
            fund_type:$('select#fund_type').val()},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr").each(function(){
                $(this).remove();
            })
            if (data['found']>0){
                var s ="";
                //write table header
                var header = data['bidang']['caption'];
                s+="<tr>";
                s+="<th rowspan='2'>No</th>";
                s+="<th rowspan='2'>PROPINSI</th>";
                for (var i in header){
                    s+="<th colspan='3'>"+header[i]+"</th>";                    
                }
                s+="<th rowspan='2'>Total</th>";
                s+="<th colspan='2'>Penerima</th>";
                s+="</tr>";
                
                s+="<tr>";
                for (var i in header){
                    s+="<th>Rp</th>";
                    s+="<th>Org</th>";
                    s+="<th>Unit</th>";                  
                }
                s+="<th>Org</th>"; 
                s+="<th>Unit</th>"; 
                s+="</tr>";
                
                
                $("table.data-list").append(s);               
                
                //show table content
                for(var i in data['bidang']['kanwil']){
                    s ="<tr>";
                    s+="<td align='center'>"+(parseInt(i)+1)+"</td>";
                    s+="<td width='120'>"+data['bidang']['kanwil'][i]['uker']+"</td>";
                    //extract for each bidang
                    for(var j in data['bidang']['kanwil'][i]['bidang']){
                        s+="<td align='right'>"+data['bidang']['kanwil'][i]['bidang'][j]['budget']+"</td>";
                        s+="<td align='right'>"+data['bidang']['kanwil'][i]['bidang'][j]['benef_orang']+"</td>";
                        s+="<td align='right'>"+data['bidang']['kanwil'][i]['bidang'][j]['benef_unit']+"</td>";
                    }
                    s+="<td align='right'>"+data['bidang']['kanwil'][i]['tot_budget']+"</td>";
                    s+="<td align='right'>"+data['bidang']['kanwil'][i]['tot_benef_orang']+"</td>";
                    s+="<td align='right'>"+data['bidang']['kanwil'][i]['tot_benef_unit']+"</td>";
                    s+="</tr>";
                    $("table.data-list").append(s);                    
                }
                
                //show total in the bottom
                s="<tr>";
                s+="<td colspan='2' class='total'>TOTAL</td>";
                for (var i in data['bidang']['total']){
                    s+="<td align='right' class='total'>"+data['bidang']['total'][i]['budget']+"</td>";
                    s+="<td align='right' class='total'>"+data['bidang']['total'][i]['benef_orang']+"</td>";
                    s+="<td align='right' class='total'>"+data['bidang']['total'][i]['benef_unit']+"</td>";
                }
                s+="</tr>";
                
                $("table.data-list").append(s);
            }else{
                s="<tr><td>Data tidak ditemukan</td></tr>";
                $("table.data-list").append(s);
            }
        })
    }
</script>
<style type="text/css">
    table.data-list td.total{font-weight: bold;}
</style>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Laporan Bina Lingkungan BRI Per Propinsi</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="dropdown">Pilih Bulan dan Tahun
                        <select id="month_from" name="month_from">
                            <?php
                            for($i=1;$i<=12;$i++){
                                echo "<option value='".$i."'";
                                if (date("n")==$i) echo " selected";
                                echo ">".get_indonesian_month($i)."</option>";
                            }
                            ?>
                        </select>
                        <select id="year_from" name="year_from">
                            <?php
                            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
                            $years = $db_obj->execSQL($sql);
                            foreach($years as $item){
                                echo "<option value='".$item['trans_year']."'>".$item['trans_year']."</option>";
                            }
                            ?>
                        </select>
                        s/d
                        <select id="month_to" name="month_to">
                            <?php
                            for($i=1;$i<=12;$i++){
                                echo "<option value='".$i."'";
                                if (date("n")==$i) echo " selected";
                                echo ">".get_indonesian_month($i)."</option>";
                            }
                            ?>
                        </select>
                        <select id="year_to" name="year_to">
                            <?php
                            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
                            $years = $db_obj->execSQL($sql);
                            foreach($years as $item){
                                echo "<option value='".$item['trans_year']."'>".$item['trans_year']."</option>";
                            }
                            ?>
                        </select>
                    </li>
                    <li>
                        Pilihan dana
                        <select id="fund_type" name="fund_type">
                            <option value="0">Alokasi</option>
                            <option value="1">Realisasi</option>
                        </select>
                    </li>
                    <li class="execute" id="btn_go">GO</li>
                    <li class="execute" id="btn_print">Print</li>
                    <li class="execute" id="btn_export">Export XLS</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <p><strong>NB: Rp dalam ribuan</strong></p>
            <table class="data-list">                
                <tr></tr>
            </table>
        </div>
        <div class="clr"></div>
    </div>
    <?php echo document_footer();?>
</body>
</html>