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
    $wilayah_active = $qs[1];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        loadUker($('select#kanwil').val(),0,'');
        
        $('li#btn_home').click(function(){
            window.location = "programs";
        })
        <?php if (userHasAccess($access, "UKER_CREATE")){?>
        $('li#btn_create').click(function(){
            window.location = "uker_update/<?php echo ACT_CREATE;?>";
        })
        <?php } if (userHasAccess($access, "UKER_EDIT")){?>
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
                window.location = "uker_update/<?php echo ACT_EDIT;?>/"+id[0];
        })
        <?php } if (userHasAccess($access, "UKER_DELETE")){?>
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
        $('div#btn_search_content').click ( function ()
	{
            var wilayah = $('select#kanwil').val();    
            var page= 0;
            var keyword = $(this).parent().find('input').val();
            loadUker(wilayah,page,keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});
        $('select#kanwil').change(function(){
            var wilayah = $(this).val();          
            var page= 0;
            var keyword = $('input#keyword').val();
            
            loadUker(wilayah, page, keyword);
        })
    })
    function loadUker(wilayah,page_num,keyword_str)
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadUker',param:wilayah,page:page_num,keyword:keyword_str},function(result){
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
                    s+="<td> <input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td align='center'>"+(start+parseInt(i)+1)+"</td>";
                    s+="<td><a href=\"uker_update/<?php echo ACT_EDIT;?>/"+data['items'][i]['id']+"\">"+data['items'][i]['uker']+"</a></td>";
                    s+="<td align='center'>"+data['items'][i]['kode']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['tipe']+"</td>";
                    s+="<td>"+data['items'][i]['alamat']+"</td>";
                    s+="<td>"+data['items'][i]['kabupaten']+"</td>";
                    s+="<td>"+data['items'][i]['propinsi']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['telepon']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['fax']+"</td>";
                    s+="<td align='center'>";
                        s+="<div class='dropdown-menu'>More action";
                            s+="<ul lang='"+data['items'][i]['id']+"'>";
                                <?php if (userHasAccess($access, "UKER_EDIT")){?>
                                s+="<li id='drp_edit'>Edit</li>";
                                <?php } if (userHasAccess($access, "UKER_DELETE")){?>
                                s+="<li id='drp_delete'>Delete</li>";
                                <?php }?>
                            s+="</ul>";
                        s+="</div>";
                    s+="</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(wilayah,page_num,keyword_str,data['pages']);
            }else{
                s="<tr class='row-msg'><td colspan='10'>Data tidak ditemukan</td></tr>";
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
            <?php if (userHasAccess($access, "UKER_EDIT")){?>
            $('li#drp_edit').click (function(){
                var id = $(this).parent().attr('lang');
                window.location = "uker_update/<?php echo ACT_EDIT;?>/"+id;
            })
            <?php } if (userHasAccess($access, "UKER_DELETE")){ ?>
            $('li#drp_delete').click (function(){
                var id = [];
                id.push($(this).parent().attr('lang'));
                if (confirm("Hapus uker terpilih ?")){
                    deleteRecords(id);
                }
            })
            <?php }?>
        })
    }
    function createNavigator(wilayah,page_active, keyword, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0; i<num_of_pages;i++){
                var s="<li onclick='loadUker("+wilayah+","+i+",\""+keyword+"\");'";
                if (i==page_active) 
                    s+= " class='active'";
                s+=">"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteUker',param:id_array.join()},function(result){
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
            if (result['error_message']!='')
                alert(result['error_message']);
        })
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Unit Kerja BRI Selindo</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li>Kanwil
                        <select id="kanwil" name="kanwil">                            
                        <?php 
                        $kanwil_options = load_kanwil($db_obj);
                        foreach($kanwil_options as $item){
                            echo "<option value='".$item['wilayah']."'";
                            if (isset($wilayah_active)&&$wilayah_active==$item['wilayah']) echo " selected";
                            echo ">".$item['uker']."</option>";
                        }
                        ?>
                            <option value="0">Semua Unitkerja</option>
                        </select>                        
                    </li>
                    <li class="execute" id="btn_create">Tambah</li>
                    <li class="execute" id="btn_edit">Edit</li>
                    <li class="execute" id="btn_delete">Hapus</li>
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
                    <th colspan="2">#</th>
                    <th>Unit Kerja</th>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Alamat</th>
                    <th>Kota</th>
                    <th>Propinsi</th>
                    <th>Telpon</th>
                    <th>Fax</th>
                    <th>Action</th>
                </tr>
                <tr class="row-msg"><td colspan="11"></td></tr>
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