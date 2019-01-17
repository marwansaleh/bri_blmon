<?php 
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$db_obj = new DatabaseConnection();

//load user access
$access = loadUserAccess($db_obj);
function get_rkap_year(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    //get any year from existing data
    $sql = "SELECT DISTINCT tahun FROM rkap ORDER BY tahun";
    $year_result = $db_obj->execSQL($sql);
    //check if current year exist in the array
    $cur_year = date("Y");
    if ($year_result)
    {
        $year_options  = array();
        foreach($year_result as $year)
            $year_options[] = $year['tahun'];
        
        if (!in_array($cur_year, $year_options))
            $year_options[] = $cur_year;
        if (!in_array($cur_year-1, $year_options))
            $year_options[] = $cur_year-1;
        if (!in_array($cur_year+1, $year_options))
            $year_options[] = $cur_year+1;
                                    
        //sort the year options
        sort($year_options);
    }else{
        $year_options = array($cur_year-1,$cur_year,$cur_year+1);
    }
    
    return $year_options;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript"> 
    $(document).ready(function(){
        //load rkap
        load_rkap($('select#tahun').val(),$('select#triwulan').val());
        $('select#tahun').change(function(){
            load_rkap($(this).val(),$('select#triwulan').val());
        })
        $('select#triwulan').change(function(){
            load_rkap($('select#tahun').val(),$(this).val());
        })
        $('li#btn_home').click(function(){
            window.location = "./";
        })
        $('li#btn_print').click(function(){
            var tahun = $('select#tahun').val();
            var triwulan = $('select#triwulan').val();
            var wnd = window.open("reports_rkap_print/"+tahun+"/"+triwulan,"Print");
            wnd.focus();
        })
        $('li#btn_export').click(function(){            
            var table_content = $('div.content:last').html();
            var filename = "report_rkap.xls";
            
            
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
    function load_rkap(year,triwulan)
    {
        $('div#my-loader').show();
        $('div#content-data').empty();
        $.post("ajax",{input_function:'loadRKAP_Report',param:year,triwulan:triwulan},function(result){
            $('div#my-loader').hide();
            
            //extract data as JSON
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                //create table data skeleton
                create_table_header(triwulan);
                
                var category_check = 0;
                var category_num = 0;
                var item_num = 0;
                for (var i in data['items']){
                    var s = "";
                    if (category_check!=parseInt(data['items'][i]['category'])){
                        category_check = parseInt(data['items'][i]['category']);
                        category_num++;                        
                        item_num = 1;
                        
                        s+="<tr class='row-msg head-rkap'>";
                        s+="<td align='center'><strong>"+category_num+"</strong></td>";                        
                        s+="<td><strong>"+data['items'][i]['cat_caption']+"</strong></td>";
                        var rkap = data['total'][category_num-1]['rkap']*1;
                        if (rkap!=0)
                            s+="<td align='right'><strong>"+rkap.formatMoney(0,',','.')+"</strong></td>";
                        else
                            s+="<td align='right'>-</td>";
                        var triwulan_1 = data['total'][category_num-1]['triwulan_1']*1;
                        if (triwulan_1!=0)
                            s+="<td align='right'><strong>"+triwulan_1.formatMoney(0,',','.')+"</strong></td>";
                        else
                            s+="<td align='right'>-</td>";
                        var triwulan_2 = data['total'][category_num-1]['triwulan_2']*1;
                        if (triwulan_2!=0)
                            s+="<td align='right'><strong>"+triwulan_2.formatMoney(0,',','.')+"</strong></td>";
                        else
                            s+="<td align='right'>-</td>";
                        var triwulan_3 = data['total'][category_num-1]['triwulan_3']*1;
                        if (triwulan_3!=0)
                            s+="<td align='right'><strong>"+triwulan_3.formatMoney(0,',','.')+"</strong></td>";
                        else
                            s+="<td align='right'>-</td>";
                        var triwulan_4 = data['total'][category_num-1]['triwulan_4']*1;
                        if (triwulan_4!=0)
                            s+="<td align='right'><strong>"+triwulan_4.formatMoney(0,',','.')+"</strong></td>";
                        else
                            s+="<td align='right'>-</td>";
                        
                        //start realisation
                        for(var k=1;k<=triwulan;k++){
                            var real = data['total'][category_num-1]['real_'+k]*1;
                            if (real!=0)
                                s+="<td align='right'><strong>"+real.formatMoney(0,',','.')+"</strong></td>";
                            else
                                s+="<td align='right'>-</td>";
                        }
                        //start persentase realisation
                        for (var k=1;k<=2;k++){
                            var persen = data['total'][category_num-1]['persen_'+k]*1;
                            if(persen!=0)
                                s+="<td align='right'><strong>"+persen.formatMoney(2,',','.')+"%</strong></td>";
                            else
                                s+="<td align='right'>-</td>";
                        }
                        s+="</tr>";
                    }
                    s+="<tr class='row-msg' id='"+data['items'][i]['id']+"'>";                    
                    s+="<td width='50' align='center'>"+category_num+"."+item_num+"</td>";
                    s+="<td>"+data['items'][i]['caption']+"</td>";
                    var rkap = data['items'][i]['rkap']*1;
                    if (rkap!=0)
                        s+="<td align='right'>"+rkap.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right'>-</td>";
                    
                    var triwulan_1 = data['items'][i]['triwulan_1']*1;
                    if(triwulan_1!=0)
                        s+="<td align='right'>"+triwulan_1.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right'>-</td>";
                    
                    var triwulan_2 = data['items'][i]['triwulan_2']*1;
                    if (triwulan_2!=0)
                        s+="<td align='right'>"+triwulan_2.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right'>-</td>";
                    
                    var triwulan_3 = data['items'][i]['triwulan_3']*1;
                    if(triwulan_3!=0)
                        s+="<td align='right'>"+triwulan_3.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right'>-</td>";
                    
                    var triwulan_4 = data['items'][i]['triwulan_4']*1;
                    if(triwulan_4!=0)
                        s+="<td align='right'>"+triwulan_4.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right'>-</td>";
                    
                    //start realisation
                    for (var k=1;k<=triwulan;k++){
                        var real = data['items'][i]['real_'+k]*1;
                        if(real!=0)
                            s+="<td align='right'>"+real.formatMoney(0,',','.')+"</td>";
                        else
                            s+="<td align='right'>-</td>";
                    }
                    
                    //start persentase realisation
                    for (var k=1;k<=2;k++){
                        var persen = data['items'][i]['persen_'+k]*1;
                        if(persen!=0)
                            s+="<td align='right'>"+persen.formatMoney(2,',','.')+"%</td>";
                        else
                            s+="<td align='right'>-</td>";
                    }
                    
                    s+="</tr>";
                    
                    item_num++;
                    $('table.data-list').append(s);
                    
                    //insert program type by checking tag=2 and tag value=0;
                    if (data['items'][i]['tag']=='2'&&data['items'][i]['tag_value']=='0'){
                        for(var j in data['program_types']){
                            var s = "<tr class='row-msg'>";
                            s+="<td>&nbsp;</td>"; //nomor
                            s+="<td>"+data['program_types'][j]['type']+"</td>";
                            var empty = 0;
                            while(empty<5){
                                s+="<td>&nbsp;</td>"; //nomor
                                empty++;
                            }
                            for(var k=1;k<=triwulan;k++){
                                var type_value = data['program_types_real'][j][(k-1)]*1;
                                if(type_value!=0)
                                    s+="<td align='right'>"+type_value.formatMoney(0,',','.')+"</td>";
                                else
                                    s+="<td align='right'>-</td>";
                            }
                            //no persentase in program type
                            s+="<td>&nbsp;</td><td>&nbsp;</td>";
                            
                            s+="</tr>";
                            $('table.data-list').append(s);
                        }                        
                    }
                }
            }else{
                s = "Data tidak ditemukan";
                $('div#content-data').append(s);
            }
        })
    }
    function create_table_header(triwulan)
    {
        var triwulan_order = ["0","I","II","III","IV"];
        var s="";
        s+="<table class='data-list'>";
        s+="<tr>";
            s+="<th rowspan='3'>NO</th>";
            s+="<th rowspan='3'>U R A I A N</th>";
            s+="<th rowspan='3'>RKAP</th>";
            s+="<th colspan='4'>BREAK DOWN</th>";
            s+="<th colspan='"+(triwulan+2)+"'>REALISASI</th>";
        s+="</tr>";
        s+="<tr>";
            s+="<th>Triwulan</th>";
            s+="<th>Triwulan</th>";
            s+="<th>Triwulan</th>";
            s+="<th>Triwulan</th>";
            //Realisasi
            for(var i=1;i<=triwulan;i++)
                s+="<th>Triwulan</th>";
            s+="<th colspan='2'>Pencapaian</th>";
        s+="</tr>";
        s+="<tr>";
            s+="<th>I</th>";
            s+="<th>II</th>";
            s+="<th>III</th>";
            s+="<th>IV</th>";
            //realisasi
            for(var i=1;i<=triwulan;i++)
                s+="<th>"+triwulan_order[i]+"</th>";
            s+="<th>Thd Triwln "+triwulan_order[triwulan]+"</th>";
            s+="<th>Thd Ttl Angg</th>";
        s+="</tr>";
        s+="</table>";
        $('div#content-data').append(s);
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
            <h1>Rencana Kerja Anggaran</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li>Tahun Anggaran
                        <select id="tahun" name="tahun">
                            <?php
                            $year_options = get_rkap_year($db_obj);
                            $cur_year = date("Y");
                            if ($year_options)foreach($year_options as $year){
                                echo "<option value='".$year."'";
                                if ($year==$cur_year) echo ' selected';
                                echo ">".$year."</option>";
                            }
                            ?>
                        </select>
                    </li>
                    <li>Realisasi Triwulan
                        <select id="triwulan" name="triwulan">
                            <option value="1">Triwulan I</option>
                            <option value="2">Triwulan II</option>
                            <option value="3">Triwulan III</option>
                            <option value="4">Triwulan IV</option>
                        </select>
                    </li>
                    <li class="execute" id="btn_print">Print</li>
                    <li class="execute" id="btn_export">Export XLS</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content" id="content-data">
            <table class="data-list">
                <!--
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">U R A I A N</th>
                    <th rowspan="3">RKAP</th>
                    <th colspan="4">Break Down</th>
                    <th colspan="5">REALISASI</th>
                </tr>
                <tr>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th>Triwulan</th>
                    <th colspan="2">Pencapaian</th>
                </tr>
                <tr>
                    <th>I</th>
                    <th>II</th>
                    <th>III</th>
                    <th>IV</th>
                    <th>I</th>
                    <th>II</th>
                    <th>III</th>
                    <th>Thd Triwln III</th>
                    <th>Thd Ttl Angg</th>
                </tr>
                -->
            </table>
        </div>
    </div>
    <?php echo document_footer();?>
</body>
</html>