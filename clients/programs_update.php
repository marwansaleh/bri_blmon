<?php 
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/tools.php");
require_once("./../funcs/constant.php"); 
require_once("./../funcs/db_config.php");

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
if (!userHasAccess($access, "PROGRAM_EDIT"))
{
    header("location: programs");
    exit();
}
if (isset($qs[1])&&$qs[1]!='')
    $mode = sanitizeText ($qs[1]);

if ($mode==ACT_EDIT){
    if (isset($qs[2]))
    {
        $id = $qs[2];
        $sql = "SELECT id, type, source, name, description, potensi_bisnis, pic, uker_cabang, uker_wilayah,
                budget, operational, benef_name, benef_address, benef_phone, benef_email,
                benef_orang, benef_unit, state, creation_date, approval_date, nodin_putusan,
		tgl_putusan,nomor_persetujuan,nomor_registrasi,tgl_register,nomor_bg
                FROM programs
                WHERE id=$id";
        $data_result = $db_obj->execSQL($sql);
    }
    else
    {
        $id = 0;
        $error_message = "Error. Mode edit namun id program tidak terdefinisi";
    }
}else{
    $id=0;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script language="javascript" type="text/javascript" src="customs/js/tabs.js"></script>
<script language="javascript" type="text/javascript" src="customs/js/jquery.form.js"></script>
<script type="text/javascript">
    var tabs, pic_timer, benef_timer;    
    $(document).ready(function(){
        $( ".datepicker" ).datepicker({
            dateFormat: "yy-mm-dd"
        });
        // masking
        $('#nomor_registrasi').mask("0999.09.099");
        
        tabs = new Tabs({"tabs_01":"Program","tabs_02":"Unit Kerja","tabs_03":"Penerima Manfaat","tabs_04":"Tambahan","tabs_05":"Dokumentasi"}, "tabs-parent");
        tabs.activePage = 0;
        tabs.init();
        
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback 
            type:           'post',
            dataType:       'json'
            // other available options: 
            //url:       url         // override for form's 'action' attribute 
            //type:      type        // 'get' or 'post', override for form's 'method' attribute 
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
            //clearForm: true        // clear all form fields after successful submit 
            //resetForm: true        // reset the form after successful submit 

            // $.ajax options can be used here too, for example: 
            //timeout:   3000 
        }; 
 
        // bind form using 'ajaxForm' 
        $('form#frm_update').ajaxForm(options); 
    
        $('input#btn_close').click(function(){
            window.location = "programs/"+$('select#type').val();
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/<?php echo ACT_CREATE;?>";
        })
        $('select#kanwil').change(function(){
            var wilayah = $(this).val();
            var type = $('option:selected', this).attr('type');
            if (type == 'KP') wilayah = 0;
			
            loadKancab(wilayah);
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
        $('input#pic').keypress(function(event){
            var target_obj = $(this).parent().find('div.lookup_bg');
            if (target_obj.css('display')=='none')
                target_obj.show();            
            
            lookupPIC(target_obj, $(this).val());
        })
        $('input#benef_name').keypress(function(event){
            var target_obj = $(this).parent().find('div.lookup_bg');
            if (target_obj.css('display')=='none')
                target_obj.show();
            
            lookupBeneficiaries(target_obj, $(this).val());
        })
        $('input.lookup').blur(function(){
            if($(this).parent().find('ul li').length==0&&$(this).parent().find('div.lookup_bg').css('display')!='none')
                $(this).parent().find('div.lookup_bg').hide();
        })
    })
    function loadKancab(wilayah)
    {
        $('select#kancab').empty();
        $.post("ajax",{input_function:'loadKancabByParent',param:wilayah},function(result){
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                //add wilayah
                wilayah_name= $('select#kanwil :selected').text();
                var s= "<option value='0'>---"+wilayah_name+"(KW)---</option>";
                $('select#kancab').append(s);
                for(var i in data['items']){
                    s= "<option value='"+data['items'][i]['id']+"'>"+data['items'][i]['uker']+"</option>";
                    $('select#kancab').append(s);
                }
            }else{
                var s = "<option value='0'>---empty---</option>";
                $('select#kancab').append(s);
            }
        })
    }
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['name'].value==''){
            alert("Nama program tidak boleh kosong");
            $('#name').focus();
            return false; 
        }
        //check if numeric field is valid
        if (!isNumber(jqForm[0]['budget'].value)){
            alert("Nilai anggaran tidak valid");
            $('#budget').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['benef_orang'].value)){
            alert("Jumlah orang penerima tidak valid");
            $('#benef_orang').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['benef_unit'].value)){
            alert("Jumlah unit penerima tidak valid");
            $('#benef_unit').focus();
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
                    s+= "<td align='center'>"+result['upload_info']['filetype']+"</td>";
                    s+= "<td align='center'><div class='icon-oknot' title='delete file' onclick='delete_docref("+result['upload_info']['id']+");'></div></td>";                    
                s+="</tr>";
                
                $('table#tbl_doc_references').append(s);
            }
            $('input#id').val(result['program_id']);
            $('p.error-message').text("Program berhasil di-update").hide().show('slow').delay(5000).hide('slow');
            
            bidang_active = $('select#type').val();
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
    function lookupPIC(target_obj, input_str)
    {
        target_obj.find('ul').empty();
        $.post('ajax',{input_function:'lookupPIC',param:input_str},function(result){
            data = jQuery.parseJSON(result);
            if (data['found']>0){
                var ul = target_obj.find('ul');
                for (var i in data['items']){
                    s="<li>"+data['items'][i]['pic']+"</li>";
                    target_obj.find('ul').append(s);
                }
            }
            $('ul.lookup li').click(function(){
                target_obj.parent().find('input').val($(this).text());
                target_obj.hide();
            })
            
            //set timer to hide this
            clearTimeout(pic_timer);
            pic_timer = setTimeout('hideLookup("lookup_pic")',4000);
        })
    }
    function lookupBeneficiaries(target_obj, input_str)
    {
        target_obj.find('ul').empty();
        $.post('ajax',{input_function:'lookupBeneficiaries',param:input_str},function(result){
            data = jQuery.parseJSON(result);
            if (data['found']>0){
                var ul = target_obj.find('ul');
                for (var i in data['items']){
                    s="<li>"+data['items'][i]['benef_name']+"</li>";
                    target_obj.find('ul').append(s);
                }
            }
            $('ul.lookup li').click(function(){
                var index = $(this).index();
                $('input#benef_name').val(data['items'][index]['benef_name']);
                $('input#benef_address').val(data['items'][index]['benef_address']);
                $('input#benef_phone').val(data['items'][index]['benef_phone']);
                $('input#benef_email').val(data['items'][index]['benef_email']);
                target_obj.hide();
            })
            
            //set timer to hide this
            clearTimeout(benef_timer);
            benef_timer = setTimeout('hideLookup("lookup_benef_name")',4000);
        })
    }
    function hideLookup(div_id)
    {
        if ($('div#'+div_id).css('display')!='none')
            $('div#'+div_id).hide();
    }
