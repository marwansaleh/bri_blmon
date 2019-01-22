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
$max_parameter_alllowed = 3;//1;
security_uri_check($max_parameter_alllowed, $qs);

$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script language="javascript" type="text/javascript" src="customs/js/jquery.form.js"></script>
<script type="text/javascript">    
    $(document).ready(function(){  
        loadAllBackups();
        $('li#btn_close').click (function(){
            window.location = "<?php echo ABSTRACT_BASE;?>index";
        });
        $('li#btn_create').click ( function ()
	{
            createBackup();
	});
        $('li#btn_restore').click ( function ()
	{
            //alert('Under construction');			
            var row_id = []
            $("tr.row-msg :checked").each ( function ()
            {
                row_id.push($(this).val());
            });
            if(row_id.length<1||row_id.length>1)
                alert("Pilih / checked hanya 1 file backup yang akan di-restore");
            else if (confirm('Anda yakin me-restore database dengan file terpilih ?')==true)			
            {
                restoreDatabase(row_id[0]+'.sql');
            }
	});
        
        $('li#btn_delete').click ( function ()
	{
            //check if any record in the table, if not, send alert and do not proceed
            if($('tr.row-msg').length==0)
            {
                alert('Tidak ada record data pada tabel');
                return;
            }
            var data_id = [];
            $("tr.row-msg :checked").each ( function ()
            {
                data_id.push($(this).val());
            });
            if(data_id.length<1)
                alert("Pilih / checked file backup yang akan dihapus");
            else if (confirm('Hapus file backup terpilih ?')==true)			
            {
                deleteRecords(data_id);
            }
	});
        
        $('li#btn_download').click ( function ()
	{
            //check if any record in the table, if not, send alert and do not proceed
            if($('tr.row-msg').length==0)
            {
                alert('Tidak ada record data pada tabel');
                return;
            }
            var data_id = [];
            $("tr.row-msg :checked").each ( function ()
            {
                data_id.push($(this).val());
            });
            if(data_id.length<1)
                alert("Pilih / check file backup yang akan di-download");
            else			
            {
                $('div#my-loader').show();
                $.post("ajax",{input_function:'download_backup',ids:data_id.toString()},function(result){
                    $('div#my-loader').hide();
                    var data = jQuery.parseJSON(result);
                    if (data.filename) {
                        var wnd = window.open("get_dbbackup_file?filename="+data.filename);
                        wnd.focus();
                    } else {
                        alert(data.message);
                    }
                })
            }
	});
		
    });    
    function loadAllBackups()
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadAllBackups'},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr.row-msg").each(function(){
                $(this).remove();
            })
            if (data['found']>0){
                for(var i in data['items']){
                    var s = "<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
                    s+="<td> <input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td align='center'>"+(parseInt(i)+1)+"</td>";
                    s+="<td>"+data['items'][i]['name']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['creation_date']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['size_kb']+" kilobytes</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
            }else{
                s="<tr class='row-msg'><td colspan='5'>File backup tidak ada</td></tr>";
                $("table.data-list").append(s);
                //clear old navigation
                $('ul.navigation').empty();
            }            
        })
    }
    function createBackup()
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'createBackup'},function(result){
            $('div#my-loader').hide();
            loadAllBackups();
        })
    }
    function restoreDatabase(filename)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'restoreFromBackup',param:filename},function(result){
            $('div#my-loader').hide();
            if (result!='')
                alert(result);
            //loadAllBackups();
        })
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteBackups',param:id_array.join()},function(result){
            $('div#my-loader').hide();
            var result = jQuery.parseJSON(result);
            for(var i in result['success_id'])
            {				
                //remove message rows in the table
		$('tr.row-msg').each ( function ()
                {
                    if ($(this).attr('id')==result['success_id'][i])
                        $(this).remove();
                });
                //renumbering
		$('tr.row-msg').each ( function (index)
		{
                    $('td', this).eq(1).text(index+1);
		});
            }
            
            var error = result['error'].join('\n');
            if (error!='') alert(error);
        })
    }
 </script>
        
 </head>
 <body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Database Backups</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_close">Home</li>
                    <li class="execute" id="btn_create">Buat Backup</li>
                    <li class="execute" id="btn_delete">Hapus Backup</li>
                    <li class="execute" id="btn_restore">Restore Database</li>
                    <li class="execute" id="btn_download">Download Backup</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                <tr>
                    <th>#</th>
                    <th>No</th>
                    <th>Nama File</th>
                    <th>Tanggal Backup</th>
                    <th>Ukuran File</th>
                </tr>
                <tr class="row-msg"><td colspan="5"></td></tr>
            </table>
        </div>
        <div class="clr"></div>
    </div>
    <?php echo document_footer();?>
</body>
</html>