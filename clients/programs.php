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

$db_obj = new DatabaseConnection();

//load user access
$access = loadUserAccess($db_obj);

if (isset($qs[1]))
    $bidang = $qs[1];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript"> 
    var access_manipulate_other = "<?php echo (userHasAccess($access, "PROGRAM_MANIPULATE_OTHER")?1:0);?>";
    var curPage = 0;
    $(document).ready(function(){
        // Initialize datepicker
        $( ".datepicker" ).datepicker({
            dateFormat: "yy-mm-dd"
        });
        
        // Initialize dialog
        $( "#dlg-approval" ).dialog({
            autoOpen: false,
            modal: true,
            minWidth: 350,
            buttons: [
                {
                    text: "Approve",
                    click: function() {
                        executeApproval();
                    }
                },
                {
                    text: "Cancel",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ]
          });

        loadPrograms(curPage,$('select#type').val(),'');
        
        $('li#btn_home').click(function(){
            window.location = "./";
        })
        <?php if (userHasAccess($access, "PROGRAM_CREATE")){?>
        $('li#btn_create').click(function(){
            window.location = "programs_update/<?php echo ACT_CREATE;?>";
        })
        <?php }if (userHasAccess($access, "PROGRAM_EDIT")){?>
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
                window.location = "programs_update/<?php echo ACT_EDIT;?>/"+id[0];
        });
        <?php }if (userHasAccess($access, "PROGRAM_DELETE")){?>
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
            else if (confirm("Hapus program terpilih ? \nSemua kegiatan, dokumen dan informasi lain akan ikut dihapus")){
                deleteRecords(id);
            }
        });
        <?php }?>
        $('li#btn_export').click(function(){
            $('div#my-loader').show();
            var p_type = $('select#type').val();
            var p_search = $('input#keyword').val();
            var p_state = $('select#state').val();
            var p_year = $('#creation_year').val();
			var kanwil = $('select#wilayah').val();
            
            $.post("ajax",{input_function:'export_filtered_programs',type:p_state,search_str:p_search,state:p_state,creation_year:p_year,wilayah:kanwil}, function(result){
                $('div#my-loader').hide();
                var data = jQuery.parseJSON(result);
                
                if (data.status) {
                    var exp_wdw = window.open("get_excel_alt?filename="+data.filename,"ExportedWindow");
                    exp_wdw.focus();
                }else {
                    alert(data.message);
                }
            });
        });
        $('select#type').change(function(){
            var program_type = $(this).val();
            var keyword = $('input#keyword').val();
            loadPrograms(0, program_type, keyword);
        });
		$('select#wilayah').change(function(){
            var program_type = $('select#type').val();
            var keyword = $('input#keyword').val();
            loadPrograms(0, program_type, keyword);
        });
        $('select#state').change(function(){
            loadPrograms(0, $('select#type').val(), $('input#keyword').val());
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            var program_type = $('select#type').val();
            loadPrograms(0, program_type, keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});        
    });
    function loadPrograms(page,program_type,keyword)
    {        
        curPage = page;
        //empty table
        $("table.data-list tr.row-msg").each(function(){
            $(this).remove();
        });
        
        var kanwil = $('select#wilayah').val();
        var state = $('select#state').val();
        var creation_year = $('#creation_year').val();
        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadPrograms',param:page,type:program_type,search_str:keyword,state:state, creation_year:creation_year, wilayah:kanwil},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            
            if (data['found']>0){
                var start = parseInt(data['start']);
                for(var i in data['items']){
                    var s = "";
                    s+="<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
                    if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                        s+="<td> <input disabled type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    else
                        s+="<td> <input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td align='center'>"+(start+parseInt(i)+1)+"</td>";
                    s+="<td title='"+data['items'][i]['description']+"'>";
                    <?php if (userHasAccess($access, "PROGRAM_EDIT")){?>
                        if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                            s+=data['items'][i]['name'];
                        else
                            s+="<a href=\"programs_update/<?php echo ACT_EDIT;?>/"+data['items'][i]['id']+"\">"+data['items'][i]['name']+"</a>";
                    <?php }else{?>
                        s+=data['items'][i]['id']+"\">"+data['items'][i]['name'];
                    <?php }?>
                    s+="</td>";
                    s+="<td><a href='areas/"+data['items'][i]['uker_wilayah']+"'>"+data['items'][i]['uker']+"</a></td>";
                    //s+="<td>"+data['items'][i]['kabupaten']+"</td>";
                    s+="<td><a href='propinsi/"+data['items'][i]['propinsi_id']+"'>"+data['items'][i]['propinsi']+"</a></td>";
                    var budget = data['items'][i]['budget']*1;
                    //s+="<td align='right'>"+budget.formatMoney(2,',','.')+"</td>";
					s+="<td align='center' width='70'>"+data['items'][i]['nodin_putusan']+"</td>";
					s+="<td align='center' width='70'>"+data['items'][i]['nomor_persetujuan']+"</td>";
                    var operational = data['items'][i]['operational']*1;
                    //s+="<td align='right'>"+operational.formatMoney(2,',','.')+"</td>";
                    s+="<td align='right'>"+data['items'][i]['real_used']+"</td>";
                    s+="<td align='center' width='70'>"+data['items'][i]['creation_date']+"</td>";
                    s+="<td><a href='personals/"+data['items'][i]['creation_by_id']+"'>"+data['items'][i]['creation_by']+"</a></td>";
                    if (data['items'][i]['state']==0)
                        s+="<td align='center'><div class='icon-oknot'></div></td>";
                    else
                        s+="<td align='center'><div class='icon-ok'></div></td>";
                    s+="<td onclick='viewBeneficiary("+data['items'][i]['id']+");' title='Klik untuk melihat detail penerima'><u>"+data['items'][i]['benef_name']+"</u></td>";
                    var benef_orang = data['items'][i]['benef_orang']*1;
                    s+="<td align='right'>"+benef_orang.formatMoney(0,',','.')+"</td>";
                    var benef_unit = data['items'][i]['benef_unit']*1;
                    s+="<td align='right'>"+benef_unit.formatMoney(0,',','.')+"</td>";
                    s+="<td align='center'>";
                        s+="<div class='dropdown-menu'>More action";
                            s+="<ul lang='"+data['items'][i]['id']+"'>";
                                <?php if (userHasAccess($access, "PROGRAM_EDIT")){?>
                                if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                                    s+="<li>Not editable</li>";
                                else
                                    s+="<li id='drp_edit'>Edit</li>";
                                <?php } if (userHasAccess($access, "PROGRAM_DELETE")){?>
                                if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                                    s+="<li>Not deletable</li>";
                                else
                                    s+="<li id='drp_delete'>Delete</li>";
                                <?php } if (userHasAccess($access, "PROGRAM_APPROVE")){?>
                                if (data['items'][i]['state']==0){
                                    s+="<li id='drp_approve'>Approve</li>";
                                }
                                else{                                    
                                    s+="<li id='drp_approve'>Not-Approve</li>";
                                }
                                <?php }?>
                                if (data['items'][i]['state']==1){
                                    s+="<li id='drp_real' onclick='programReal("+data['items'][i]['id']+");'>Realisasi</li>";
                                    s+="<li id='drp_task' onclick='programTask("+data['items'][i]['id']+");'>Task list</li>";
                                }
                                s+="<li id='drp_view' onclick='programView("+data['items'][i]['id']+");'>Detail Program</li>";
                            s+="</ul>";
                        s+="</div>";
                    s+="</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(page, program_type, keyword, data['pages']);
            }else{
                s="<tr class='row-msg'><td colspan='15'>Data tidak ditemukan</td></tr>";
                $("table.data-list").append(s);
                //clear old navigation
                $('ul.navigation').empty();
            }
            
            //create event handler for dropdown menu click
            $('div.dropdown-menu').click(function(){
                if ($('ul',this).css('display')!='none')
                {
                    $('ul',this).hide();
                }else{
                    $('div.dropdown-menu ul').each(function(){
                        $(this).hide();
                    });
                    $('ul',this).show();
                }
            });
            <?php if (userHasAccess($access, "PROGRAM_EDIT")){?>
            $('li#drp_edit').click (function(){
                var id = $(this).parent().attr('lang');
                window.location = "programs_update/<?php echo ACT_EDIT;?>/"+id;
            })
            <?php }if (userHasAccess($access, "PROGRAM_APPROVE")){?>
            $('li#drp_approve').click (function(){
                var caption = $(this).text();
                var program_id = $(this).parent().attr('lang');
                var btn_ref = $(this);
                var td_ref = $('tr#'+program_id).find('td').eq(10);
                
                if (caption=='Approve'){
                    //Update dialog
                    $('#dlg-approval').find('#dlg-program-id').val(program_id);
                    $('#dlg-approval').find('#dlg-tgl-persetujuan').val($('#dlg-today').val());
                    $( "#dlg-approval" ).dialog("open");
                    
                }else{
                    cancelApprovalStatus(program_id);
                }
                
            });
            <?php }?>
            $('li#drp_delete').click (function(){
                var id = [];
                id.push($(this).parent().attr('lang'));
                if (confirm("Hapus program terpilih ? \nSemua kegiatan, dokumen dan informasi lain akan ikut dihapus")){
                    deleteRecords(id);
                }
            })
        })
    };
    function cancelApprovalStatus(program_id){
        //Update server
        $('div#my-loader').show();
        $.post("ajax",{input_function:'cancelProgramStatus',param:program_id},function(result){
            $('div#my-loader').hide();
            var result = jQuery.parseJSON(result);
            
            if (result.status){
                loadPrograms(curPage, $('select#type').val(), $('input#keyword').val());
            }else{
                alert(result.message);
            }   

        });
    };
    function executeApproval() {
        var program_id = $('#dlg-program-id').val();
        var nama_realisasi = $('#dlg-nama-realisasi').val();
        var nominal_realisasi = $('#dlg-nominal-realisasi').val();
        var tgl_persetujuan = $('#dlg-tgl-persetujuan').val();
        
        //Close dialog
        $( "#dlg-approval" ).dialog("close");
        
        //Update server
        $('div#my-loader').show();
        $.post("ajax",{
            input_function:'approveProgramStatus',
            param:program_id,
            approval_date:tgl_persetujuan,
            nama_realisasi: nama_realisasi,
            nominal_realisasi: nominal_realisasi
        },function(result){
            $('div#my-loader').hide();
            var result = jQuery.parseJSON(result);
            
            if (result.status){
                loadPrograms(curPage, $('select#type').val(), $('input#keyword').val());
            }else{
                alert(result.message);
            }     

        });
    };
    function programTask(program_id)
    {
        window.location="tasks/"+program_id;
    };
    function programReal(program_id)
    {
        window.location="realisation/"+program_id;
    }
    function programView(program_id)
    {
        var wnd = window.open("program_view/"+program_id,"ProgramDetail","width=700,scrollbars=1");
        wnd.focus();
    }
    function createNavigator(page_active, type, keyword, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0;i<num_of_pages;i++){
                var s="<li onclick='loadPrograms("+i+",\""+type+"\",\""+keyword+"\");'";
                if (i==page_active) s+=" class='active'";
                s+=">"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deletePrograms',param:id_array.join()},function(result){
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
            var error = result['error_message'];
            if (error.length > 0)
                alert(error.join("\n"));
        })
    }
    function viewBeneficiary(programId){
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadBeneficiary',param:programId},function(result){
            $('div#my-loader').hide();
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                alert(
                    'Nama Penerima:\t'+data['items']['benef_name']+'\n'+
                    'Alamat Penerima:\t'+data['items']['benef_address']+'\n'+
                    'Telpon / HP:\t'+data['items']['benef_phone']+'\n'+
                    'Email Penerima:\t'+data['items']['benef_email']
                );
            }
        })
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Program CSR BRI - Berdasarkan Kategori</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="dropdown">
                        <select id="type" name="type">                            
                            <?php
                            $types = get_program_types($db_obj);
                            if($types)foreach($types as $item){
                                echo "<option value='".$item['id']."'";
                                if (isset($bidang)&&$bidang==$item['id']) echo " selected";
                                echo ">".$item['type']."</option>";
                            }
                            ?>
                            <option value="-1">SEMUA BIDANG</option>
                        </select>
                    </li>
					<li class="dropdown">
                        <select id="wilayah" name="wilayah">
                            <?php
                            $wilayahs = load_kanwil($db_obj);
                            if($wilayahs)foreach($wilayahs as $item){
                                echo "<option value='".$item['id']."'";
                                if (isset($wilayah)&&$wilayah==$item['id']) echo " selected";
                                echo ">".$item['uker']."</option>";
                            }
                            ?>
                            <option value="-1">SEMUA WILAYAH</option>
                        </select>
                    </li>
                    <li class="dropdown">
                        <select id="state" name="state">  
                            <option value="-1">Status</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </li>
                    
                    <?php if (userHasAccess($access, "PROGRAM_CREATE")){?>
                    <li class="execute" id="btn_create">Tambah</li>
                    <?php }if (userHasAccess($access, "PROGRAM_EDIT")){?>
                    <li class="execute" id="btn_edit">Edit</li>
                    <?php }if (userHasAccess($access, "PROGRAM_DELETE")){?>
                    <li class="execute" id="btn_delete">Hapus</li>
                    <?php }?>
                    <li class="search">&laquo;</li>
                    <li class="execute">
                        <input type="text" id="creation_year" name="creation_year" placeholder="Tahun" style="width:40px;" />
                    </li>
                    <li class="search">
                        <input type="text" id="keyword" name="keyword" 
                            	value="<?php echo (isset($keyword)?$keyword:'');?>" />
                            <div id="btn_search_content" class="buttons" 
                                 lang="<?php echo cur_page_name(false);?>">Search</div>
                    </li>  
                    <li class="execute" id="btn_export">Export</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Program</th>
                    <th rowspan="2">Unitkerja</th>                    
                    <th rowspan="2">Propinsi</th>
                    <th rowspan="2">Putusan</th>
					<th rowspan="2">Persetujuan</th>
                    <th rowspan="2">Realisasi</th>
                    <th rowspan="2">Dibuat</th>
                    <th rowspan="2">Oleh</th>
                    <th rowspan="2">Status</th>   
                    <th colspan="3">Penerima</th>
                    <th rowspan="2">Action</th>
                </tr>
                <tr>
                    <th>Nama</th>
                    <th>Orang</th>
                    <th>Unit</th>                    
                </tr>
                <tr class="row-msg"><td colspan="15"></td></tr>
            </table>
        </div>
        <div class="clr"></div>
        <div class="content">
            <ul class="navigation"></ul>
        </div>
    </div>
    
    <!-- Modal dialog -->
    <div id="dlg-approval" title="Program Approval">
        <input type="hidden" id="dlg-program-id" value="0" />
        <input type="hidden" id="dlg-today" value="<?php echo date('Y-m-d'); ?>" />
        <table border="0">
            <tbody>
                <tr>
                    <td>Nama realisasi</td>
                    <td align="right"><input type="text" id="dlg-nama-realisasi" style="width: 200px;" /></td>
                </tr>
                <tr>
                    <td>Nominal realisasi</td>
                    <td align="right"><input type="text" id="dlg-nominal-realisasi" style="width: 200px;" /></td>
                </tr>
                <tr>
                    <td>Tgl. Persetujuan</td>
                    <td align="right"><input type="text" class="datepicker" id="dlg-tgl-persetujuan" style="width: 200px;" /></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php echo document_footer();?>
</body>
</html>