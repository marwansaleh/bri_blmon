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

//check if user id different with this user,
//means another user try to edit
//check if has access to edit different user account
if (!userHasAccess($access, "BUDGET_CREATE"))
{
    header("location: index");
    exit;
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
        tabs = new Tabs({"tabs_01":"New Kredit"}, "tabs-parent");
        tabs.activePage = 0;
        tabs.init();
        
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback 
            type:           'post',
            dataType:       'json',
            resetForm: true
        }; 
        // bind form using 'ajaxForm' 
        $('form#frm_update').ajaxForm(options); 
        
        $('li#btn_home').click(function(){
            window.location = "index";
        })
        $('input#btn_close').click(function(){
            window.location = "index";
        })
    })
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['trans_desc'].value==''){
            alert("Nama transaksi tidak boleh kosong")
            return false; 
        }
        if (jqForm[0]['trans_credit'].value==''||!isNumber(jqForm[0]['trans_credit'].value)){
            alert("Nilai transaksi tidak valid")
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result=='1'){      
            $('p.error-message').text("Saldo berhasil di-update").hide().show('slow').delay(5000).hide('slow');
        }
        else alert('Gagal mengupdate saldo');
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1 id="page-title">Update Saldo</h1>      
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="creditSaldo" />
                <input type="hidden" id="param" name="param" value="0" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama transaksi kredit</td>
                                <td><input type="text" id="trans_desc" name="trans_desc" value="" /></td>
                            </tr>
                            <tr>
                                <td class="title">Nilai transaksi (Rp)</td>
                                <td><input type="text" id="trans_credit" name="trans_credit" value="0.00" class="numeric" /></td>
                            </tr>
                        </table>
                        <p style="font-style: italic;">Saldo akan ditambahkan untuk saldo alokasi dan saldo realisasi</p>
                    </div>
                    
                    
                </div>
                <div class="content">
                    <table class="data-input">
                        <tr>
                            <td>
                                <input type="submit" id="btn_submit" name="btn_submit" value="Simpan" />
                                <input type="reset" id="btn_reset" name="btn_reset" value="Reset" />
                                <input type="button" id="btn_close" name="btn_close" value="Close" />
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