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

if (isset($qs[1])){
    $program = $qs[1];
}else{
    $program = 1;
}
$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">    
    var access_manipulate_other = "<?php echo (userHasAccess($access, "REALBUDGET_MANIPULATE_OTHER")?1:0);?>";
    $(document).ready(function(){
        loadRealBudget(<?php echo $program;?>,0,'');
        
        $('li#btn_home').click(function(){
            window.location = "programs";
        })
        <?php if (userHasAccess($access, "REALBUDGET_CREATE")){?>
        $('li#btn_create').click(function(){
            window.location = "real_update/<?php echo ACT_CREATE;?>/<?php echo $program;?>";
        })
        <?php } if (userHasAccess($access, "REALBUDGET_EDIT")){?>
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
                window.location = "real_update/<?php echo ACT_EDIT;?>/"+id[0];
        })
        <?php } if (userHasAccess($access, "REALBUDGET_DELETE")){?>
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
            else if (confirm("Hapus data record terpilih ?")){
                deleteRecords(id);
            }
        })
        <?php }?>
        $('select#program').change(function(){
            var program_id = $(this).val();
            window.location ="<?php echo cur_page_name(false);?>/"+program_id;
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            var program = $('select#program').val();
            loadRealBudget(program,0,keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});
    })
    function loadRealBudget(program_id,page,keyword)
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadRealBudget',param:program_id,page:page,search_str:keyword},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr.row-msg").each(function(){
                $(this).remove();
            })
            if (data['found']>0){
                for(var i in data['items']){
                    var s = "<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
                    if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                        s+="<td> <input disabled type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    else
                        s+="<td> <input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td align='center'>"+(parseInt(i)+1)+"</td>";
                    s+="<td>";
                        if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                            s+=data['items'][i]['caption'];
                        else
                            s+="<a href=\"real_update/<?php echo ACT_EDIT;?>/"+data['items'][i]['id']+"\">"+data['items'][i]['caption']+"</a>";
                    s+="</td>";
                    s+="<td align='right'>"+data['items'][i]['nominal']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['creation_date']+"</td>";
                    s+="<td>"+data['items'][i]['creation_by']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['last_update']+"</td>";
                    //s+="<td>"+data['items'][i]['last_update_by']+"</td>";
                    s+="<td align='center'>";
                        s+="<div class='dropdown-menu'>More action";
                            s+="<ul lang='"+data['items'][i]['id']+"'>";
                                <?php if (userHasAccess($access, "REALBUDGET_EDIT")){?>
                                if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                                    s+="<li>Not editable</li>";
                                else
                                    s+="<li id='drp_edit'>Edit</li>";
                                <?php } if (userHasAccess($access, "REALBUDGET_DELETE")){?>
                                if (data['items'][i]['view_by']!=data['items'][i]['creation_by_id']&&access_manipulate_other!='1')
                                    s+="<li>Not deletable</li>";
                                else
                                    s+="<li id='drp_delete'>Delete</li>";
                                <?php }?>
                            s+="</ul>";
                        s+="</div>";
                    s+="</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(page, program_id, data['pages'],keyword);
            }else{
                s="<tr class='row-msg'><td colspan='9'>Data tidak ditemukan</td></tr>";
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
            <?php if (userHasAccess($access, "TASK_EDIT")){?>
            $('li#drp_edit').click (function(){
                var id = $(this).parent().attr('lang');
                window.location = "real_update/<?php echo ACT_EDIT;?>/"+id;
            })
            <?php } if (userHasAccess($access, "TASK_DELETE")){?> 
            $('li#drp_delete').click (function(){
                var id = [];
                id.push($(this).parent().attr('lang'));
                if (confirm("Hapus record terpilih ?")){
                    deleteRecords(id);
                }
            })
            <?php }?>
        })
    }
    function createNavigator(page_active, program_id, num_of_pages, keyword)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i;i<num_of_pages;i++){
                var s="<li onclick='loadRealBudget("+program_id+","+i+",\""+keyword+"\");'>"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteRealisation',param:id_array.join()},function(result){
            $('div#my-loader').hide();
			if (parseInt(result)==1)
			{
				for(var i=0; i<id_array.length; i++)
				{
					$('tr#'+id_array[i]).remove();
				}
				$('tr.row-msg').each ( function (index)
				{
					$('td', this).eq(1).text(index+1);
				});
				
				//alert("Record deleted successfully!");
			}else alert(result.substr(1));
        })
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Program: <?php echo get_program_name_by_id($program, $db_obj);?></h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Program</li>
                    <li class="dropdown">
                        <select id="program" name="program">
                            <?php
                            $programs = load_programs($db_obj);
                            if($programs)foreach($programs as $item){
                                echo "<option value='".$item['id']."'";
                                if ($program==$item['id'])echo ' selected';
                                echo ">".$item['name']."</option>";
                            }
                            ?>
                        </select>
                    </li>
                    <?php if (userHasAccess($access, "TASK_CREATE")){?>
                    <li class="execute" id="btn_create">Tambah Realisasi</li>
                    <?php } if (userHasAccess($access, "TASK_EDIT")){?>
                    <li class="execute" id="btn_edit">Edit Realisasi</li>
                    <?php } if (userHasAccess($access, "TASK_DELETE")){?>
                    <li class="execute" id="btn_delete">Hapus Realisasi</li>
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
                    <th>#</th>
                    <th>No</th>
                    <th>Nama Realisasi</th>
                    <th>Jumlah Nominal (Rp)</th>
                    <th>Dibuat Tgl</th>
                    <th>Oleh</th>
                    <th>Last Update</th>
					<!--
                    <th>Update oleh</th>
					-->
                    <th>Action</th>
                </tr>
                <tr class="row-msg"><td colspan="9"></td></tr>
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