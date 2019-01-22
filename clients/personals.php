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
    $person = $qs[1];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">    
    var access_manipulate_other = "<?php echo (userHasAccess($access, "PROGRAM_MANIPULATE_OTHER")?1:0);?>";
    $(document).ready(function(){
        loadPrograms(0,$('select#person').val(),'');
        
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
        })
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
        })
        <?php }?>
        $('select#person').change(function(){
            var person = $(this).val();
            var keyword = $('input#keyword').val();
            loadPrograms(0, person, keyword);
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            var person = $('select#person').val();
            loadPrograms(0, person, keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});
    })
    function loadPrograms(page,person,keyword)
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadProgramsByCreator',param:page,person:person,search_str:keyword},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr.row-msg").each(function(){
                $(this).remove();
            })
            if (data['found']>0){
                var start = parseInt(data['start']);
                for(var i in data['items']){
                    var s = "<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
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
                    s+="<td align='right'>"+budget.formatMoney(2,',','.')+"</td>";
                    var operational = data['items'][i]['operational']*1;
                    s+="<td align='right'>"+operational.formatMoney(2,',','.')+"</td>";
                    s+="<td align='right'>"+data['items'][i]['real_used']+"</td>";
                    s+="<td><a href='personals/"+data['items'][i]['creation_by_id']+"'>"+data['items'][i]['creation_by']+"</a></td>";
                    s+="<td align='center' width='70'>"+data['items'][i]['approval_date']+"</td>";
                    if (data['items'][i]['state']==0)
                        s+="<td align='center'><div class='icon-oknot'></div></td>";
                    else
                        s+="<td align='center'><div class='icon-ok'></div></td>";
                    s+="<td align='center'>"+data['items'][i]['progress']+"</td>";
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
                createNavigator(page, person, keyword, data['pages']);
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
                    new_caption = 'Not-Approve';
                    new_status = 1;
                }else{
                    new_caption = "Approve";
                    new_status = 0;
                }
                var approval_date = (new_status==1?prompt("Masukkan tanggal approval dengan format YYYY-mm-dd. Kosongkan untuk tanggal hari ini",""):'');
                $('div#my-loader').show();
                $.post("ajax",{input_function:'updateProgramStatus',param:program_id,status:new_status,approval_date:approval_date},function(result){
                    $('div#my-loader').hide();
                    if (parseInt(result)==1){
                        btn_ref.text(new_caption);
                        if(new_status==0){                            
                            td_ref.html("<div class='icon-oknot'></div>");
                            btn_ref.parent().find('#drp_real').remove();
                            btn_ref.parent().find('#drp_task').remove();
                        }else{
                            td_ref.html("<div class='icon-ok'></div>");
                            btn_ref.parent().append("<li id='drp_real' onclick='programReal("+program_id+");'>Realisasi</li>");
                            btn_ref.parent().append("<li id='drp_task' onclick='programTask("+program_id+");'>Task list</li>");
                        }
                    }else{
                        alert(result.substr(1));
                    }     
                    
                })
                
            })
            <?php }?>
            $('li#drp_delete').click (function(){
                var id = [];
                id.push($(this).parent().attr('lang'));
                if (confirm("Hapus data record terpilih ?")){
                    deleteRecords(id);
                }
            })
        })
    }
    function programTask(program_id)
    {
        window.location="tasks/"+program_id;
    }
    function programReal(program_id)
    {
        window.location="realisation/"+program_id;
    }
    function programView(program_id)
    {
        var wnd = window.open("program_view/"+program_id,"ProgramDetail","width=700,scrollbars=1");
        wnd.focus();
    }
    function createNavigator(page_active, person, keyword, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0;i<num_of_pages;i++){
                var s="<li onclick='loadPrograms("+i+",\""+person+"\",\""+keyword+"\");'";
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
            <h1>Program CSR BRI - Berdasarkan Creator</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li>Kreator
                        <select id="person" name="person">
                            <?php
                            $sql = "SELECT id, full_name FROM users ORDER BY full_name";
                            $persons = $db_obj->execSQL($sql);
                            if($persons)foreach($persons as $item){
                                echo "<option value='".$item['id']."'";
                                if (isset($person)&&$person==$item['id']) echo " selected";
                                echo ">".$item['full_name']."</option>";
                            }
                            ?>
                            <option value="-1">SEMUA USER</option>
                        </select>
                    </li>
                    <?php if (userHasAccess($access, "PROGRAM_CREATE")){?>
                    <li class="execute" id="btn_create">Tambah Program</li>
                    <?php }if (userHasAccess($access, "PROGRAM_EDIT")){?>
                    <li class="execute" id="btn_edit">Edit Program</li>
                    <?php }if (userHasAccess($access, "PROGRAM_DELETE")){?>
                    <li class="execute" id="btn_delete">Hapus Program</li>
                    <?php }?>
                    <li class="search">&laquo;</li>
                    <li class="search">
                        <input type="text" id="keyword" name="keyword" 
                            	value="<?php echo (isset($keyword)?$keyword:'');?>" />
                            <div id="btn_search_content" class="buttons" 
                                 lang="<?php echo cur_page_name(false);?>">Search</div>
                    </li>  
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
                    <th rowspan="2">Anggaran</th>
                    <th rowspan="2">Operasional</th>
                    <th rowspan="2">Realisasi</th>
                    <th rowspan="2">Oleh</th>
                    <th rowspan="2">Approved</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">(%)</th>
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
    <?php echo document_footer();?>
</body>
</html>