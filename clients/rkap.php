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
    var my_dlg;
    $(document).ready(function(){
        my_dlg = new Dialog("my-dialog-csr", "Komponen", [700,500]);
        //load rkap
        load_rkap($('select#tahun').val());
        $('select#tahun').change(function(){
            load_rkap($(this).val());
        })
        $('li#btn_home').click(function(){
            window.location = "./";
        })
        $('li#btn_add').click(function(){
            window.location = "rkap_update";
        })
        $('li#btn_edit').click(function(){
            if($('tr.row-msg').length==0){
                alert('Tidak ada data untuk edit');
                return;
            }
            var id = [];
            $("table.data-list :checked").each ( function ()
            {
                id.push($(this).val());
            });
            if(id.length<1||id.length>1)
                alert("Pilih / checked satu record yang akan diedit");
            else
                window.location = "rkap_update/"+id[0];
        })
        $('li#btn_delete').click(function(){
            if($('tr.row-msg').length==0){
                alert('Tidak ada data untuk dihapus');
                return;
            }
            var id = [];
            $("table.data-list :checked").each ( function ()
            {
                id.push($(this).val());
            });
            if(id.length<1)
                alert("Pilih / checked record yang akan dihapus");
            else if (confirm("Hapus komponen RKAP terpilih ?")){
                deleteRecords(id);
            }
        })
        $('li#btn_report').click(function(){
            window.location = "reports_rkap";
        })
        $('li.dropdown').hover(
            function(){
                $('ul', this).show();
            },
            function(){
                $('ul',this).hide();
            }
        )
        $('li#drp-category').click(function(){
            my_dlg.setDialogTitle($(this).text());
            my_dlg.setDimension(600, 450);
            my_dlg.showDialog();
            
            var s = "<div style='float:left;width:100%; padding-bottom: 10px;'>";
            s+= "<table style='border:solid 1px #ccc; width:99%;'>";
                s+="<tr>";
                    s+="<td>Kelompok komponen</td>";
                    s+="<td>Tag</td>";
                    s+="<td>Urutan</td>";
                    s+="<td>&nbsp;</td>";
                s+="</tr>";
                s+="<tr>";
                    s+="<td><input type='text' id='category' style='width:250px;' /></td>";
                    s+="<td><input type='text' id='cat_tag' style='width:50px;text-align:right;' /></td>";
                    s+="<td><input type='text' id='cat_sort' style='width:50px;text-align:right;' /></td>";
                    s+="<td align='right'>";
                        s+="<input type='button' id='btn_save_category' value='Save' />";
                        s+="<input type='button' id='btn_new_category' value='New' />";
                        s+="<input disabled='disabled' type='button' id='btn_del_category' value='Del' />";
                    s+="</td>";
                s+="</tr>";
            s+="</table>";
            s+="<input type='hidden' id='category_id' value='0' />";
            s+= "</div>";
            my_dlg.setDialogContent(s);
            $('input#btn_save_category').click(function(){
                if ($('input#category').val()==''){
                    alert('Nama kelompok komponen tidak boleh kosong');
                    return;
                }
                //save component;
                save_rkap_category($('input#category_id').val(),$('input#category').val(),$('input#cat_tag').val(),$('input#cat_sort').val());
                $('input#btn_new_category').click();
            })
            $('input#btn_new_category').click(function(){
                $('input#category_id').val('0');
                $('input#category').val('');
                $('input#cat_tag').val('0');
                $('input#cat_sort').val('0');
                
                $('input#btn_del_category').attr('disabled','disabled');
            })
            $('input#btn_del_category').click(function(){
                if ($('input#category_id').val()!=0&&confirm('Hapus kelompok komponen ini ?')){
                    delete_rkap_category($('input#category_id').val());
                    
                    $('input#btn_new_category').click();
                }
            })
            //load component
            load_rkap_category();
        })
        
        $('li#drp-component').click(function(){
            my_dlg.setDialogTitle($(this).text());
            my_dlg.setDimension(800,500);
            my_dlg.showDialog();
            
            var s = "<div style='float:left;width:100%; padding-bottom: 10px;'>";
            s+= "<table style='border:solid 1px #ccc; width:99%;'>";
                s+="<tr>";
                    s+="<td>Kelompok</td>";
                    s+="<td>Komponen</td>";
                    s+="<td>Tag</td>";
                    s+="<td>TagValue</td>";
                    s+="<td>Sort</td>";
                    s+="<td>&nbsp;</td>";
                s+="</tr>";
                s+="<tr>";
                    s+="<td>";
                        s+="<select id='category'>";
                            s+="<option value='0'>--Pilih kelompok--</option>";
                        s+="</select>";
                    s+="</td>";
                    s+="<td><input type='text' id='component' style='width:200px;' /></td>";  
                    s+="<td><input type='text' id='co_tag' style='width:50px;text-align:right;' /></td>";
                    s+="<td><input type='text' id='co_tag_value' style='width:50px;text-align:right;' /></td>";
                    s+="<td><input type='text' id='co_sort' style='width:50px;text-align:right;' /></td>";
                    s+="<td align='right'>";
                        s+="<input type='button' id='btn_save_component' value='Save' />";
                        s+="<input type='button' id='btn_new_component' value='New' />";
                        s+="<input disabled='disabled' type='button' id='btn_del_component' value='Del' />";
                    s+="</td>";
                s+="</tr>";
            s+="</table>";
            s+="<input type='hidden' id='component_id' value='0' />";
            s+= "</div>";
            my_dlg.setDialogContent(s);
            $('input#btn_save_component').click(function(){
                if ($('select#category').val()==0){
                    alert('Kelompok belum dipilih');
                    return;
                }
                if ($('input#component').val()==''){
                    alert('Nama komponen tidak boleh kosong');
                    return;
                }
                //save component;
                save_rkap_component($('input#component_id').val(),$('select#category').val(),$('input#component').val(),$('input#co_tag').val(),$('input#co_tag_value').val(),$('input#co_sort').val());
                $('input#btn_new_component').click();
            })
            $('input#btn_new_component').click(function(){
                $('select#category').val(0);
                $('input#component_id').val('0');
                $('input#component').val('');
                $('input#co_tag').val('0');
                $('input#co_tag_value').val('0');
                $('input#co_sort').val('0');
                
                $('input#btn_del_component').attr('disabled','disabled');
            })
            $('input#btn_del_component').click(function(){
                if ($('input#component_id').val()!=0&&confirm('Hapus komponen ini ?')){
                    delete_rkap_component($('input#component_id').val());
                    
                    $('input#btn_new_component').click();
                }
            })
            //load category
            $.post('ajax',{input_function:'loadRKAPCategory'},function(result){
                var data = jQuery.parseJSON(result);
                if (data['found']>0){
                    for (var i in data['items']){
                        s = "<option value='"+data['items'][i]['id']+"'>";
                        s+= data['items'][i]['caption'];
                        s+= "</option>";
                        $('select#category').append(s);
                    }
                }
            });
            
            //load component
            load_rkap_component();
        })
    })
    function load_rkap_category()
    {
        $('div#my-loader').show();
        
        $.post('ajax',{input_function:'loadRKAPCategory'},function(result){
            $('div#my-loader').hide();
            var data = jQuery.parseJSON(result);
            var s= "";
            //create table header
            if ($('table#msg-dialog').length==0)
            {
                s = "<table id='msg-dialog' class='data-list'>";
                s+="<tr><th>No</th><th>Nama Kelompok</th><th>Tag</th><th>Urutan</th></tr>";
                s+= "</table>";
                my_dlg.appendDialogContent(s);
            }else{
                $('table#msg-dialog tr.msg-dlg-data').each(function(){
                    $(this).remove();
                })
            }
            
                
            if (data['found']>0){
                //start iterate
                for(var i in data['items']){
                    s= "<tr class='msg-dlg-data' id='"+data['items'][i]['id']+"' style='cursor:pointer;'>";
                    s+="<td align='center'>"+(parseInt(i)+1)+"</td>";
                    s+="<td>"+data['items'][i]['caption']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['tag']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['sort']+"</td>";
                    s+="</tr>";
                    $('table#msg-dialog').append(s);
                }
            }else{
                if (data['error']!='')
                    s="<tr class='msg-dlg-data'><td colspan='2'>"+data['error']+"</td></tr>";
                else
                    s="<tr class='msg-dlg-data'><td colspan='2'>Data tidak ditemukan</td></tr>";
                    
                $('table#msg-dialog').append(s);
            }
                
            $('tr.msg-dlg-data').click(function(){
                $('input#category_id').val($(this).attr('id'));
                $('input#category').val($(this).find('td').eq(1).text());
                $('input#cat_tag').val($(this).find('td').eq(2).text());
                $('input#cat_sort').val($(this).find('td').eq(3).text());
                
                //enabled del button
                $('input#btn_del_category').removeAttr('disabled');
            })
        })
    }
    function save_rkap_category(id, category, tag, sort)
    {
        $('div#my-loader').show();
        $.post('ajax',{input_function:'saveRKAPCategory',param:id,caption:category,tag:tag,sort:sort},function(result){
            $('div#my-loader').hide();
            if (parseInt(result)==1)
                load_rkap_category();
            else
                alert(result.substr(1));
        })
    }
    function delete_rkap_category(id)
    {
        $('div#my-loader').show();
        $.post('ajax',{input_function:'deleteRKAPCategory',param:id},function(result){
            $('div#my-loader').hide();
            if (parseInt(result)==1)
                load_rkap_category();
            else
                alert(result.substr(1));
        })
    }
    function load_rkap_component()
    {
        $('div#my-loader').show();
        
        $.post('ajax',{input_function:'loadRKAPComponent'},function(result){
            $('div#my-loader').hide();
            var data = jQuery.parseJSON(result);
            var s= "";
            //create table header
            if ($('table#msg-dialog').length==0)
            {
                s = "<table id='msg-dialog' class='data-list'>";
                s+="<tr><th>No</th><th>Kelompok</th><th>Komponen</th><th>Tag</th><th>TagValue</th><th>Sort</th></tr>";
                s+= "</table>";
                my_dlg.appendDialogContent(s);
            }else{
                $('table#msg-dialog tr.msg-dlg-data').each(function(){
                    $(this).remove();
                })
            }
            
                
            if (data['found']>0){
                //start iterate
                for(var i in data['items']){
                    s= "<tr class='msg-dlg-data' id='"+data['items'][i]['id']+"' style='cursor:pointer;'>";
                    s+="<td align='center'>"+(parseInt(i)+1)+"</td>";
                    s+="<td lang='"+data['items'][i]['category']+"'>"+data['items'][i]['cat_caption']+"</td>";
                    s+="<td>"+data['items'][i]['caption']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['tag']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['tag_value']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['sort']+"</td>";
                    s+="</tr>";
                    $('table#msg-dialog').append(s);
                }
            }else{
                if (data['error']!='')
                    s="<tr class='msg-dlg-data'><td colspan='3'>"+data['error']+"</td></tr>";
                else
                    s="<tr class='msg-dlg-data'><td colspan='3'>Data tidak ditemukan</td></tr>";
                    
                $('table#msg-dialog').append(s);
            }
                
            $('tr.msg-dlg-data').click(function(){
                $('input#component_id').val($(this).attr('id'));
                $('input#component').val($(this).find('td').eq(2).text());
                $('select#category').val($(this).find('td').eq(1).attr('lang'));
                $('input#co_tag').val($(this).find('td').eq(3).text());
                $('input#co_tag_value').val($(this).find('td').eq(4).text());
                $('input#co_sort').val($(this).find('td').eq(5).text());
                
                //enabled del button
                $('input#btn_del_component').removeAttr('disabled');
            })
        })
    }
    function save_rkap_component(id,category,component,tag,tag_value,sort)
    {
        $('div#my-loader').show();
        $.post('ajax',{input_function:'saveRKAPComponent',param:id,category:category,caption:component,tag:tag,tag_value:tag_value,sort:sort},function(result){
            $('div#my-loader').hide();
            if (parseInt(result)==1)
                load_rkap_component();
            else
                alert(result.substr(1));
        })
    }
    function delete_rkap_component(id)
    {
        $('div#my-loader').show();
        $.post('ajax',{input_function:'deleteRKAPComponent',param:id},function(result){
            $('div#my-loader').hide();
            if (parseInt(result)==1)
                load_rkap_component();
            else
                alert(result.substr(1));
        })
    }
    function load_rkap(year)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadRKAP',param:year},function(result){
            $('div#my-loader').hide();
            //empty data view table
            $("table.data-list tr.row-msg").each(function(){
                $(this).remove();
            });
            //extract data as JSON
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                var category_check = 0;
                var category_num = 0;
                var item_num = 0;
                for (var i in data['items']){
                    var s = "";
                    if (category_check!=parseInt(data['items'][i]['category'])){
                        category_check = parseInt(data['items'][i]['category']);
                        category_num++;                        
                        item_num = 1;
                        
                        s+="<tr class='row-msg'>";
                        s+="<td>&nbsp;</td>";
                        s+="<td align='center'><strong>"+category_num+"</strong></td>";
                        s+="<td colspan='9'><strong>"+data['items'][i]['cat_caption']+"</strong></td>";
                        s+="</tr>";
                    }
                    s+="<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
                    s+="<td width='30' align='center'><input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td width='50' align='center'>"+category_num+"."+item_num+"</td>";
                    s+="<td width='150'><a href='rkap_update/"+data['items'][i]['id']+"' title='Edit komponen'>"+data['items'][i]['caption']+"</a></td>";
                    var triwulan_1 = data['items'][i]['triwulan_1']*1;
                    if (triwulan_1!=0)
                        s+="<td width='100' align='right'>"+triwulan_1.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    var triwulan_2 = data['items'][i]['triwulan_2']*1;
                    if(triwulan_2!=0)
                        s+="<td width='100' align='right'>"+triwulan_2.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right' width='100'>-</td>";
                    var triwulan_3 = data['items'][i]['triwulan_3']*1;
                    if(triwulan_3!=0)
                        s+="<td width='100' align='right'>"+triwulan_3.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td align='right' width='100'>-</td>";
                    
                    var triwulan_4 = data['items'][i]['triwulan_4']*1;
                    if(triwulan_4!=0)
                        s+="<td width='100' align='right'>"+triwulan_4.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    
                    var real_1 = data['items'][i]['real_1']*1;
                    if(real_1!=0)
                        s+="<td width='100' align='right'>"+real_1.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    
                    var real_2 = data['items'][i]['real_2']*1;
                    if(real_2!=0)
                        s+="<td width='100' align='right'>"+real_2.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    var real_3 = data['items'][i]['real_3']*1;
                    if(real_3!=0)
                        s+="<td width='100' align='right'>"+real_3.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    var real_4 = data['items'][i]['real_4']*1;
                    if(real_4!=0)
                        s+="<td width='100' align='right'>"+real_4.formatMoney(0,',','.')+"</td>";
                    else
                        s+="<td width='100' align='right'>-</td>";
                    
                    s+="</tr>";
                    
                    item_num++;
                    $('table.data-list').append(s);
                }
            }else{
                s = "<tr class='row-msg'><td colspan='11'>Data tidak ditemukan</td></tr>";
                $('table.data-list').append(s);
            }
        })
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteRKAP',param:id_array.join()},function(result){
            $('div#my-loader').hide();
            if(parseInt(result)==1)
                load_rkap($('select#tahun').val());
            else
                alert(result.substr(1));
        })
    }
</script>
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
                    <li class="execute" id="btn_add">Tambah</li>
                    <li class="execute" id="btn_edit">Edit</li>
                    <li class="execute" id="btn_delete">Hapus</li>
                    <?php if (userHasAccess($access, 'RKAP_SETUP')){?>
                    <li class="dropdown">Pengaturan
                        <ul>
                            <li id="drp-category">Kelompok Komponen</li>
                            <li id="drp-component">Daftar Komponen</li>
                        </ul>
                    </li>
                    <?php }?>
                    <li class="execute" id="btn_report">Report</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Uraian</th>
                    <th colspan="4">RKAP</th>
                    <th colspan="4">Realisasi</th>
                </tr>
                <tr>
                    <th>Triwulan I</th>
                    <th>Triwulan II</th>
                    <th>Triwulan III</th>
                    <th>Triwulan IV</th>
                    <th>Triwulan I</th>
                    <th>Triwulan II</th>
                    <th>Triwulan III</th>
                    <th>Triwulan IV</th>
                </tr>
            </table>
        </div>
        <div class="clr"></div>
        <div class="content">
            <ul class="navigation"></ul>
        </div>
    </div>
    <?php echo document_footer();?>
</body>
</html>