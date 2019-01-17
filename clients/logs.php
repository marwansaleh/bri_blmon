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
if (!userHasAccess($access, "MANAGE_LOG"))
{
    header("location: index");
    exit;
}

if (isset($_GET['keyword']))
{
    $keyword = $_GET['keyword'];
}
?>
<!DOCTYPE html>
<html>
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        loadLogs(0,'');
        $('li#btn_home').click(function(){
            window.location = "index";
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
            else if (confirm("Hapus logs terpilih ?")){
                deleteRecords(id);
            }
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            loadLogs(0,keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});      
    })
    function loadLogs(page, keyword)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadLogs',param:page,search_str:keyword},function(result){
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
                    s+="<td> <input type='checkbox' id='check' name='check[]' value='"+data['items'][i]['id']+"'></td>";
                    s+="<td width='120'>"+data['items'][i]['log_date']+"</td>";
                    s+="<td width='100' align='center'>"+data['items'][i]['ip_address']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['username']+"</td>";
                    s+="<td>"+data['items'][i]['page']+"</td>";
                    s+="<td>"+data['items'][i]['request']+"</td>";
                    s+="<td>"+data['items'][i]['action']+"</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(page, keyword, data['pages']);
            }
        })
    }
    function createNavigator(page_active, keyword, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0;i<num_of_pages;i++){
                var s="<li onclick='loadLogs("+i+",\""+keyword+"\");'";
                if (i==page_active) s+=" class='active'";
                s+=">"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteLogs',param:id_array.join()},function(result){
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
            }
            if (result['error_message']!='')
                alert(result['error_message']);
        })
    }
</script>
<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Program CSR BRI</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="execute" id="btn_delete">Hapus Log</li>
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
                    <th>Tanggal</th>
                    <th>IP Address</th>
                    <th>User</th>
                    <th>Page</th>
                    <th>Request</th>
                    <th>Action</th>
                </tr>
                <tr class="row-msg"><td colspan="7"></td></tr>
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
