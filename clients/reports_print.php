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
$max_parameter_alllowed = 5;
security_uri_check($max_parameter_alllowed, $qs);

$db_obj = new DatabaseConnection();

if (isset($qs[1]))
    $type = $qs[1];
else
    $type = 1;

if (isset($qs[2]))
    $months = explode(",",$qs[2]);
else
    $months=array(date("m").",".date("m"));

if (isset($qs[3]))
    $years = explode(",",$qs[3]);
else
    $years = array(date("Y").",".date("Y"));
if (isset($qs[4]))
    $fund_type = $qs[4];
else
    $fund_type = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>    
<script type="text/javascript">    
    $(document).ready(function(){
        loadReport(); 
        $('button#btn_go').click(function(){ 
            var month_1 = getIndonesiaMonth($('select#month_from').val());
            var month_2 = getIndonesiaMonth($('select#month_to').val());
            
            if (month_1!=month_2){                
                $('span#time_desc').html(" "+month_1.toUpperCase()+" "+$('select#year_from').val()+" - "+month_2.toUpperCase()+" "+$('select#year_to').val())
            }else{
                $('span#time_desc').html(" "+month_1.toUpperCase()+" "+$('select#year_from').val());
            }
            loadReport();
        })
        $('h1.report-title').click(function(){
            var def_title = $(this).html();
            var new_title = prompt("Masukkan judul baru laporan",def_title);
            $(this).html(new_title);
        })
    })
    
    function loadReport()
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadReport',param:$('select#type').val(),
            month_from:$('select#month_from').val(),
            year_from:$('select#year_from').val(),
            month_to:$('select#month_to').val(),
            year_to:$('select#year_to').val(),
            fund_type:$('select#fund_type').val()},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            $('div.print:last').empty();
            $('div.print').append("<p><strong>NB: Rp dalam ribuan</strong></p>");
            if (data['found']>0){
                var records = data['records'];
                //create new header
                $('div.print:first').append("<table class='print'></table>");
                var s ="";
                //write table header
                var header = data['bidang']['caption'];
                s+="<tr>";
                s+="<th rowspan='2'>No</th>";
                s+="<th rowspan='2'>AREA</th>";
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
                
                
                $("table.print").append(s);  
                
                //show table content
                var page=0;
                for(var i in data['bidang']['kanwil']){
                    //create page break;
                    if (i>0&&i%records==0){
                        s="<tr>";
                        s+="<td colspan='2'>SUBTOTAL</td>";
                        for (var k in data['bidang']['subtotal'][page]){
                            s+="<td align='right'>"+data['bidang']['subtotal'][page][k]['budget']+"</td>";
                            s+="<td align='right'>"+data['bidang']['subtotal'][page][k]['benef_orang']+"</td>";
                            s+="<td align='right'>"+data['bidang']['subtotal'][page][k]['benef_unit']+"</td>";
                        }
                        s+="</tr>";
                        
                        $("table.print:last").append(s);
                        page++;
                        //show page break
                        s = "<p class='page-break'>Page break not printed (num of records per page set by admin)";
                        for(var x=0;x<210;x++)
                            s+="-";
                        s+= "</p>";
                        $('div.print:last').append(s);
                        //create new header
                        $('div.print:last').append("<table class='print'></table>");
                        s="<tr>";
                        s+="<th rowspan='2'>No</th>";
                        s+="<th rowspan='2'>AREA</th>";
                        for (var l in header){
                            s+="<th colspan='3'>"+header[l]+"</th>";                    
                        }
                        s+="<th rowspan='2'>Total</th>";
                        s+="<th colspan='2'>Penerima</th>";
                        s+="</tr>";

                        s+="<tr>";
                        for (var k in header){
                            s+="<th>Rp</th>";
                            s+="<th>Org</th>";
                            s+="<th>Unit</th>";                  
                        }
                        s+="<th>Org</th>"; 
                        s+="<th>Unit</th>"; 
                        s+="</tr>";
                        $("table.print:last").append(s);
                    }
                    s ="<tr>";
                    s+="<td align='center' width='40'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+(parseInt(i)+1)+"</td>";
                    s+="<td width='100'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['uker']+"</td>";
                    //extract for each bidang
                    for(var j in data['bidang']['kanwil'][i]['bidang']){
                        s+="<td align='right' width='110'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['bidang'][j]['budget']+"</td>";
                        s+="<td align='right' width='50'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['bidang'][j]['benef_orang']+"</td>";
                        s+="<td align='right' width='50'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['bidang'][j]['benef_unit']+"</td>";
                    }
                    s+="<td align='right' width='110'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['tot_budget']+"</td>";
                    s+="<td align='right' width='50'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['tot_benef_orang']+"</td>";
                    s+="<td align='right' width='50'"+(parseInt(i)==data['found']-1?" class='double-border'":"")+">"+data['bidang']['kanwil'][i]['tot_benef_unit']+"</td>";
                    s+="</tr>";
                    $("table.print:last").append(s);
                }
                
                
                //show total in the bottom
                s="<tr>";
                s+="<td colspan='2' class='double-border'>TOTAL</td>";
                for (var i in data['bidang']['total']){
                    s+="<td align='right' class='double-border'>"+data['bidang']['total'][i]['budget']+"</td>";
                    s+="<td align='right' class='double-border'>"+data['bidang']['total'][i]['benef_orang']+"</td>";
                    s+="<td align='right' class='double-border'>"+data['bidang']['total'][i]['benef_unit']+"</td>";
                }
                s+="</tr>";
                
                $("table.print:last").append(s);                
            }else{
                s="<tr><td>Data tidak ditemukan</td></tr>";
                $("table.print").append(s);
            }
        })
    }
