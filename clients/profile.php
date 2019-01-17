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

if (isset($qs[1])){
    $id = $qs[1];
}else{
    $id = get_user_info("ID");
}
if (isset($qs[2])){
    $caller_page = $qs[2];
}else{
    $caller_page = 'index';
}
$db_obj = new DatabaseConnection();
$access = loadUserAccess($db_obj);

//check if user id different with this user,
//means another user try to edit
//check if has access to edit different user account
if ($id != get_user_info("ID"))
{
    if (!userHasAccess($access, "ACCOUNT_MANAGE"))
    {
        header("location: index");
        exit;
    }
}
if ($id>0)
{
    $sql = "SELECT id, user_name, full_name,position, access,
            login_status, last_login, last_ip, created_on, avatar
            FROM users
            WHERE id=$id";
    $data_result = $db_obj->execSQL($sql);
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
    var caller_page = "<?php echo $caller_page;?>";
    $(document).ready(function(){
        tabs = new Tabs({"tabs_01":"User","tabs_02":"Photo Profil"}, "tabs-parent");
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
            window.location = caller_page;
        })
        $('input#btn_close').click(function(){
            window.location = caller_page;
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/0/"+caller_page;
        })
        $('input[type="file"]').change ( function ()
        {
            var new_upload_file = $(this).val();
            var allowable_ext = ["jpg"];
            if (new_upload_file !=''){
                arr_file = new_upload_file.split('.');
		ext = arr_file[arr_file.length-1];
		if(allowable_ext.indexOf(ext.toLowerCase())==-1)
		{
                    alert('Format '+ext.toLowerCase()+' tidak dapat diupload');
                    $(this).val('');
		}
            }
            else alert('Tidak ada file yang akan diupload');
	});
    })
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['user_name'].value==''){
            alert("Username tidak boleh kosong")
            return false; 
        }
        if (jqForm[0]['full_name'].value==''){
            alert("Nama tidak boleh kosong")
            return false; 
        }
        if (jqForm[0]['access'].value=='1'&&jqForm[0]['access_ori'].value!='1'){
            if (confirm('Yakin memilih tipe akses "admin" ?\nUser akses admin memiliki semua akses')==false)
                return false;
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){   
            if (result['upload']==true){
                if (result['filename']!=''){
                    $('img#img_avatar').attr('src',result['filename']);
                }
                $('input#avatar').val('');
            }
            $('input#param').val(result['id']);
            $('p.error-message').text("Akun berhasil di-update").hide().show('slow').delay(5000).hide('slow');
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
            <h1 id="page-title">Update Account</h1>      
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="accountUpdate" />
                <input type="hidden" id="param" name="param" value="<?php echo $id;?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Username</td>
                                <?php $user_name = (isset($data_result)?$data_result[0]['user_name']:'');?>
                                <td><input type="text" id="user_name" name="user_name" value="<?php echo $user_name;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Nama Lengkap</td>
                                <?php $full_name = (isset($data_result)?$data_result[0]['full_name']:'');?>
                                <td><input type="text" id="full_name" name="full_name" value="<?php echo $full_name;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Posisi / Jabatan</td>
                                <?php $position = (isset($data_result)?$data_result[0]['position']:'Staf CSR');?>
                                <td><input type="text" id="position" name="position" value="<?php echo $position;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">User Akses</td>
                                <?php $u_access = (isset($data_result)?$data_result[0]['access']:3);?>
                                <td>
                                    <input type="hidden" id="access_ori" name="access_ori" value="<?php echo $u_access;?>" />
                                    <?php if (userHasAccess($access, "ACCOUNT_MANAGE")){?>                                    
                                    <select id="access" name="access">
                                    <?php }else{?>
                                    <select id="access" name="access" disabled>                                    
                                    <?php }?>
                                        <?php
                                        $sql = "SELECT id, type FROM user_types";
                                        $accesses = $db_obj->execSQL($sql);
                                        if ($accesses)foreach($accesses as $item){
                                            echo "<option value='".$item['id']."'";
                                            if ($u_access==$item['id']) echo " selected";
                                            echo ">".$item['type']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Password</td>
                                <td><input type="text" id="password" name="password" /></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="tabs-each" id="tabs_02">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Photo Profil</td>
                            </tr>
                            <tr>
                                <?php $avatar = (isset($data_result)?$data_result[0]['avatar']:'default.jpg');?>
                                <td>
                                    <img id="img_avatar" src="<?php echo PROFILE_URL. $avatar;?>" 
                                         width="70" height="80" alt="Photo profil"
                                         style="border-right:solid 2px #ccc; border-bottom:solid 2px #ccc;" />
                                </td>
                            </tr>
                            <tr>
                                <td>Upload to change  (70x80px;jpg)<input type="file" id="avatar" name="avatar" /></td>
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
                                <?php if (userHasAccess($access, "ACCOUNT_MANAGE")){?>
                                <input type="button" id="btn_new" name="btn_new" value="New" />
                                <?php }?>
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