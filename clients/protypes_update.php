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
    $id = $qs[1];
}else{
    $id = 0;
}
$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);

if (!userHasAccess($access, "MANAGE_PROGRAM_TYPES"))
{
    header("location: index");
    exit;
}
if ($id>0)
{
    $sql = "SELECT id, type
            FROM program_types
            WHERE id=$id";
    $data_result = $db_obj->execSQL($sql);
    if (!$data_result)
        $error_message = $db_obj->getLastEror();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script language="javascript" type="text/javascript" src="customs/js/tabs.js"></script>
<script language="javascript" type="text/javascript" src="customs/js/jquery.form.js"></script>
<script type="text/javascript"> 
    var tabs;
    $(document).ready(function(){
        tabs = new Tabs({"tabs_01":"Bidang Program"}, "tabs-parent");
        tabs.activePage = 0;
        tabs.init();
        
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback 
            type:           'post',
            dataType:       'json'
        }; 
        // bind form using 'ajaxForm' 
        $('form#frm_update').ajaxForm(options); 
        
        $('li#btn_home').click(function(){
            window.location = "protypes";
        })
        $('input#btn_close').click(function(){
            window.location = "protypes";
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/0";
        })
    })
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['type'].value==''){
            alert("Nama bidang tidak bole kosong");
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){  
            $('input#param').val(result['items']['id']);
            $('p.error-message').text(result['items']['type']+" berhasil di-update").hide().show('slow').delay(5000).hide('slow');
        }
        if (result['error']!='') alert(result['error']);
    }
    
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Update Bidang Program</h1>      
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="protypeUpdate" />
                <input type="hidden" id="param" name="param" value="<?php echo $id;?>" />
                <input type="hidden" id="creation_date" name="creation_date" value="<?php echo (isset($data_result)?$data_result[0]['creation_date']:'');?>" />
                <input type="hidden" id="creation_by" name="creation_by" value="<?php echo (isset($data_result)?$data_result[0]['creation_by']:'');?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama bidang</td>
                                <?php $type = (isset($data_result)?$data_result[0]['type']:'');?>
                                <td><input type="text" id="type" name="type" value="<?php echo $type;?>" /></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="content">
                    <table class="data-input">
                        <tr>
                            <td>
                                <input type="submit" id="btn_submit" name="btn_submit" value="Simpan" />
                                <input type="reset" id="btn_reset" name="btn_reset" value="Reset" />
                                <input type="button" id="btn_close" name="btn_close" value="Close" />
                                <input type="button" id="btn_new" name="btn_new" value="New" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>    
        </div>
        <div class="clr"></div>
        <div class="content"></div>
    </div>
    <?php echo document_footer();?>
</body>
</html>