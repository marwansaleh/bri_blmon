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
if (!userHasAccess($access, "PROGRAM_EDIT"))
{
    header("location: uker");
    exit();
}
if (isset($qs[1])&&$qs[1]!='')
    $mode = sanitizeText ($qs[1]);

if ($mode==ACT_EDIT){
    if (isset($qs[2]))
    {
        $id = $qs[2];
        $sql = "SELECT id, parent, wilayah, cabang, kode, uker,
                tipe, alamat, kota, kabupaten, propinsi, telepon, fax
                FROM uker
                WHERE id=$id";
        $data_result = $db_obj->execSQL($sql);
    }
    else
    {
        $id = 0;
        $error_message = "Error. Mode edit namun id kegiatan tidak terdefinisi";
    }
}else{
    $id = 0;
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
        tabs = new Tabs({"tabs_01":"Info Utama","tabs_02":"Lokasi"}, "tabs-parent");
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
            window.location = "uker/"+$('select#kanwil').val();
        })
        $('input#btn_new').click(function(){
            window.location = "<?php echo cur_page_name(false);?>/<?php echo ACT_CREATE;?>";
        })
        $('select#tipe').change(function(){
            var tipe = $(this).val();
            //only load wilayah if tipe not KW
            showLoadData(tipe);            
        })
        $('select#kanwil').change(function(){
            var tipe = $('select#tipe').val();
            if (tipe=='KCK'||tipe=='KCP'||tipe=='KK'){
                loadKancab();
            }
        })
        $('select#propinsi').change(function(){
            var propinsi = $(this).val();
            //only load wilayah if tipe not KW
            loadKabupaten(propinsi);            
        })
    })
    function showLoadData(tipe)
    {
        if (tipe!='KW'&&tipe!='KP'){                
            loadKanwil(tipe);
        }else{
            var wilayah_ori = $('input#wilayah_ori').val();
            var cabang_ori = $('input#cabang_ori').val();
            $('select#kanwil').empty();
            $('select#kancab').empty(); 
            
            $('select#kanwil').append("<option value='"+wilayah_ori+"'>Not available</option>");
            $('select#kancab').append("<option value='"+cabang_ori+"'>Not available</option>");
        }
    }
    function loadKanwil(tipe)
    {
        $('select#kanwil').empty();
        $('select#kancab').empty();
        $.post("ajax",{input_function:'loadKanwil'},function(result){
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                for(var i in data['items']){
                    var s="<option value='"+data['items'][i]['wilayah']+"'>"+data['items'][i]['uker']+"</option>";
                    $('select#kanwil').append(s);
                }                                 
                if (tipe=='KCK'||tipe=='KCP'||tipe=='KK'){
                    loadKancab();
                }
                else{
                    var cabang_ori = $('input#cabang_ori').val();
                    $('select#kancab').append("<option value='"+cabang_ori+"'>Not available</option>");
                }  
            }else{
                alert('Tidak ada data wilayah di database');
            }
        })
         
    }
    function loadKancab()
    {
        $('select#kancab').empty();
        $.post("ajax",{input_function:'loadKancab',param:$('select#kanwil').val()},function(result){
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                for(var i in data['items']){
                    var s= "<option value='"+data['items'][i]['id']+"'>"+data['items'][i]['uker']+"</option>";
                    $('select#kancab').append(s);
                }
            }
        })
    }
    function loadKabupaten(propinsi)
    {
        $('select#kota').empty();
        $.post("ajax",{input_function:'loadKabupaten',param:propinsi},function(result){
            var data = jQuery.parseJSON(result);
            if (data['found']>0){
                for(var i in data['items']){
                    var s= "<option value='"+data['items'][i]['id']+"' title='"+data['items'][i]['kabupaten']+"'>"+data['items'][i]['ibukota']+"</option>";
                    $('select#kota').append(s);
                }
            }
        })
    }
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['uker'].value==''){
            alert("Nama unit kerja tidak boleh kosong")
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){
            $('input#param').val("<?php echo ACT_EDIT;?>");
            $('input#id').val(result['id']);
            $('p.error-message').text("Uker berhasil di-update").hide().show('slow').delay(5000).hide('slow');
            
            wilayah_active = $('select#kanwil').val();
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
            <h1 id="page-title">Unit Kerja BRI Selindo</h1>
            <p class="error-message"><?php echo (isset($error_message)?$error_message:'');?></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="saveUker" />
                <input type="hidden" id="param" name="param" value="<?php echo $mode;?>" />
                <input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title">Jenis Uker</td>
                                <?php $tipe = (isset($data_result)?$data_result[0]['tipe']:'KW');?>
                                <?php $types= load_uker_types($db_obj);?>
                                <td>
                                    <select id="tipe" name="tipe">
                                        <?php if ($types)foreach($types as $item){
                                           echo "<option value='".$item['type']."'";
                                           if ($tipe==$item['type']) echo " selected";
                                           echo ">".$item['caption']."</option>";
                                        }?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Kantor Wilayah</td>
                                <?php $wilayah = (isset($data_result)?$data_result[0]['wilayah']:0);?>
                                <td>
                                    <input type="hidden" id="wilayah_ori" name="wilayah_ori" value="<?php echo $wilayah;?>" />
                                    <select id="kanwil" name="kanwil">
                                        <?php
                                        if ($tipe!='KW'){
                                            $kanwil_options = load_kanwil($db_obj);
                                            foreach($kanwil_options as $item){
                                                echo "<option value='".$item['wilayah']."'";
                                                if ($wilayah>0){
                                                    if ($wilayah==$item['wilayah'])
                                                    echo " selected";
                                                }else
                                                {
                                                    $wilayah = $item['wilayah'];
                                                    echo " selected";
                                                }
                                                echo ">".$item['uker']."</option>";
                                            }
                                        }else{
                                            echo "<option value='".$wilayah."'>Not available</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Kantor Cabang</td>
                                <?php $cabang = (isset($data_result)?$data_result[0]['cabang']:0);?>
                                <td>
                                    <input type="hidden" id="cabang_ori" name="cabang_ori" value="<?php echo $cabang;?>" />
                                    <select id="kancab" name="kancab">
                                        <?php 
                                        if ($tipe!='KW'&&$tipe!='KANINS'&&$tipe!='KC')
                                        {
                                            $kancab_options = load_kancab_by_parent($wilayah, $db_obj);
                                            foreach($kancab_options as $item){
                                                echo "<option value='".$item['cabang']."'";
                                                if ($cabang>0&&$cabang==$item['cabang'])
                                                    echo " selected";
                                                echo ">".$item['uker']."</option>";
                                            }
                                        }else{
                                            echo "<option value='".$cabang."'>Not available</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Nama Unit Kerja</td>
                                <?php $uker = (isset($data_result)?$data_result[0]['uker']:'');?>
                                <td><input type="text" id="uker" name="uker" value="<?php echo $uker;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Kode Uker</td>
                                <?php $kode = (isset($data_result)?$data_result[0]['kode']:'');?>
                                <td><input type="text" id="kode" name="kode" value="<?php echo $kode;?>" /></td>
                            </tr>                            
                        </table>
                    </div>
                    
                    <div class="tabs-each" id="tabs_02">
                        <table class="data-input">                            
                            <tr>
                                <td class="title">Propinsi</td>
                                <?php $propinsi = (isset($data_result)?$data_result[0]['propinsi']:0);?>
                                <td>
                                    <select id="propinsi" name="propinsi">
                                        <?php 
                                        $provinces = load_propinsi($db_obj);
                                        if ($provinces){
                                            foreach($provinces as $item){
                                                echo "<option value='".$item['id']."'";
                                                if ($propinsi==0) $propinsi=$item['id'];
                                                if ($propinsi==$item['id'])
                                                    echo " selected";
                                                echo ">".$item['propinsi']."</option>";
                                            }
                                        }else{
                                            echo "<option value='NULL'>Not Available</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">Kota</td>
                                <?php $kota = (isset($data_result)?$data_result[0]['kabupaten']:'');?>
                                <td>
                                    <select id="kota" name="kota">
                                        <?php 
                                        $kabupaten = load_kabupaten($propinsi,$db_obj);
                                        if ($propinsi>0&&$kabupaten){
                                            foreach($kabupaten as $item){
                                                echo "<option value='".$item['id']."'";
                                                if ($kota==$item['id'])
                                                    echo " selected";
                                                echo ">".$item['ibukota']."</option>";
                                            }
                                        }else{
                                            echo "<option value='NULL'>Not Available</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" width="250">Alamat Uker</td>    
                                <?php $alamat = (isset($data_result)?$data_result[0]['alamat']:'');?>
                                <td><textarea id="alamat" name="alamat"><?php echo $alamat;?></textarea></td>
                            </tr>
                            <tr>
                                <td class="title">Telepon</td>
                                <?php $telepon = (isset($data_result)?$data_result[0]['telepon']:'');?>
                                <td><input type="text" id="telepon" name="telepon" value="<?php echo $telepon;?>" /></td>
                            </tr>
                            <tr>
                                <td class="title">Fax</td>
                                <?php $fax = (isset($data_result)?$data_result[0]['fax']:'');?>
                                <td><input type="text" id="fax" name="fax" value="<?php echo $fax;?>" /></td>
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