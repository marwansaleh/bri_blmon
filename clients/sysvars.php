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
$max_parameter_alllowed = 1;
security_uri_check($max_parameter_alllowed, $qs);

$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);
if (!userHasAccess($access, "MANAGE_SYSVAR"))
{
    header("location: index");
    exit;
}
function funcs_sys_return($func_id, DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection ();
    switch($func_id)
    {
        case 'load_style': return loadStyleFolder(); break;
        case 'type': return load_article_types($db_obj); break;
        default: return false;
    }
}
function loadStyleFolder()
{
    $directory = array();
    if ($handle = opendir('../customs/style/templates')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry!='.'&&$entry!='..')
            $directory[$entry]=$entry;
        }

        closedir($handle);
    }
    return $directory;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        $('li#btn_home').click(function(){
            window.location = "programs";
        })
        $('li#btn_update').click ( function ()
	{
            //check if any record in the table, if not, send alert and do not proceed
            if($('tr.row-msg').length==0)
            {
                alert('Tidak ada record data pada tabel');
                return;
            }
            var data_id = [];
            var data_value = [];
            var data_var = [];
            $("tr.row-msg").each ( function ()
            {
                data_id.push($(this).attr('id'));
                data_var.push($(this).find('td').eq(1).text());
                data_value.push($(this).find('.input').val());
            });
            if(data_id.length<1)
                alert("Pilih / checked record data yang akan diupdate");
            else if (confirm('Update semua system variables ?')==true)			
            {
                var btn = $(this);
                var original_text = btn.text();
                //change the text so user knows it is processing
                btn.text('Wait ...');
                $('div#my-loader').show();
                $.post("ajax",{input_function:'updateSysVarAll',param:data_id.join(),var_name:data_var.join('|'),var_value:data_value.join('|')}, 
                    function(result){
                    $('div#my-loader').hide();
                    btn.text(original_text);
                    if(parseInt(result)==1)
                        alert('Update semua sysvar berhasil !');
                    else
                        alert(result.substr(1));
                });
             }
	});
        $('div.buttons').click(function(){
            var var_id = $(this).attr('id');
            var var_name = $(this).parent().parent().find('td').eq(1).text();
            var new_val = $(this).parent().parent().find('.input').val();
            //alert('var_id:'+var_id+',new_val:'+new_val+', var_name:'+var_name);
                    
            var btn = $(this);
            var original_text = btn.text();
            //change the text so user knows it is processing
            btn.text('Wait ...');
            $('div#my-loader').show();
            $.post("ajax",{input_function:'updateSysVar',param:var_id,var_name:var_name,var_value:new_val}, 
                function(result){
                $('div#my-loader').hide();
                btn.text(original_text);
                if(parseInt(result)!=1)
                    alert(result.substr(1));
                    
             })
        })
    })
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Kelola Variable System</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="execute" id="btn_update">Update All SYSVAR</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                <tr>
                    <th>#</th>
                    <th align="left">Variable Name</th>
                    <th align="left">Variable Value</th>
                    <th align="left">Deskripsi</th>
                    <th>Command</th>
                </tr>
                <?php
                $sql= "SELECT id, var, var_value, var_description, var_option, func_name FROM sysvars ORDER BY var";
                $result = $db_obj->execSQL($sql);
                $i=1;
                if ($result) foreach($result as $item){
                    echo "<tr class='row-msg' id='".$item['id']."'>";
                    echo "<td width='30' align='center'>".$i."</td>"; $i++;
                    echo "<td width='200'>".$item['var']."</td>";
                    if ($item['var_option']==0)
                        echo "<td><input class='input' type='text' style='width:300px;padding:5px 0 5px 0;' id='".$item['var']."' name='".$item['var']."' value='".$item['var_value']."' /></td>";
                    else if ($item['var_option']==1&&$item['func_name'])
                    {
                        echo "<td>";
                        echo "<select class='input' id='".$item['var']."' name='".$item['var']."' style='width:300px;padding:5px 0 5px 0;'>";
                        $funcs_result = funcs_sys_return($item['func_name'], $db_obj);
                        if ($funcs_result)foreach($funcs_result as $key=>$val)
                        {
                            echo "<option value='".$key."'";
                            if ($item['var_value']==$key) echo " selected";
                                echo ">".$val."</option>";
                        }
                        echo "</select>";
                        echo "</td>";
                    }
                                
                    echo "<td>".$item['var_description']."</td>";
                    echo "<td width='100'>";
                    echo "<div class='buttons' id='".$item['id']."'>UPDATE VAR</div>";
                    echo "</td>";
                    echo "</tr>";
                }else{
                    echo "<tr>";
                    echo "<td colspan='11'>Tidak system variables terdefinisi di database</td>";
                    echo "</tr>";
                }
                ?>
             </table>
        </div>
        <div class="clr"></div>
        <div class="content"></div>
    </div>
    <?php echo document_footer();?>
</body>
</html>