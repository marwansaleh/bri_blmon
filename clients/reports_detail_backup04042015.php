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

if (isset($qs[1]))
{
    $time = explode(",",$qs[1]);
    $month_from = (isset($time[0])&&(int)$time[0]>0?$time[0]:date('m'));
    $year_from = (isset($time[1])&&(int)$time[1]>0?$time[1]:date('y'));
}
else
{
    $month_from=date("m");
    $year_from = date("y");
}
if (isset($qs[2]))
{
    $time = explode(",",$qs[2]);
    $month_to = (isset($time[0])&&(int)$time[0]>0?$time[0]:date('m'));
    $year_to = (isset($time[1])&&(int)$time[1]>0?$time[1]:date('y'));
}
else
{
    $month_to=date("m");
    $year_to = date("y");
}
if (isset($qs[3])&&(int)$qs[3]>=0)
    $type = (int)$qs[3];
else
    $type = 0;
//load user access
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">    
    $(document).ready(function(){
        //loadArea();
        loadReport();
        
        $('li#btn_home').click(function(){
            window.location = "./";
        })
        
        $('li#btn_print').click(function(){
            var month_from = $('select#month_from').val();
            var year_from = $('select#year_from').val();
            var month_to = $('select#month_to').val();
            var year_to = $('select#year_to').val();
            
            var type = $('select#type').val();
            var area = $('select#area').val();
            
            var wnd = window.open("reports_detail_print/"+month_from+","+year_from+"/"+month_to+","+year_to+"/"+type+"/"+area,"Print");
            wnd.focus();
        })
        $('li#btn_export').click(function(){            
            var table_content = $('div.content:last').html();
            var filename = "report_detail.xls";
            
            
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
        $('select.report').change(function(){
            if (parseInt($('select#month_from').val())<=parseInt($('select#month_to').val())&&parseInt($('select#year_from').val())<=parseInt($('select#year_to').val())){
                loadReport();
            }else{
                alert('Range bulan atau tahun tidak valid');
            }
        })
        $('select#type').change(function(){
            loadArea();
        })
    })
    function loadArea()
    {
        $('div#my-loader').show();
        var mode = parseInt($('select#type').val());
        
        //empty the selection box
        $('select#area').empty();
        //add 1 row
        if (mode==0)
            $('select#area').append("<option value='0'>Semua Wilayah</option>");
        else
            $('select#area').append("<option value='0'>Semua Propinsi</option>");
        
        $.post("ajax",{input_function:'loadAreas',param:mode},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            if (data['found']>0){
                for (var i in data['items']){
                    if (mode==0)
                        s = "<option value='"+data['items'][i]['id']+"'>"+data['items'][i]['uker']+"</option>";
                    else
                        s = "<option value='"+data['items'][i]['id']+"'>"+data['items'][i]['propinsi']+"</option>";
                    
                    $('select#area').append(s);
                }
            }
            //loadReport
			loadReport();
        })
    }
    function loadReport()
    {    
        $('div#my-loader').show();
        var mode = $('select#type').val();
        var nama_bulan = getIndonesiaMonth($('select#month_from').val());
        
        //update title
        if (mode==0)
            $('h1.report-title').text('Laporan Detail Bina Lingkungan '+nama_bulan+' '+$('select#year_from').val()+' - PER WILAYAH');
        else
            $('h1.report-title').text('Laporan Detail Bina Lingkungan '+nama_bulan+' '+$('select#year_from').val()+' - PER PROPINSI');
        $.post("ajax",{input_function:'loadReportDetail',param:mode,
            month_from:$('select#month_from').val(),
            year_from:$('select#year_from').val(),
            month_to:$('select#month_to').val(),
            year_to:$('select#year_to').val(),
            area:$('select#area').val(),
            rand:Math.floor(Math.random()*100)},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr").each(function(){
                $(this).remove();
            })
            if (data['found']['kanwil']>0){                
                var s ="";
                //write table header
                var header = data['bidang'];
                var header_total_count = (header.length*3)+10;
                var col_num_count = (header.length*3)+2; //+2 for budget and operational
                
                s+="<tr>";
                s+="<th rowspan='2'>No</th>";
                s+="<th rowspan='2'>Area</th>";
                s+="<th rowspan='2'>Uker</th>";
                s+="<th rowspan='2'>Tanggal<br />Realisasi</th>";
                s+="<th rowspan='2'>Deskripsi<br />Program</th>";
                s+="<th rowspan='2'>Potensi<br />Bisnis</th>";
		s+="<th colspan='2'>Penerima Manfaat</th>";
                s+="<th rowspan='2'>Budget</th>";
                for (var i in header){
                    s+="<th colspan='3'>"+header[i]+"</th>";                    
                }
                s+="<th rowspan='2'>Operasional</th>";
                s+="</tr>";      
                $("table.data-list").append(s);
                s="<tr>";
                
                s+="<th>Nama</th><th>Alamat</th>";
                for (var i in header){
                    s+="<th>Real</th><th>Org</th><th>Unit</th>";
                }
                s+="</tr>";
                //write the table header
                $("table.data-list").append(s);               
                
                //show table content
                var grand_total= new Array();
                var total_uker = new Array();
                var total_kanwil = new Array();
                var total_per_kanwil_caption = "";
                if (mode==0)
                    total_per_kanwil_caption = "<strong><em>SUBTOTAL KANWIL</em></strong>";
                else
                    total_per_kanwil_caption = "<strong><em>SUBTOTAL PROPINSI</em></strong>";
                
                for(var z=0; z<col_num_count; z++)
                {
                    grand_total[z] = 0;
                    total_uker[z] = 0;
                    total_kanwil[z] = 0;
                }
                for(var i in data['kanwil']['uker']){                                        
                    //first row for kanwil name
                    var uker_id = 0;
                    var uker_name = "";
                    s ="<tr>";
                    s+="<td align='center' width='25'>"+(parseInt(i)+1)+"</td>";
                    s+="<td colspan='"+(header_total_count-1)+"'>"+data['kanwil']['uker'][i]['uker']+"</td>";
                    s+="</tr>";
                    $("table.data-list").append(s);
                    //next row for program list
                    
                    //extract for each program in this kanwil
                    
                    if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']]){
                        var kanwil_program_count = data['kanwil']['items'][data['kanwil']['uker'][i]['id']];
                        kanwil_program_count = kanwil_program_count.length;
                        for(var j in data['kanwil']['items'][data['kanwil']['uker'][i]['id']]){
                            var index = 0;
                            if (j>0&&uker_id!=data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id']){
                                //show sub per uker                                
                                printTotal("<em>SubTotal "+uker_name+"</em>",total_uker,header_total_count,6,2,true);
                                uker_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id'];
                                uker_name = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker']+" ("+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['jenis_uker']+")";
                            }else{
                                uker_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id'];
                                uker_name = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker']+" ("+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['jenis_uker']+")";
                            }
                            s="<tr>";
                            s+="<td>&nbsp;</td><td width='80'>&nbsp;</td>";
                            s+="<td width='70'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker']+" ("+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['jenis_uker']+")"+"</td>";
                            s+="<td width='70' align='center'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['approval_date']+"</td>";
                            s+="<td width='170'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['description']+"</td>";                            
                            s+="<td width='170'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['potensi_bisnis']+"</td>";
                            
                            s+="<td width='70'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_name']+"</td>";
                            var alamat_penerima = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_address'];
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_phone']!='')
                                alamat_penerima+=", Telp:"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_phone'];
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_email']!='')
                                alamat_penerima+=", Email:"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_email'];
                            s+="<td width='120'>"+alamat_penerima+"</td>";
                            
                            var budget = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['budget'] * 1;
                            total_uker[index] += budget;
                            total_kanwil[index] += budget;
                            grand_total[index] += budget;
                            index++;
                            
                            s+="<td align='right' width='60'>"+budget.formatMoney(0,',','.')+"</td>";
                            
                            //tampilan per bidang
                            //var index = 1;
                            for (var k in data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang']){
                                var real_budget = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['real']*1;
                                total_uker[index] += real_budget;
                                total_kanwil[index] += real_budget;
                                grand_total[index] += real_budget;
                                index++;
                                s+="<td align='right' width='60'>"+real_budget.formatMoney(0,',','.')+"</td>";
                                
                                var benef_orang = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['benef_orang']*1;
                                total_uker[index] += benef_orang;
                                total_kanwil[index] += benef_orang;
                                grand_total[index] += benef_orang;
                                index++;
                                s+="<td align='right' width='50'>"+benef_orang.formatMoney(0,',','.')+"</td>";
                                
                                var benef_unit = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['benef_unit']*1;
                                total_uker[index] += benef_unit;
                                total_kanwil[index] += benef_unit;
                                grand_total[index] += benef_unit;
                                index++;
                                s+="<td align='right' width='50'>"+benef_unit.formatMoney(0,',','.')+"</td>";        
                            }               
                            
                            var operational = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['operational'] * 1;
                            total_uker[index] += operational;
                            total_kanwil[index] += operational;
                            grand_total[index] += operational;
                            s+="<td align='right' width='60'>"+operational.formatMoney(0,',','.')+"</td>";
                            
                            s+="</tr>";
                            $("table.data-list").append(s);

                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id']!=uker_id||
                                    j==kanwil_program_count-1){
                                printTotal("<em>SubTotal "+uker_name+"</em>",total_uker,header_total_count,6,2,true);
                            }
                        }
                    }
                    //show sub total kanwil
                    printTotal(total_per_kanwil_caption,total_kanwil,header_total_count,7,1,true);
                }
                //show total
                printTotal("<strong>GRANDTOTAL</strong>",grand_total,header_total_count,8,0,true);
               
            }else{
                s="<tr><td>Data tidak ditemukan</td></tr>";
                $("table.data-list").append(s);
            }
        })
    }
    function printTotal(caption,total,header_total_count,number_colspan,number_indent,empty_count)
    {
        if (number_indent===undefined) number_indent = 1;
        if (number_colspan===undefined) number_colspan = 7;
        if (empty_count===undefined) empty_count = true;
        
        s="<tr>";
        for(var i=0; i<number_indent;i++){
            s+="<td>&nbsp;</td>";
        }
        
        s+="<td colspan='"+number_colspan+"'>"+caption+"</td>";
        for (var x=number_colspan,y=0; x<header_total_count-number_indent;x++){
            var nilai = total[y];
            y++;
            s+="<td align='right'>"+nilai.formatMoney(0,',','.')+"</td>";
        }
        s+="</tr>";

        $("table.data-list").append(s);
        
        if (empty_count){
            //make it zero
            for(var n=0; n<total.length;n++)
            {
                total[n] = 0;
            }
        }
    }
