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
if (!userHasAccess($access, "TASK_EDIT"))
{
    header("location: tasks");
    exit();
}

if (isset($qs[1])&&$qs[1]!='')
    $mode = sanitizeText ($qs[1]);

if ($mode==ACT_EDIT){
    if (isset($qs[2]))
    {
        $id = $qs[2];
        $sql = "SELECT id, program, task, target, completed
                FROM tasks
                WHERE id=$id";
        $data_result = $db_obj->execSQL($sql);
		if ($data_result)
			$program = $data_result[0]['program'];
		else
		{
			$data_result = NULL;
			$id = 0;
			$error_message = "Error. Mode edit namun gagal meload database";
		}
    }
    else
    {
        $id = 0;
        $error_message = "Error. Mode edit namun id kegiatan tidak terdefinisi";
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
        tabs = new Tabs({"tabs_01":"Kegiatan","tabs_02":"Dokumen Referensi"}, "tabs-parent");
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
            window.location = "tasks/<?php echo $program;?>";
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/<?php echo ACT_CREATE;?>/<?php echo $program;?>";
        })
        $('input[type="file"]').change ( function ()
        {
            var new_upload_file = $(this).val();
            var allowable_ext = ["jpg","doc","pdf"];
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
        if (jqForm[0]['task'].value==''){
            alert("Nama kegiatan tidak boleh kosong")
            return false; 
        }
        if (parseInt(jqForm[0]['target'].value)==0){            
            alert("Nilai 'target' tidak valid");
            return false;
        }
        if (parseInt(jqForm[0]['target'].value)<parseInt(jqForm[0]['completed'].value)){
            alert("Nilai 'completed' tidak boleh lebih besar dari 'target'");
            return false;
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        $('input[type="file"]').val('');
        if (result['success']==true){
            if (result['upload']==true){
                var s = "<tr id='"+result['upload_info']['id']+"'>";
                    s+= "<td><a class='view-docref' onclick='openDocRef(\""+result['upload_info']['filename']+"\");'>"+result['upload_info']['filename']+"</a></td>";
                    s+= "<td align='center'>"+result['upload_info']['filetype']+"</td>";
                    s+= "<td align='center'><div class='icon-oknot' title='delete file' onclick='delete_docref("+result['upload_info']['id']+");'></div></td>";
                s+="</tr>";
                
                $('table#tbl_doc_references').append(s);
            }
            $('input#id').val(result['task_id']);
            $('p.error-message').text("Kegiatan berhasil di-update").hide().show('slow').delay(5000).hide('slow');
        }
        if (result['error']!='') alert(result['error']);
    }
    function delete_docref(doc_id)
    {
        if (confirm("Hapus dokumen referensi ini ?")){
            $('div#my-loader').show();
            $.post("ajax",{input_function:"deleteDocRef",param:doc_id},function(result){
                $('div#my-loader').hide();
                if (parseInt(result)==1){
                    $('table#tbl_doc_references tr#'+doc_id).remove();
                }else{
                    alert(result.substr(1));
                }
            })
        }
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
                <input type="hidden" id="input_function" name="input_function" value="saveTasks" />
                <input type="hidden" id="param" name="param" value="<?php echo $program;?>" />
                <input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama Kegiatan</td>
                                <?php $task = (isset($data_result)?$data_result[0]['task']:'');?>
                                <td><input type="text" id="task" name="task" value="<?php echo $task;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Target (volume)</td>
                                <?php $target = (isset($data_result)?$data_result[0]['target']:1);?>
                                <td><input type="text" id="target" name="target" value="<?php echo $target;?>" class="numeric" /></td>
                            </tr>
                            <tr>
                                <td class="title">Completed (volume)</td>
                                <?php $completed = (isset($data_result)?$data_result[0]['completed']:0);?>
                                <td><input type="text" id="completed" name="completed" value="<?php echo $completed;?>" class="numeric" /></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="tabs-each" id="tabs_02">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Dokumen Referensi</td>                                
                                <td><input type="file" id="doc_reference" name="doc_reference" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table class="doc-references-list" id="tbl_doc_references">
                                        <tr>
                                            <th align='left'>Filename</th>
                                            <th width="80">Filetype</th>
                                            <th width="60">Action</th>
                                        </tr>
                                        <?php
                                        if (isset($data_result)){
                                            $id = $data_result[0]['id'];
                                            $sql = "SELECT id, filename, filetype FROM doc_references
                                                    WHERE task=$id";
                                            $doc_references = $db_obj->execSQL($sql);
                                            if ($doc_references){
                                                foreach($doc_references as $item){
                                                    echo "<tr id='".$item['id']."'>";
                                                        echo "<td><a class='view-docref' onclick='openDocRef(\"".$item['filename']."\");'>".$item['filename']."</a></td>";
                                                        echo "<td align='center'>".get_label_docref_type($item['filetype'])."</td>";
                                                        echo "<td align='center'><div class='icon-oknot' title='delete file' onclick='delete_docref(".$item['id'].");'></div></td>";
                                                    echo "<tr>";
                                                }

                                            }else{
                                                echo "<tr><td colspan=\"2\"></td></tr>";
                                            }
                                        }
                                        ?>    
                                    </table>
                                </td>
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