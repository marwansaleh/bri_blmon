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
$max_parameter_alllowed = 3;
security_uri_check($max_parameter_alllowed, $qs);

//Create database Object
$db_obj = new DatabaseConnection();

//load user access
$access = loadUserAccess($db_obj);
if (!userHasAccess($access, "REALBUDGET_EDIT"))
{
    header("location: realisation");
    exit();
}

if (isset($qs[1])&&$qs[1]!='')
    $mode = sanitizeText ($qs[1]);

if ($mode==ACT_EDIT){
    if (isset($qs[2]))
    {
        $id = $qs[2];
        $sql = "SELECT id, program, caption, nominal
                FROM budget_real_used
                WHERE id=$id";
        $data_result = $db_obj->execSQL($sql);
        $program = $data_result[0]['program'];
    }
    else
    {
        $id = 0;
        $error_message = "Error. Mode edit namun id real budget tidak terdefinisi";
    }
}else{
    $id = 0;
    $program = $qs[2];
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
        tabs = new Tabs({"tabs_01":"Kegiatan"}, "tabs-parent");
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
    
        $('input#btn_close').click(function(){
            window.location = "realisation/<?php echo $program;?>";
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/<?php echo ACT_CREATE;?>/<?php echo $program;?>";
        })
        
    })
    
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['caption'].value==''){
            alert("Nama realisasi tidak boleh kosong")
            return false; 
        }
        if (parseInt(jqForm[0]['nominal'].value)==0){            
            alert("Nilai 'dana realisasi' tidak valid");
            return false;
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){            
            $('input#id').val(result['real_id']);
            $('p.error-message').text("Dana realisasi berhasil di-update").hide().show('slow').delay(5000).hide('slow');
        }
        if (result['error']!='') alert(result['error']);
    }
    function openDocRef(filename){
        var wnd = window.open("view_docref?file="+filename,"DocRef");
        wnd.focus();
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Program: <?php echo get_program_name_by_id($program, $db_obj);?></h1>
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="saveRealBudget" />
                <input type="hidden" id="param" name="param" value="<?php echo $program;?>" />
                <input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama Realisasi</td>
                                <?php $caption = (isset($data_result)?$data_result[0]['caption']:'');?>
                                <td><input type="text" id="caption" name="caption" value="<?php echo $caption;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Dana Realisasi (Rp)</td>
                                <?php $nominal = (isset($data_result)?$data_result[0]['nominal']:0);?>
                                <input type="hidden" id="nominal_original" name="nominal_original" value="<?php echo $nominal;?>" />
                                <td><input type="text" id="nominal" name="nominal" value="<?php echo $nominal;?>" class="numeric" /></td>
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