</script>
<style media="print,screen">
    @media print{@page {size: landscape}}
    body{
        font-family:Tahoma, Geneva, sans-serif;
        font-size:12px;
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
        padding: 5px;
        border-left: solid 1px #000000;
        border-bottom: solid 1px #000000;
    }
    table.print td {
        padding: 2px;
        border-left: solid 1px #000000;
        border-bottom: solid 1px #000000;
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
</style>
</head>

<body>
    <h1 class="report-title" title="Klik untuk ubah judul">PENYALURAN DANA BINA LINGKUNGAN
        <?php
        if ($months[0].",".$years[0]==$months[1].",".$years[1]){
            echo "<span id='time_desc'>".strtoupper(get_indonesian_month($months[0]))." ". $years[0]."</span>";
        }else{
            echo "<span id='time_desc'>";
            echo strtoupper(get_indonesian_month($months[0]))." ". $years[0]." - ". strtoupper(get_indonesian_month($months[1]))." ". $years[1];
            echo "</span>";
        }?>
    </h1>
    <p class="no-print">
        <button id="btn_print" onclick="window.print();">Print</button>
        <button id="btn_close" onclick="window.close();">Close</button>
        Jenis laporan
        <select id="type" name="type">
            <option value="1" <?php if ($type==1) echo " selected";?>>Per Wilayah</option>
            <option value="2" <?php if ($type==2) echo " selected";?>>Per Propinsi</option>
        </select>
        Bulan &amp; Tahun
        <select id="month_from" name="month_from">
            <?php
            for($i=1;$i<=12;$i++){
                echo "<option value='".$i."'";
                if ($months[0]==$i)
                    echo " selected";
                echo ">".get_indonesian_month($i)."</option>";
            }
            ?>
        </select>
        <select id="year_from" name="year_from">
            <?php
            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
            $year = $db_obj->execSQL($sql);
            foreach($year as $item){
                echo "<option value='".$item['trans_year']."'";
                if ($years[0]==$i)
                    echo " selected";
                echo ">".$item['trans_year']."</option>";
            }
            ?>
        </select>
        s/d
        <select id="month_to" name="month_to">
            <?php
            for($i=1;$i<=12;$i++){
                echo "<option value='".$i."'";
                if ($months[1]==$i)
                    echo " selected";
                echo ">".get_indonesian_month($i)."</option>";
            }
            ?>
        </select>
        <select id="year_to" name="year_to">
            <?php
            $sql = "SELECT DISTINCT YEAR(trans_date) as trans_year FROM saldo";
            $year = $db_obj->execSQL($sql);
            foreach($year as $item){
                echo "<option value='".$item['trans_year']."'";
                if ($years[1]==$i)
                    echo " selected";
                echo ">".$item['trans_year']."</option>";
            }
            ?>
        </select>
        Pilihan dana
        <select id="fund_type" name="fund_type">
            <option value="0" <?php if ($fund_type==0) echo " selected";?>>Alokasi</option>
            <option value="1" <?php if ($fund_type==1) echo " selected";?>>Realisasi</option>
        </select>
        <button id="btn_go" name="btn_go">GO</button>
    </p>
    <div class="print">
        <table class="print">
            <tr></tr>
        </table>
    </div>
    
    
</body>
</html>