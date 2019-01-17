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
if (isset($qs[1])&&$qs[1]!='')
{
    $id = sanitizeText ($qs[1]);
    $sql = "SELECT ra.tahun, ra.component, ra.triwulan_1, ra.triwulan_2, 
            ra.triwulan_3, ra.triwulan_4, ra.real_1, ra.real_2, real_3, ra.real_4,
            co.tag, co.tag_value
            FROM rkap ra, rkap_component co 
            WHERE (ra.id=$id)AND(ra.component=co.id)";
    $data_result = $db_obj->execSQL($sql);
}else{
    $id = 0;
}
function get_rkap_year(DatabaseConnection $db_obj=NULL)
{
    if (!$db_obj) $db_obj = new DatabaseConnection();
    //get any year from existing data
    $sql = "SELECT DISTINCT tahun FROM rkap ORDER BY tahun";
    $year_result = $db_obj->execSQL($sql);
    //check if current year exist in the array
    $cur_year = date("Y");
    if ($year_result)
    {
        $year_options  = array();
        foreach($year_result as $year)
            $year_options[] = $year['tahun'];
        
        if (!in_array($cur_year, $year_options))
            $year_options[] = $cur_year;
        if (!in_array($cur_year-1, $year_options))
            $year_options[] = $cur_year-1;
        if (!in_array($cur_year+1, $year_options))
            $year_options[] = $cur_year+1;
                                    
        //sort the year options
        sort($year_options);
    }else{
        $year_options = array($cur_year-1,$cur_year,$cur_year+1);
    }
    
    return $year_options;
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
        tabs = new Tabs({"tabs_01":"RKAP","tabs_02":"REALISASI"}, "tabs-parent");
        tabs.activePage = 0;
        tabs.init();
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback 
            type:           'post',
            dataType:       'json'
            //resetForm: true        // reset the form after successful submit 

            // $.ajax options can be used here too, for example: 
            //timeout:   3000 
        }; 
 
        // bind form using 'ajaxForm' 
        $('form#frm_update').ajaxForm(options); 
    
        $('input#btn_close').click(function(){
            window.location = "rkap";
        })
        $('input#btn_new').click(function(){
            window.location = "rkap_update";
        })
        $('select#component').change(function(){
            var show_calculate_button = ($('option:selected', this).attr('tag')=='2'||$('option:selected', this).attr('tag')=='4');
            $('input.calculate').each(function(){
                if (show_calculate_button)
                    $(this).show();
                else
                    $(this).hide();
            })
        })
        $('input.calculate').click(function(){
            var triwulan = $(this).attr('triwulan');
            var tag = $(this).attr('tag');
            var tag_value = $(this).attr('tag_value');
            var target_val = $(this).parent().find('input[type=text]');
            
            $('div#my-loader').show();
            switch(tag){
                case '2':
                $.post("ajax",{input_function:'getProgramRealisationByTriwulan',param:$('select#tahun').val(),source:tag_value,triwulan:triwulan},function(result){
                    $('div#my-loader').hide();
                    target_val.val(result);
                }); break;
                case '4':
                $.post("ajax",{input_function:'getBenefTriwulan',param:$('select#tahun').val(),source:tag_value,triwulan:triwulan},function(result){
                    $('div#my-loader').hide();
                    target_val.val(result);
                }); break;
            }
        })
    })
    function showRequest(formData, jqForm, options) {
        //check if numeric field is valid
        if (!isNumber(jqForm[0]['triwulan_1'].value)){
            alert("Nilai Triwulan I tidak valid");
            tabs.setActivePage(0);
            $('input#triwulan_1').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['triwulan_2'].value)){
            alert("Nilai Triwulan II tidak valid");
            tabs.setActivePage(0);
            $('input#triwulan_2').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['triwulan_3'].value)){
            alert("Nilai Triwulan III tidak valid");
            tabs.setActivePage(0);
            $('input#triwulan_3').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['triwulan_4'].value)){
            alert("Nilai Triwulan IV tidak valid");
            tabs.setActivePage(0);
            $('input#triwulan_4').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['real_1'].value)){
            alert("Nilai Realisasi Triwulan I tidak valid");
            tabs.setActivePage(1);
            $('input#real_1').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['real_2'].value)){
            alert("Nilai Realisasi Triwulan II tidak valid");
            tabs.setActivePage(1);
            $('input#real_2').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['real_3'].value)){
            alert("Nilai Realisasi Triwulan III tidak valid");
            tabs.setActivePage(1);
            $('input#real_3').focus();
            return false; 
        }
        if (!isNumber(jqForm[0]['real_4'].value)){
            alert("Nilai Realisasi Triwulan IV tidak valid");
            tabs.setActivePage(1);
            $('input#real_4').focus();
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){
            $('input#param').val(result['id']);
            $('p.error-message').text("Item RKAP berhasil di-update").hide().show('slow').delay(5000).hide('slow');
        }
        if (result['error']!='') {            
            $('p.error-message').text('Error!'+result['error']).hide().show('slow').delay(5000).hide('slow');
            alert(result['error']);
        }
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
        <div class="content">
            <h1>RKAP - Update</h1>
            <p class="error-message"></p>
        </div>   
        <div class="clr"></div>
        <div class="content">
            <form id="frm_update" name="frm_update" enctype="multipart/form-data" method="post" action="ajax">
                <input type="hidden" id="input_function" name="input_function" value="saveRKAP" />
                <input type="hidden" id="param" name="param" value="<?php echo $id;?>" /> 
                <div class="tabs-container" id="tabs-parent">
                    <div class="tabs-each" id="tabs_01">
                        <table class="data-input">
                            <tr>
                                <td class="title" width="250">Tahun Anggaran</td>
                                <td>
                                    <select id="tahun" name="tahun" dir="rtl">
                                        <?php
                                        $cur_year = (isset($data_result)?$data_result[0]['tahun']:date("Y"));
                                        //get any year from existing data
                                        $year_options = get_rkap_year($db_obj);
                                        foreach($year_options as $year){
                                            echo "<option value='".$year."'";
                                            if ($cur_year==$year) echo ' selected';
                                            echo ">".$year."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="title">Pilih Jenis Komponen</td>
                                <td>
                                    <select id="component" name="component">                                
                                        <?php
                                        $init_val = (isset($data_result)?$data_result[0]['component']:1);
                                        $sql = "SELECT co.id, co.category, co.caption, ca.caption as cat_caption,
                                                co.tag, co.tag_value 
                                                FROM rkap_component as co, rkap_category as ca
                                                WHERE (co.category=ca.id)
                                                ORDER BY ca.sort, co.sort";
                                        $comp_options = $db_obj->execSQL($sql);
                                        if ($comp_options){
                                            $category_check = 0;
                                            foreach($comp_options as $component)
                                            {
                                                if ($category_check!=$component['category'])
                                                {
                                                    echo "<option value='".$component['id']."'><strong>".$component['cat_caption']."</strong></option>";                                            
                                                    $category_check = $component['category'];
                                                }
                                                echo "<option value='".$component['id']."' tag='".$component['tag']."' tag_value='".$component['tag_value']."'";
                                                if ($init_val==$component['id']) echo ' selected';
                                                echo ">----- ".$component['caption']."</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="title" colspan="2">Besaran Nilai per Triwulan</td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['triwulan_1']:0); ?>
                                <td class="title" style="text-indent: 20px;">Triwulan I</td>
                                <td>                            
                                    <input type="text" id="triwulan_1" name="triwulan_1" value="<?php echo $init_val;?>" class="numeric" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['triwulan_2']:0); ?>
                                <td class="title" style="text-indent: 20px;">Triwulan II</td>
                                <td>                            
                                    <input type="text" id="triwulan_2" name="triwulan_2" value="<?php echo $init_val;?>" class="numeric" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['triwulan_3']:0); ?>
                                <td class="title" style="text-indent: 20px;">Triwulan III</td>
                                <td>                            
                                    <input type="text" id="triwulan_3" name="triwulan_3" value="<?php echo $init_val;?>" class="numeric" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['triwulan_4']:0); ?>
                                <td class="title" style="text-indent: 20px;">Triwulan IV</td>
                                <td>                            
                                    <input type="text" id="triwulan_4" name="triwulan_4" value="<?php echo $init_val;?>" class="numeric" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="tabs-each" id="tabs_02">
                        <?php 
                        $calculate_style = (isset($data_result)&&($data_result[0]['tag']==2||$data_result[0]['tag']==4)?'':'display:none;'); 
                        $tag = (isset($data_result)?$data_result[0]['tag']:0);
                        $tag_value = (isset($data_result)?$data_result[0]['tag_value']:0);
                        ?>
                        <table class="data-input">
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['real_1']:0); ?>
                                <td class="title" width="250">Triwulan I</td>
                                <td>
                                    <input type="text" id="real_1" name="real_1" value="<?php echo $init_val;?>" class="numeric" />
                                    <input type="button" class="calculate" triwulan="1" value="Calculate" style="<?php echo $calculate_style;?>" tag="<?php echo $tag;?>" tag_value="<?php echo $tag_value;?>" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['real_2']:0); ?>
                                <td class="title" width="250">Triwulan II</td>
                                <td>
                                    <input type="text" id="real_2" name="real_2" value="<?php echo $init_val;?>" class="numeric" />
                                    <input type="button" class="calculate" triwulan="2" value="Calculate" style="<?php echo $calculate_style;?>" tag="<?php echo $tag;?>" tag_value="<?php echo $tag_value;?>" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['real_3']:0); ?>
                                <td class="title" width="250">Triwulan III</td>
                                <td>
                                    <input type="text" id="real_3" name="real_3" value="<?php echo $init_val;?>" class="numeric" />
                                    <input type="button" class="calculate" triwulan="3" value="Calculate" style="<?php echo $calculate_style;?>" tag="<?php echo $tag;?>" tag_value="<?php echo $tag_value;?>" />
                                </td>
                            </tr>
                            <tr>
                                <?php $init_val = (isset($data_result)?$data_result[0]['real_4']:0); ?>
                                <td class="title" width="250">Triwulan IV</td>
                                <td>
                                    <input type="text" id="real_4" name="real_4" value="<?php echo $init_val;?>" class="numeric" />
                                    <input type="button" class="calculate" triwulan="4" value="Calculate" style="<?php echo $calculate_style;?>" tag="<?php echo $tag;?>" tag_value="<?php echo $tag_value;?>" />
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