<?php
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 

$qs = str_ireplace(FOLDER_PREFIX, "", $_SERVER["REQUEST_URI"]);
$qs = explode("/", $qs);
array_shift($qs);

//check security uri, must do in every page
//to avoid http injection
$max_parameter_alllowed = 1;
security_uri_check($max_parameter_alllowed, $qs);

$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);
if (!userHasAccess($access, "ACCOUNT_MANAGE"))
{
    header("location: index");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        loadUsers(0,'');
        $('li#btn_home').click(function(){
            window.location = "index";
        })
        $('li#btn_create').click(function(){
            window.location = "profile/0"+"/accounts";
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
                alert("Pilih / checked satu user yang akan diedit");
            else
                window.location = "profile/"+id[0]+"/accounts";
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            loadUsers(0,keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});      
    })
    function loadUsers(page, keyword)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadUsers',param:page,search_str:keyword},function(result){
            $('div#my-loader').hide();
            var data = jQuery.parseJSON(result);
            //empty table
            $("table.data-list tr.row-msg").each(function(){
                $(this).remove();
            })
            if (data['found']>0){
                for(var i in data['items']){
                    var s = "";
                    s+="<tr class='row-msg' id='"+data['items'][i]['id']+"'>";
                    s+="<td>";
                        s+="<input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'>";
                        s+="<img src='customs/profile/photo/"+data['items'][i]['avatar']+"' width='30' height='34' />";
                    s+="</td>";
                    s+="<td width='120'>"+data['items'][i]['user_name']+"</td>";
                    s+="<td>"+data['items'][i]['full_name']+"</td>";
                    s+="<td>"+data['items'][i]['position']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['access']+"</td>";
                    if (data['items'][i]['login_status']==0)
                        s+="<td align='center'><div class='icon-oknot'></div></td>";
                    else
                        s+="<td align='center'><div class='icon-ok'></div></td>";
                    s+="<td width='120' align='center'>"+data['items'][i]['last_login']+"</td>";
                    s+="<td width='120' align='center'>"+data['items'][i]['last_ip']+"</td>";
                    s+="<td width='120' align='center'>"+data['items'][i]['created_on']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['programs']+"</td>";
                    s+="<td align='center'>";
                        s+="<div class='dropdown-menu'>More action";
                            s+="<ul lang='"+data['items'][i]['id']+"'>";
                                s+="<li id='drp_edit'>Edit</li>";
                                if (parseInt(data['items'][i]['programs'])==0)
                                    s+="<li id='drp_delete'>Delete</li>";
                                s+="<li id='drp_view' onclick='programPersonal("+data['items'][i]['id']+");'>Programs</li>";
                            s+="</ul>";
                        s+="</div>";
                    s+="</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(page, keyword, data['pages']);
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
            $('li#drp_edit').click (function(){
                var id = $(this).parent().attr('lang');
                window.location = "profile/"+id+"/accounts";
            })
            $('li#drp_delete').click (function(){
                var id = $(this).parent().attr('lang');
                if (confirm("Hapus akun terpilih ?")){
                    deleteRecords(id);
                }
            })
        })
    }
    function createNavigator(page_active, keyword, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0;i<num_of_pages;i++){
                var s="<li onclick='loadUsers("+i+",\""+keyword+"\");'";
                if (i==page_active) s+=" class='active'";
                s+=">"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function programPersonal(user_id)
    {
        window.location = "personals/"+user_id;
    }
    function deleteRecords(id)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteUser',param:id},function(result){
            $('div#my-loader').hide();
            if (parseInt(result)==1){
                $('tr#'+id).remove();
            }else{
                alert(result.substr(1));
            }
        })
    }
</script>
<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Kelola Akun Pengguna</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="execute" id="btn_create">Tambah User</li>
                    <li class="execute" id="btn_edit">Edit User</li>
                    <li class="search">&laquo;</li>
                    <li class="search">
                        <input type="text" id="keyword" name="keyword"  />
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
                    <th>User Name</th>
                    <th>Nama Lengkap</th>
                    <th>Posisi</th>
                    <th>Hak Akses</th>
                    <th>Status Login</th>
                    <th>Last Login</th>
                    <th>Last IP</th>
                    <th>Dibuat Tanggal</th>
                    <th>Program</th>
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
