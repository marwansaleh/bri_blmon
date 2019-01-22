<?php 
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

check_login();

$qs = str_ireplace(FOLDER_PREFIX, "", $_SERVER["REQUEST_URI"]);
$qs = explode("#", $qs);
array_shift($qs);

//check security uri, must do in every page
//to avoid http injection
$max_parameter_alllowed = 6;//5;
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
if (isset($qs[4])&&(int)$qs[4]>=0)
    $area = (int)$qs[4];
else
    $area = 0;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>    
<script type="text/javascript">    
    $(document).ready(function(){
        loadReport(); 
        $('h1.report-title').click(function(){
            var def_title = $(this).html();
            var new_title = prompt("Masukkan judul baru laporan",def_title);
            if (new_title)
                $(this).html(new_title);
        })
        $('select.report').change(function(){
            if ($('select#month_from').val()<=$('select#month_to').val()&&$('select#year_from').val()<=$('select#year_to').val()){
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
            
            loadReport();
        })
    }
    function loadReport()
    {    
        $('div#my-loader').show();
        var mode = $('select#type').val();
        var nama_bulan_from = getIndonesiaMonth($('select#month_from').val());
        var nama_bulan_to = getIndonesiaMonth($('select#month_to').val());
        var judul_laporan = 'Laporan Detail Bina Lingkungan';
        if (mode==0)
            judul_laporan += ' PER WILAYAH ';
        else
            judul_laporan += ' PER PROPINSI ';
        if (nama_bulan_from!=nama_bulan_to)
        {
            if ($('select#year_from').val()!=$('select#year_to').val())
                judul_laporan += nama_bulan_from +' '+$('select#year_from').val()+'-'+nama_bulan_to +' '+$('select#year_to').val();
            else
                judul_laporan += nama_bulan_from +'-'+nama_bulan_to+' '+ $('select#year_from').val();
                
        }else{
            if ($('select#year_from').val()!=$('select#year_to').val())
                judul_laporan += nama_bulan_from +' '+$('select#year_from').val()+'-'+$('select#year_to').val();
            else
                judul_laporan += nama_bulan_from +' '+ $('select#year_from').val();
        }
        $('h1.report-title').text(judul_laporan);
        $.post("ajax",{input_function:'loadReportDetail',param:mode,
            month_from:$('select#month_from').val(),
            year_from:$('select#year_from').val(),
            month_to:$('select#month_to').val(),
            year_to:$('select#year_to').val(),
            area:$('select#area').val(),
            rand:Math.floor(Math.random()*100)},function(result){
            
            data = jQuery.parseJSON(result);
            //empty table
            $('div.print').empty();
            
            if (data['found']['kanwil']>0){                
                var records_per_page = data['records'];
                var rows = 0;
                var s ="";
                //write table header
                var header = data['bidang'];
                var header_total_count = (header.length*3)+11;
                var col_num_count = (header.length*3)+3; //+2 for budget and operational
                
                var grand_total= new Array();
                var total_per_uker = new Array();
                var total_per_page = new Array();
                var total_per_kanwil = new Array();
                
                var total_per_kanwil_caption = "";
                if (mode==0)
                    total_per_kanwil_caption = "<strong><em>SUBTOTAL KANWIL</em></strong>";
                else
                    total_per_kanwil_caption = "<strong><em>SUBTOTAL PROPINSI</em></strong>";
                
                for(var z=0; z<col_num_count; z++)
                {
                    grand_total[z] = 0;
                    total_per_uker[z] = 0;
                    total_per_page[z] = 0;
                    total_per_kanwil[z] = 0;
                }   
                
                var nomor_urut_kanwil = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
                var nomor_urut_program = 1;
                
                for(var i in data['kanwil']['uker']){
                    var cabang_id=0; kanwil_id = data['kanwil']['uker'][i]['id'];                                     
                    
                    //Create header for first row
                    if (i==0){
                        createTableDataHeader(header);
                        rows = 0;
                    } 
                    
                    //show kanwil name
                    printKanwilName(nomor_urut_kanwil[i], header_total_count, data['kanwil']['uker'][i]['uker']);
                    rows++;                    
                    
                    if (rows%records_per_page==0){                        
                        //printTotal("<em>SubTotal</em>", total_per_uker, header_total_count, 5, 2,true);
                        printTotal("<em>SUBTOTAL per PAGE</em>", total_per_page, header_total_count, 8, 0,true,true,header);
                        rows=0;
                        //show kanwil name
                        printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                        rows++;  
                    }
                    
                    if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']]){ 
                        var kanwil_program_count = data['kanwil']['items'][data['kanwil']['uker'][i]['id']];
                        kanwil_program_count = kanwil_program_count.length;
                        for(var j in data['kanwil']['items'][data['kanwil']['uker'][i]['id']]){   
                            //store value id of programs
                            var program_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['id'];
                            
                            //sub total beda cabang
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id']!=cabang_id){
                                if (j>0){  
                                    if (rows>1){
                                        printTotal("<em>SubTotal</em>", total_per_uker, header_total_count, 6, 2,true);
                                        rows++;
                                    }
                                    //check if new page
                                    if (rows%records_per_page==0){                                        
                                        printTotal("<em>SUBTOTAL per PAGE</em>", total_per_page, header_total_count, 8, 0,true,true,header);
                                        rows=0;
                                        
                                        //show kanwil name
                                        printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                                        rows++; 
                                    }
                                }
                                cabang_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id'];
                            }
                            s="<tr>";
                            s+="<td>&nbsp;</td>";
                            s+="<td width='80' align='center'>"+(nomor_urut_program++)+"</td>";
                            s+="<td width='70'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker']+" ("+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['jenis_uker']+")</td>";
                            s+="<td width='50' align='center' class='updatable' lang='programs,approval_date,"+program_id+",0'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['approval_date']+"</td>";
                            s+="<td width='170' class='updatable' lang='programs,description,"+program_id+",0'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['description']+"</td>";                            
                            s+="<td width='170' class='updatable' lang='programs,potensi_bisnis,"+program_id+",0'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['potensi_bisnis']+"</td>";
                            s+="<td width='60' class='updatable' lang='programs,benef_name,"+program_id+",0'>"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_name']+"</td>";
                            var alamat_penerima = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_address'];
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_phone']!='')
                                alamat_penerima+=", Telp:"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_phone'];
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_email']!='')
                                alamat_penerima+=", Email:"+data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['benef_email'];
                            s+="<td width='60'>"+alamat_penerima+"</td>";
                            
                            var budget = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['budget'] * 1;
                            
                            var index = 0;
                            grand_total[index] += budget;
                            total_per_uker[index] += budget;
                            total_per_page[index] += budget;
                            total_per_kanwil[index] += budget;
                            index++;
                            s+="<td align='right' width='60' class='updatable' lang='programs,budget,"+program_id+",1'>"+budget.formatMoney(0,',','.')+"</td>";
                            
                            var real = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['real'] * 1;
                            total_per_uker[index] += real;
                            total_per_page[index] += real;
                            total_per_kanwil[index] += real;
                            grand_total[index] += real;
                            index++;
                            s+="<td align='right' width='60'>"+real.formatMoney(0,',','.')+"</td>";
                            
                            //tampilan per bidang
                            //var index = 1;
                            for (var k in data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang']){
                                var real_budget = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['real']*1;
                                grand_total[index] += real_budget;
                                total_per_uker[index] += real_budget;
                                total_per_page[index] += real_budget;
                                total_per_kanwil[index] += real_budget;
                                index++;
                                s+="<td align='right' width='60'>"+real_budget.formatMoney(0,',','.')+"</td>";
                                
                                var benef_orang = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['benef_orang']*1;
                                grand_total[index] += benef_orang;
                                total_per_uker[index] += benef_orang;
                                total_per_page[index] += benef_orang;
                                total_per_kanwil[index] += benef_orang;
                                index++;
                                s+="<td align='right' width='30' class='updatable' lang='programs,benef_orang,"+program_id+",1'>"+benef_orang.formatMoney(0,',','.')+"</td>";
                                
                                var benef_unit = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['bidang'][k]['benef_unit']*1;
                                grand_total[index] += benef_unit;
                                total_per_uker[index] += benef_unit;
                                total_per_page[index] += benef_unit;
                                total_per_kanwil[index] += benef_unit;
                                index++;
                                s+="<td align='right' width='30' class='updatable' lang='programs,benef_unit,"+program_id+",1'>"+benef_unit.formatMoney(0,',','.')+"</td>";        
                            }               
                            
                            var operational = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['operational'] * 1;
                            grand_total[index] += operational;
                            total_per_uker[index] += operational;
                            total_per_page[index] += operational;
                            total_per_kanwil[index] += operational;
                            s+="<td align='right' width='60' class='updatable' lang='programs,operational,"+program_id+",1'>"+operational.formatMoney(0,',','.')+"</td>";
                            
                            s+="</tr>";
                            rows++;
                            $("table.print:last").append(s); 
                            if (rows%records_per_page==0){
                                //Show sub total for sub uker
                                if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id']!=cabang_id||
                                    j==kanwil_program_count-1){
                                    printTotal("<em>SubTotal</em>", total_per_uker, header_total_count, 6, 2,true);
                                }
                                
                                printTotal("<em>SUBTOTAL Per PAGE</em>", total_per_page, header_total_count, 8, 0,true,true,header);                               
                                rows=0;
                                
                                //show kanwil name
                                printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                                rows++;  
                                
                            }
                            
                            //sub total beda uker
                            if (data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id']!=cabang_id||
                                    j==kanwil_program_count-1){
                                    
                                if (rows>1&&kanwil_id==data['kanwil']['uker'][i]['id']){                                    
                                    cabang_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id'];
                                    if (rows%records_per_page==0){
                                        //printTotal("<em>SubTotal</em>", total_per_uker, header_total_count, 5, 2,true,true,header);                               
                                        //printTotal("<em>SUBTOTAL per PAGE</em>", total_per_page, header_total_count, 7, 0,true,true,header);                               
                                        //rows=0;
                                    }else{
                                        printTotal("<em>SubTotal</em>", total_per_uker, header_total_count, 6, 2);
                                        rows++;
                                        if (rows%records_per_page==0){
                                            printTotal("<em>SUBTOTAL per PAGE</em>", total_per_page, header_total_count, 8, 0,true,true,header);
                                            rows = 0;
                                            
                                            //show kanwil name
                                            printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                                            rows++;  
                                        }
                                    }
                                }else{
                                    
                                    kanwil_id = data['kanwil']['uker'][i]['id'];
                                    cabang_id = data['kanwil']['items'][data['kanwil']['uker'][i]['id']][j]['uker_id'];
                                }
                            }
                        }                        
                        
                    }   else{
                        for(var n=0;n<col_num_count.length;n++){
                            total_per_uker[n] = 0;
                            total_per_page[n] = 0;
                            total_per_kanwil[n] = 0;
                        }
                    }       
                    //sub total per kanwil
                    if (rows%records_per_page==0){
                        printTotal(total_per_kanwil_caption, total_per_kanwil, header_total_count, 7, 1,true,true,header);
                        rows = 0;
                        //show kanwil name
                        printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                        rows++; 
                    }else{
                        printTotal(total_per_kanwil_caption, total_per_kanwil, header_total_count, 7, 1,true);                        
                        rows++;
                        
                        if (rows%records_per_page==0){
                            printTotal("<em>SUBTOTAL per PAGE</em>", total_per_page, header_total_count, 8, 0,true,true,header);
                            rows = 0;
                            //show kanwil name
                            printKanwilName(parseInt(i)+1, header_total_count, data['kanwil']['uker'][i]['uker']);
                            rows++;  
                        }
                    }
                }
                //show total
                printTotal("<strong><em>GRANDTOTAL</em></strong>", grand_total, header_total_count,8,0);
            }else{
                s="<tr><td>Data tidak ditemukan</td></tr>";
                $("table.print:last").append(s);
            }
            $('div#my-loader').hide();
            
            $('table.print td').click(function(){
                var ori_value = $(this).text();
                var new_value=prompt('Masukkan nilai baru di sel ini', ori_value);
                if (new_value||new_value==''){
                    $(this).text(new_value);
                    if ($(this).hasClass('updatable')&&ori_value!=new_value&&confirm('Simpan perubahan permanen ke database ?')){
                        //get all neccesary value
                        var var_array = $(this).attr('lang');
                        var_array = var_array.split(",");
                        if (var_array.length==4){
                            $('div#my-loader').show();
                            $.post('ajax',{input_function:'updateSingleValue',table:var_array[0],
                                field_name:var_array[1],
                                field_id_val:var_array[2],
                                field_type:var_array[3],
                                field_value:new_value},function(result){
                                $('div#my-loader').hide();
                                if (parseInt(result)==1)
                                    alert('Update berhasil');
                                else
                                    alert('Error. Update gagal dengan pesan:'+result.substr(1));
                            })
                        }else{
                            alert('Error. Missing variables');
                        }
                    }
                }
                
            })
        })
    }
    function printTotal(caption,total, header_total_count,number_colspan,number_indent,empty_count,new_page,header)
    {
        if (number_indent===undefined) number_indent = 1;
        if (number_colspan===undefined) number_colspan = 7;
        if (empty_count===undefined) empty_count = true;
        if (new_page===undefined) new_page = false;
        
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

        $("table.print:last").append(s);
        
        if (empty_count){
            //make it zero
            for(var n=0; n<total.length;n++)
            {
                total[n] = 0;
            }
        }
        if (new_page){
            //create header
            createTableDataHeader(header);
        }
    }
    function createTableDataHeader(header)
    {
        //create new table
        $('div.print').append("<table class='print'></table>");  
        //show header in new page                                   
        var s= "";        
        
        s+="<tr>";
        s+="<th rowspan='2'>No</th>";
        s+="<th rowspan='2' width='10'>Area</th>";
        s+="<th rowspan='2'>Uker</th>";
        s+="<th rowspan='2'>Tanggal<br />Realisasi</th>";
        s+="<th rowspan='2'>Deskripsi<br />Program</th>";
        s+="<th rowspan='2'>Potensi<br />Bisnis</th>";
        s+="<th colspan='2'>Penerima Manfaat</th>";
        s+="<th rowspan='2'>Budget</th>";
        s+="<th rowspan='2'>Real Budget</th>"
        for (var n in header){
            s+="<th colspan='3'>"+header[n]+"</th>";                    
        }
        s+="<th rowspan='2'>Operasional</th>";
        s+="</tr>"; 
        $("table.print:last").append(s);
        
        s="<tr>";
        s+="<th>Nama</th><th>Alamat</th>";
        for (var n in header){
            s+="<th>Real</th><th>Org</th><th>Unit</th>";
        }
        s+="</tr>";
        //write the table header
        $("table.print:last").append(s); 
    }
    function printKanwilName(no, header_total_count, kanwil_name)
    {
        var s ="<tr>";
        s+="<td align='center' width='25'>"+no+"</td>";
        s+="<td colspan='"+(header_total_count-1)+"'>"+kanwil_name+"</td>";
        s+="</tr>";
        $("table.print:last").append(s);
    }