</script>
<style type="text/css">
    table.data-list th{font-size: 9px;}
    table.data-list td{font-size: 9px;}
    table.data-list td.total{font-weight: bold;}
</style>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 class="report-title">Laporan Detail Bina Lingkungan BRI</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li>Bulan&amp;Tahun
                        <select class="report" id="month_from" name="month_from">
                            <?php
                            for($i=1;$i<=12;$i++){
                                echo "<option value='".$i."'";
                                if ($month_from==$i) echo " selected";
                                echo ">".get_indonesian_month($i)."</option>";
                            }
                            ?>
                        </select>
                        <select class="report" id="year_from" name="year_from">
                            <?php
                            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
                            $years = $db_obj->execSQL($sql);
                            if ($years)
                            {
                                foreach($years as $item){
                                    echo "<option value='".$item['trans_year']."'";
                                    if ($year_from==$item['trans_year']) echo ' selected';
                                    echo ">".$item['trans_year']."</option>";
                                }
                            }else{
                                echo "<option value='".date("y")."'>".date("Y")."</option>";
                            }
                            ?>
                        </select>
                        s/d
                        <select class="report" id="month_to" name="month_to">
                            <?php
                            for($i=1;$i<=12;$i++){
                                echo "<option value='".$i."'";
                                if ($month_to==$i) echo " selected";
                                echo ">".get_indonesian_month($i)."</option>";
                            }
                            ?>
                        </select>
                        <select class="report" id="year_to" name="year_to">
                            <?php
                            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
                            $years = $db_obj->execSQL($sql);
                            if ($years)
                            {
                                foreach($years as $item){
                                    echo "<option value='".$item['trans_year']."'";
                                    if ($year_to==$item['trans_year']) echo ' selected';
                                    echo ">".$item['trans_year']."</option>";
                                }
                            }else{
                                echo "<option value='".date("y")."'>".date("Y")."</option>";
                            }
                            ?>
                        </select>
                    </li>
                    <li>
                        Tipe
                        <select id="type" name="type">
                            <option value="0">Wilayah</option>
                            <option value="1">Propinsi</option>
                        </select>
                    </li>
                    <li>
                        Area
                        <select class="report" id="area" name="area">
                            <option value="0" selected>Semua Wilayah</option>
                            <?php
                            //get kanwil as default
                            $areas = load_kanwil($db_obj);
                            if ($areas){
                                foreach($areas as $item){
                                    echo "<option value='".$item['id']."'>".$item['uker']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </li>
                    <li class="execute" id="btn_print">Print</li>
                    <li class="execute" id="btn_export">Export XLS</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">                
                <tr></tr>
            </table>
        </div>
        <div class="clr"></div>
    </div>
    <?php echo document_footer();?>
</body>
</html>