</script>
<style>
    div.lookup_bg {
        position: absolute;
        width: 300px;
        padding: 0 10px 5px 10px;
        border: solid 1px #ccc;
        border-top: none;
        background-color: #F1F1F1;
        display: none;
    }
    ul.lookup{
        padding: 0; margin: 0; list-style: none;
    }
    ul.lookup li{
        float: left;
        padding: 5px 10px 5px 10px;
        width: 300px;
        font-size: 12px;
        display: block;
        cursor: pointer;
    }
    ul.lookup li:hover{
        color: #01539C;
    }
</style>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>Program BL BRI - Update</h1>
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="savePrograms" />
                <input type="hidden" id="param" name="param" value="<?php echo $mode;?>" />
                <input type="hidden" id="id" name="id" value="<?php echo $id;?>" />                
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Nama Program</td>
                                <?php $name = (isset($data_result)?$data_result[0]['name']:'');?>
                                <td><input type="text" id="name" name="name" value="<?php echo $name;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Kelompok Program</td>
                                <?php $source = (isset($data_result)?$data_result[0]['source']:0);?>
                                <td>
                                    <select id="source" name="source">
                                        <option value="0" <?php if ($source==0) echo " selected";?>>BRI Perduli</option>
                                        <option value="1" <?php if ($source==1) echo " selected";?>>BUMN Perduli</option>
                                    </select>
                                </td>
                            </tr>                            
                            <tr>
                                <td class="title">Bidang BL</td>
                                <?php $type = (isset($data_result)?$data_result[0]['type']:1);?>
                                <td>
                                    <select id="type" name="type">
                                        <?php 
                                        $program_types = get_program_types($db_obj);
                                        if ($program_types)foreach($program_types as $item){
                                            echo "<option value='".$item['id']."'";
                                            if ($type==$item['id']) echo " selected";
                                            echo ">".$item['type']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Besar Anggaran</td>
                                <?php $budget = (isset($data_result)?$data_result[0]['budget']:0);?>
                                <?php $state = (isset($data_result)?$data_result[0]['state']:0);?>
                                <input type="hidden" id="state" name="state" value="<?php echo $state;?>" />
                                <td>
                                    <input type="hidden" id="budget_original" name="budget_original" value="<?php echo $budget;?>" />
                                    <?php 
                                    if ($state==1){
                                        if(userHasAccess($access, "BUDGET_APPROVED_EDIT")) {?>
                                            <input type="text" id="budget" name="budget" value="<?php echo $budget;?>" class="numeric" />
                                        <?php }else{?>
                                            <input type="hidden" id="budget" name="budget" value="<?php echo $budget;?>" />
                                            <input type="text" disabled value="<?php echo $budget;?>" class="numeric" />
                                        <?php }
                                    }else {?>
                                            <input type="text" id="budget" name="budget" value="<?php echo $budget;?>" class="numeric" />
                                    <?php }?>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Dana Operasional</td>
                                <?php $operational = (isset($data_result)?$data_result[0]['operational']:0);?>
                                <td>
                                    <input type="hidden" id="operational_original" name="operational_original" value="<?php echo $operational;?>" />
                                    <input type="text" id="operational" name="operational" value="<?php echo $operational;?>" class="numeric" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_02">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Person in Charge (PIC)</td>
                                <?php $pic = (isset($data_result)?$data_result[0]['pic']:'');?>
                                <td>
                                    <input type="text" class="lookup" id="pic" name="pic" value="<?php echo $pic;?>" />
                                    <div class="lookup_bg" id="lookup_pic"><ul class="lookup"></ul></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" colspan="2">Unit Kerja</td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Kantor Wilayah</td>                                
                                <?php 
                                $wilayah = (isset($data_result)?$data_result[0]['uker_wilayah']:0);
                                $wilayah_label = "";
                                ?>                                
                                <td>                                    
                                    <select id="kanwil" name="kanwil">
                                        <?php 
                                        $kanwil_options = load_kanwil($db_obj);
                                        foreach($kanwil_options as $item){
                                            echo "<option value='".$item['id']."' type='" .$item['tipe']."'" ;
                                            if ($wilayah==0){
                                                $wilayah = $item['id'];                                                
                                            }                                                
                                            if ($wilayah==$item['id'])
                                            {
                                                echo " selected='selected'";
                                                $wilayah_label = $item['uker'];
                                            }
                                            echo ">".$item['uker']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Kantor Cabang</td>
                                <?php $cabang = (isset($data_result)?$data_result[0]['uker_cabang']:0);?>
                                <td>
                                    <select id="kancab" name="kancab">
                                        <?php 
                                        //insert the wilayah it self
                                        echo "<option value='0'";
                                        if ($cabang==0) echo " selected";
                                        echo ">---".$wilayah_label." (KW)---</option>";
                                        
                                        $kancab_options = load_kancab_by_parent($wilayah, $db_obj);
                                        foreach($kancab_options as $item){
                                            echo "<option value='".$item['id']."'";
                                            if ($cabang>0&&$cabang==$item['id'])
                                                echo " selected";
                                            echo ">".$item['uker']."</option>";
                                        }                                        
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_03">
                        <table class="data-input">
                            <tr>
                                <td class="title" colspan="2">Penerima Manfaat</td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Nama Penerima</td>
                                <?php $benef_name = (isset($data_result)?$data_result[0]['benef_name']:'');?>
                                <td>
                                    <input type="text" class="lookup" id="benef_name" name="benef_name" value="<?php echo $benef_name;?>" maxlength="50" />
                                    <div class="lookup_bg" id="lookup_benef_name"><ul class="lookup"></ul></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Alamat Penerima</td>
                                <?php $benef_address = (isset($data_result)?$data_result[0]['benef_address']:'');?>
                                <td><input type="text" id="benef_address" name="benef_address" value="<?php echo $benef_address;?>" maxlength="254" /></td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Telepon Penerima</td>
                                <?php $benef_phone = (isset($data_result)?$data_result[0]['benef_phone']:'');?>
                                <td><input type="text" id="benef_phone" name="benef_phone" value="<?php echo $benef_phone;?>" maxlength="50" /></td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Email Penerima</td>
                                <?php $benef_email = (isset($data_result)?$data_result[0]['benef_email']:'');?>
                                <td><input type="text" id="benef_email" name="benef_email" value="<?php echo $benef_email;?>" maxlength="30" /></td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Jumlah Orang</td>
                                <?php $benef_orang = (isset($data_result)?$data_result[0]['benef_orang']:0);?>
                                <td><input type="text" id="benef_orang" name="benef_orang" value="<?php echo $benef_orang;?>" class="numeric" /></td>
                            </tr>
                            <tr>
                                <td class="title" style="text-indent: 20px;">Jumlah Unit</td>
                                <?php $benef_unit = (isset($data_result)?$data_result[0]['benef_unit']:0);?>
                                <td><input type="text" id="benef_unit" name="benef_unit" value="<?php echo $benef_unit;?>" class="numeric" /></td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_04">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Tanggal Pembuatan <em>(YYYY-mm-dd)</em></td>         
                                <?php $creation_date = (isset($data_result)?$data_result[0]['creation_date']:date("Y-m-d"));?>
                                <td colspan="3"><input type="text" class="datepicker" id="creation_date" name="creation_date" value="<?php echo $creation_date;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Nodin Putusan</td>         
                                <?php $nodin_putusan = (isset($data_result)?$data_result[0]['nodin_putusan']:'');?>
                                <td><input type="text" id="nodin_putusan" name="nodin_putusan" value="<?php echo $nodin_putusan;?>" /></td>
                                
                                <td class="title" width="250" align="right">Tanggal Putusan <em>(YYYY-mm-dd)</em></td>         
                                <?php $tgl_putusan = (isset($data_result)&&$state?$data_result[0]['tgl_putusan']:'');?>
                                <td><input type="text" class="datepicker" id="tgl_putusan" name="tgl_putusan" value="<?php echo $tgl_putusan;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Nomor Persetujuan</td>         
                                <?php $nomor_persetujuan = (isset($data_result)?$data_result[0]['nomor_persetujuan']:'');?>
                                <td><input type="text" id="nomor_persetujuan" name="nomor_persetujuan" value="<?php echo $nomor_persetujuan;?>" /></td>
                                
                                <td class="title" width="250" align="right">Tanggal Persetujuan <em>(YYYY-mm-dd)</em></td>         
                                <?php $approval_date = (isset($data_result)&&$state?$data_result[0]['approval_date']:'');?>
                                <td><input type="text" class="datepicker" id="approval_date" name="approval_date" value="<?php echo $approval_date;?>" <?php echo (!userHasAccess($access, "PROGRAM_APPROVE")?'disabled':''); ?> /></td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Nomor Register</td>         
                                <?php $nomor_registrasi = (isset($data_result)?$data_result[0]['nomor_registrasi']:'');?>
                                <td><input type="text" id="nomor_registrasi" name="nomor_registrasi" value="<?php echo $nomor_registrasi;?>" /></td>
                                
                                <td class="title" width="250" align="right">Tanggal Register <em>(YYYY-mm-dd)</em></td>         
                                <?php $tgl_register = (isset($data_result)&&$state?$data_result[0]['tgl_register']:'');?>
                                <td><input type="text" class="datepicker" id="tgl_register" name="tgl_register" value="<?php echo $tgl_register;?>"  /></td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Nomor BG</td>         
                                <?php $nomor_bg = (isset($data_result)?$data_result[0]['nomor_bg']:'');?>
                                <td colspan="3"><input type="text" id="nomor_bg" name="nomor_bg" value="<?php echo $nomor_bg;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Deskripsi Program</td>
                                <?php $description = (isset($data_result)?$data_result[0]['description']:'');?>
                                <td colspan="3"><textarea id="description" name="description" rows="8"><?php echo $description;?></textarea></td>
                            </tr>
                            <tr>
                                <td class="title">Potensi Bisnis</td>
                                <?php $potensi_bisnis = (isset($data_result)?$data_result[0]['potensi_bisnis']:'');?>
                                <td colspan="3"><textarea id="potensi_bisnis" name="potensi_bisnis" rows="8"><?php echo $potensi_bisnis;?></textarea></td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_05">
                        <table class="data-input">
                            <tr>
                                <td class="title">Dokumentasi / Referensi (doc,jpg,pdf) </td>                                
                                <td><input type="file" id="doc_reference" name="doc_reference" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table class="doc-references-list" id="tbl_doc_references">
                                        <tr>
                                            <th align='left'>Filename</th>
                                            <th width="80">Filetype</th>
                                            <th width="120">Date Upload</th>
                                            <th width="60">Action</th>
                                        </tr>
                                        <?php
                                        if (isset($data_result)){
                                            $id = $data_result[0]['id'];
                                            $sql = "SELECT id, filename, filetype, upload_date FROM doc_references
                                                    WHERE program=$id";
                                            $doc_references = $db_obj->execSQL($sql);
                                            if ($doc_references){
                                                foreach($doc_references as $item){
                                                    echo "<tr id='".$item['id']."'>";
                                                        echo "<td><a class='view-docref' onclick='openDocRef(\"".$item['filename']."\");'>".$item['filename']."</a></td>";
                                                        echo "<td align='center'>".get_label_docref_type($item['filetype'])."</td>";
                                                        echo "<td align='center'>".$item['upload_date']."</td>";
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
