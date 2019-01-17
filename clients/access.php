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
if (!userHasAccess($access, "ACCOUNT_ACCESS_RIGHTS"))
{
    header("location: index");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        $('li#btn_home').click(function(){
            window.location = "index";
        })
        $('li#btn_update').click ( function ()
	{
            //check if any record in the table, if not, send alert and do not proceed
            if($('tr.row-msg').length==0)
            {
                alert('Tidak ada record data pada tabel');
                return;
            }
            var access_id = [];
            var access = [];
            var access_name = [];
            $("tr.row-msg").each ( function ()
            {
                access_id.push($(this).attr('id'));
                access_name.push($(this).find('td').eq(1).text());
                var access_val='';
                $(this).find(':checked').each(function(){
                    access_val+=$(this).val()+'|';
                });
                access.push(access_val.substr(0,3));
            });
            //alert(access.join());
                    
            if(access_id.length<1)
                alert("Pilih / checked record data yang akan diupdate");
            else if (confirm('Update semua hak akses ?')==true)			
            {
                var btn = $(this);
                var original_text = btn.text();
                //change the text so user knows it is processing
                btn.text('Wait ...');
                $('div#my-loader').show();
                $.post("ajax",{input_function:'updateAccessAll',param:access_id.join(),var_name:access_name.join('|'),var_value:access.join()}, 
                    function(result){
                    $('div#my-loader').hide();
                    btn.text(original_text);
                    if(parseInt(result)==1)
                        alert('Update semua hak akses berhasil !');
                    else
                        alert(result.substr(1));
                 });
            }
                    
	});
        $('div.buttons').click(function(){
            var access_id = $(this).attr('id');
            var access_name = $(this).parent().parent().find('td').eq(1).text();
            //var author = $(this).parent().parent().find('td').eq(4).find('input :checked').val();
            var access = [];
            $('tr#'+access_id+' :checked').each(function(){
                access.push($(this).val());
            })
            //alert('access_id:'+access_id+',access_name:'+access_name+', access:'+access.join());
                    
            var btn = $(this);
            var original_text = btn.text();
            //change the text so user knows it is processing
            btn.text('Wait ...');
            $('div#my-loader').show();
            $.post("ajax",{input_function:'updateAccess',param:access_id,var_name:access_name,var_value:access.join()}, 
                function(result){
                $('div#my-loader').hide();
                btn.text(original_text);
                if(parseInt(result)==1)
                    alert('Update hak akses berhasil !');
                else
                    alert(result.substr(1));
            });
        })
    })
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Kelola Hak Akses User</h1>
            <div id="panel-buttons">
                <ul>
                    <li>&raquo;</li>
                    <li class="execute" id="btn_home">Home</li>
                    <li class="execute" id="btn_update">Update All Rights</li>
                    <li class="search">&laquo;</li>
                </ul>
            </div>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <table class="data-list">
                        <tr>
                            <th>#</th>
                            <th>Hak Akses</th>
                            <th>Deskripsi Akses</th>
                            <th>Supervisor</th>
                            <th>Staf</th>
                            <th>Command</th>
                        </tr>
                        <?php
                        $sql= "SELECT id, access, description, supervisor,staf FROM user_access";
                        if (isset($keyword))
                            $sql.=" WHERE (access LIKE '%$keyword%')OR(category LIKE '%$keyword%')";
                        
                        $sql.=" ORDER BY access";
                        
                        $result = $db_obj->execSQL($sql);
                        $i=1;
                        if ($result) foreach($result as $item){
                            echo "<tr class='row-msg' id='".$item['id']."'>";
                            echo "<td width='30' align='center'>".$i."</td>"; $i++;
                            echo "<td>".strtoupper($item['access'])."</td>";
                            echo "<td>".$item['description']."</td>";
                            echo "<td align='center'>";
                                echo "<input type='radio' name='supervisor".$item['id']."' value='1'".($item['supervisor']==1?' checked':'')." />enabled<br />";
                                echo "<input type='radio' name='supervisor".$item['id']."' value='0'".($item['supervisor']==0?' checked':'')." />disabled";
                            echo "</td>";
                            echo "<td align='center'>";
                                echo "<input type='radio' name='staf".$item['id']."' value='1'".($item['staf']==1?' checked':'')." />enabled<br />";
                                echo "<input type='radio' name='staf".$item['id']."' value='0'".($item['staf']==0?' checked':'')." />disabled";
                            echo "</td>";
                            echo "<td width='100'>";
                            echo "<div class='buttons' id='".$item['id']."'>Update Access</div>";
                            echo "</td>";
                            echo "</tr>";
                        }else{
                            echo "<tr>";
                            echo "<td colspan='6'>Tidak ada hak akses terdefinisi di database</td>";
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