</script>
<style media="print,screen">
    @media print{@page {size: landscape}}
    body{
        font-family:Tahoma, Geneva, sans-serif;
        font-size:11px;
        background: none;
    }
    table.print {
        border-spacing: 0;
        border: solid 1px #000000;
        page-break-after:always;
    }
    table.print:last-child{
        page-break-after: auto;
    }
    table.print th {
	font-size:8px;
        padding: 5px;
        border-left: solid 1px #000000;
        border-bottom: solid 1px #000000;
    }
    table.print td {
        padding: 2px;
        border-left: solid 1px #000000;
        border-bottom: solid 1px #000000;
		font-size:8px;
    }
    table.print td.double-border{border-top: solid 2px #000000;}
    h1.report-title {cursor: pointer;}
</style>
<style media="print">
    .no-print {display: none;}
    p.page-break {
        padding: 5px 0 5px 0;
        margin:  5px 0 5px 0;
        border: dotted 1px #ccc;
        font-size: 10px;
        font-style: italic;
        text-align: center;
        display: none;
    }
    #myHeader{display: none;}
</style>
</head>

<body>
    <h1 class="report-title" title="Klik untuk ubah judul">LAPORAN DETAIL BINA LINGKUNGAN</h1>
    <p class="no-print">
        <button id="btn_print" onclick="window.print();">Print</button>
        <button id="btn_close" onclick="window.close();">Close</button>
        Tipe
        <select id="type" name="type">
            <option value="0" <?php if ($type==0) echo " selected";?>>Per Wilayah</option>
            <option value="1" <?php if ($type==1) echo " selected";?>>Per Propinsi</option>
        </select>
        Area
        <select class="report" id="area" name="area">
            <option value="0" <?php echo ($area==0?'selected':'');?>>Semua <?php echo ($type==0?'Wilayah':'Propinsi');?></option>
            <?php
                //get areas as default
                if ($type==0)
                    $areas = load_kanwil($db_obj);
                else
                    $areas = load_propinsi($db_obj);
                if ($areas){
                    foreach($areas as $item){                        
                        echo "<option value='".$item['id']."'";
                        if ($area==$item['id']) echo ' selected';
                        echo ">";
                        if ($type==0)
                            echo $item['uker'];
                        else
                            echo $item['propinsi'];
                        echo "</option>";
                    }
                }
            ?>
        </select>
        Bulan&amp;Tahun
        <select class="report" id="month_from" name="month_from">
            <?php
            for($i=1;$i<=12;$i++){
                echo "<option value='".$i."'";
                if ($month_from==$i)
                    echo " selected";
                echo ">".get_indonesian_month($i)."</option>";
            }
            ?>
        </select>
        <select class="report" id="year_from" name="year_from">
            <?php
            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
            $years = $db_obj->execSQL($sql);
            foreach($years as $item){
                echo "<option value='".$item['trans_year']."'";
                if ($year_from==$item['trans_year'])
                    echo " selected";
                echo ">".$item['trans_year']."</option>";
            }
            ?>
        </select>
        s/d
        <select class="report" id="month_to" name="month_to">
            <?php
            for($i=1;$i<=12;$i++){
                echo "<option value='".$i."'";
                if ($month_to==$i)
                    echo " selected";
                echo ">".get_indonesian_month($i)."</option>";
            }
            ?>
        </select>
        <select class="report" id="year_to" name="year_to">
            <?php
            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
            $years = $db_obj->execSQL($sql);
            foreach($years as $item){
                echo "<option value='".$item['trans_year']."'";
                if ($year_to==$item['trans_year'])
                    echo " selected";
                echo ">".$item['trans_year']."</option>";
            }
            ?>
        </select>
    </p>
    <div class="print">
        <table class="print">
            <tr></tr>
        </table>
    </div>
    
    
</body>
</html>