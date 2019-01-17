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

//Create database Object
$db_obj = new DatabaseConnection();
//load user access
$access = loadUserAccess($db_obj);
if (isset($qs[1])&&$qs[1]!='')
    $id = sanitizeText ($qs[1]);
else
    $id = 0;

if ($id>0)
{
    $sql = "SELECT id, propinsi, kabupaten, ibukota, luas, populasi, web
            FROM kabupaten
            WHERE id=$id";
    $data_result = $db_obj->execSQL($sql);
    if (!$data_result) $error_message = "Gagal meng-load data kabupaten dari database";
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
        tabs = new Tabs({"tabs_01":"Kabupaten","tabs_02":"Info Tambahan"}, "tabs-parent");
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
            window.location = "prop_kab/"+$('select#propinsi').val();
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/0";
        })
    })
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['kabupaten'].value==''){
            alert("Nama kota / kabupaten tidak boleh kosong")
            return false; 
        }
        if (jqForm[0]['ibukota'].value==''){
            alert("Nama ibukota tidak boleh kosong")
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){
            $('input#param').val(result['id']);
            $('p.error-message').text("Data kabupaten berhasil di-update").hide().show('slow').delay(5000).hide('slow');
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
            <h1 id="page-title">Update Data Kabupaten</h1>
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="saveKabupaten" />
                <input type="hidden" id="param" name="param" value="<?php echo $id;?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama kabupaten / Kota</td>
                                <?php $kabupaten = (isset($data_result)?$data_result[0]['kabupaten']:'');?>
                                <td><input type="text" id="kabupaten" name="kabupaten" value="<?php echo $kabupaten;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Ibukota</td>
                                <?php $ibukota = (isset($data_result)?$data_result[0]['ibukota']:'');?>
                                <td><input type="text" id="ibukota" name="ibukota" value="<?php echo $ibukota;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Propinsi</td>
                                <?php $propinsi = (isset($data_result)?$data_result[0]['propinsi']:1);?>
                                <?php $provinces= load_propinsi($db_obj);?>
                                <td>
                                    <select id="propinsi" name="propinsi">
                                        <?php if ($provinces)foreach($provinces as $item){
                                           echo "<option value='".$item['id']."'";
                                           if ($propinsi==$item['id']) echo " selected";
                                           echo ">".$item['propinsi']."</option>";
                                        }?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_02">
                        <table class="data-input">
                            <tr>
                                <td class="title">Luas area (km2)</td>
                                <?php $luas = (isset($data_result)?$data_result[0]['luas']:0);?>
                                <td><input type="text" id="luas" name="luas" value="<?php echo $luas;?>" class="numeric" /></td>
                            </tr>
                            <tr>
                                <td class="title">Jumlah Populasi (jiwa)</td>
                                <?php $populasi = (isset($data_result)?$data_result[0]['populasi']:0);?>
                                <td><input type="text" id="populasi" name="populasi" value="<?php echo $populasi;?>" class="numeric" /></td>
                            </tr>      
                            <tr>
                                <td class="title">Alamat Website Kabupaten</td>
                                <?php $web = (isset($data_result)?$data_result[0]['web']:'');?>
                                <td><input type="text" id="web" name="web" value="<?php echo $web;?>" /></td>
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