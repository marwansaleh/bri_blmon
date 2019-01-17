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
if (!userHasAccess($access, "MANAGE_PROGRAM_TYPES"))
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
        loadProTypes(0);
        $('li#btn_home').click(function(){
            window.location = "index";
        })
        $('li#btn_create').click(function(){
            window.location = "protypes_update/0";
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
                window.location = "protypes_update/"+id[0];
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
            else if (confirm("Hapus bidang terpilih ?")){
                deleteRecords(id);
            }
        })
    })
    function loadProTypes(page)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadProTypes',param:page},function(result){
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
                    s+="</td>";
                    s+="<td width='120'>"+data['items'][i]['type']+"</td>";
                    s+="<td>"+data['items'][i]['creation_by']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['creation_date']+"</td>";
                    s+="<td>"+data['items'][i]['last_update_by']+"</td>";
                    s+="<td align='center'>"+data['items'][i]['last_update']+"</td>";
                    s+="<td align='right'>"+data['items'][i]['program']+"</td>";
                    s+="</tr>";
                
                    $("table.data-list").append(s);
                }
                //create navigator buttons if needed
                createNavigator(page, data['pages']);
            }
        })
    }
    function createNavigator(page_active, num_of_pages)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i=0;i<num_of_pages;i++){
                var s="<li onclick='loadProTypes("+i+");'";
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
    function deleteRecords(id_array)
    {
        $('div#my-loader').show();
        $.post("ajax",{input_function:'deleteProtypes',param:id_array.join()},function(result){
            $('div#my-loader').hide();
            if(parseInt(result)==1){
                for(var i in id_array)
                {				
                    //remove message rows in the table
                    $('tr.row-msg').each ( function ()
                    {
                        if ($(this).attr('id')==id_array[i])
                            $(this).remove();
                    });
                }
            }
            else alert(result.substr(1));
        })
    }
</script>
<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Kelola Bidang Program BL BRI</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="execute" id="btn_create">Tambah Bidang</li>
                    <li class="execute" id="btn_edit">Edit Bidang</li>
                    <li class="execute" id="btn_delete">Hapus Bidang</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                <tr>
                    <th>#</th>
                    <th>Nama bidang</th>
                    <th>Dibuat Oleh</th>
                    <th>Tanggal Buat</th>
                    <th>Last Update Oleh</th>
                    <th>Tanggal Update</th>
                    <th># Program</th>
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
