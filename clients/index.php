<?php 
require_once("./../funcs/database.class.php"); 
require_once("./../funcs/functions.php"); 
require_once("./../funcs/constant.php"); 

check_login("index");

$db_obj = new DatabaseConnection();
//load user access
$access = loadUserAccess($db_obj);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script type="text/javascript">
    $(document).ready(function(){
        $('div.slider-btn-up').click(function(){
            var container = $(this).parent().find('div.slider-container');
            var content = container.find('div.slider-content');
            
            var container_height = container.height();
            var content_height = content.height();
            var max_diff = content_height-container_height;
            var move = container_height;
                        
            var current_top = parseInt(content.css('margin-top'));
            if (current_top<0&&max_diff>0){
                if ((current_top+move) < 0){
                    margin_top = current_top+move;
                }else{
                    margin_top = 0;
                }
                content.animate({marginTop: margin_top},1000);
            }
        })
        $('div.slider-btn-down').click(function(){
            var container = $(this).parent().find('div.slider-container');
            var content = container.find('div.slider-content');            
            
            var container_height = container.height();
            var content_height = content.height();
            var max_diff = content_height-container_height;
            var move = container_height;
                        
            var current_top = parseInt(content.css('margin-top'));
            if (Math.abs(current_top)>=0&&max_diff>0){
                if (Math.abs(current_top-move) < max_diff){
                    margin_top = current_top-move;
                }else{
                    margin_top = -(max_diff);
                }
                content.animate({marginTop: margin_top},1000);
            }
        })
    })
    function programView(program_id)
    {
        var wnd = window.open("program_view/"+program_id,"ProgramDetail","width=700,scrollbars=1");
        wnd.focus();
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">
    	<!-- profile user-->
    	<div class="box-container" id="dash-profile">
            <div class="dashboard-title"><a href='profile'>PROFIL USER</a></div>
            <div class="dashboard-content">
            	<table  class="data-dashboard">
                	<tr>
                    	<td valign="top" align="left" width="70">
                            <img src="<?php echo PROFILE_URL. get_user_info("AVATAR");?>" width="70" height="80" alt="Photo profil"
                            	style="border-right:solid 2px #ccc; border-bottom:solid 2px #ccc;" />
                        </td>
                        <td valign="top" align="right" style="padding-left:15px;">
                            <table  class="data-dashboard">
                            	<tr>
                                    <td>Username:</td>
                                    <td><strong><?php echo get_user_info("USERNAME");?></strong></td>
                                </tr>
                                <tr>
                                    <td>Nama lengkap:</td>
                                    <td><strong><?php echo get_user_info("FULLNAME");?></strong></td>
                                </tr>
                                <tr>
                                    <td>Posisi:</td>
                                    <td><strong><?php echo get_user_info("POSITION");?></strong></td>
                                </tr>
                                
                            </table>
                        </td>
                    
                    <?php if (userHasAccess($access, "PROGRAM_APPROVE")){?>
                    <tr>
                        <td colspan="2">
                            Total program yang ada: <strong><?php echo count_programs(0,3,$db_obj);?> buah</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Total program belum disetujui: <strong><?php echo count_programs(0,0,$db_obj);?> buah</strong>
                        </td>
                    </tr>
                    <?php }else{?>
                    <tr>
                        <td colspan="2">
                            Program yang dibuat: <strong><?php echo count_programs(get_user_info("ID"),3,$db_obj);?> buah</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Program yang disetujui: <strong><?php echo count_programs(get_user_info("ID"),1,$db_obj);?> buah</strong>
                        </td>
                    </tr>
                    <?php }
                    $users_online = get_user_loggedin(2,true,$db_obj);
                    ?>
                    <tr><td colspan="2"><strong><em>Users loggedin (<?php echo count($users_online);?> users)</em></strong></td></tr>
                    <tr>
                    	<td colspan="2">
                            <div class="slider-btn-up"></div>
                            <div class="slider-container" style="height: 50px;">
                                <div class="slider-content">
                                    <ul style="margin:0; padding:0 0 0 10px;list-style: none;">
                                    <?php                                    
                                    if ($users_online){
                                        foreach($users_online as $user){
                                            echo "<li style='float:left; padding:5px 0 5px 0;'><img align='middle' src='".PROFILE_URL.$user['avatar']."' width='30' height='34' />&nbsp;".$user['full_name']. "(login: ".$user['last_login'].")</li>";
                                        }
                                    }
                                    ?>
                                    </ul>
                                </div>
                            </div>    
                            <div class="slider-btn-down"></div>
                        </td>
                    </tr>
                </table>
            </div>            
        </div>
        <!-- Posisi saldo alokasi-->
        <div class="box-container">
            <div class="dashboard-title">SALDO &amp; ALOKASI</div>
            <div class="dashboard-content">
                <?php
                //get initial saldo first
                $sql = "SELECT trans_desc, DATE(trans_date) as trans_date, trans_debet, trans_credit
                        FROM saldo WHERE (trans_desc LIKE '%initial%')";
                $initial_saldo = $db_obj->execSQL($sql);
                $sql = "SELECT trans_desc, DATE(trans_date) as trans_date, trans_debet, trans_credit
                        FROM saldo WHERE (trans_desc NOT LIKE '%initial%')
                        ORDER BY trans_date DESC
                        LIMIT 50";
                $saldo_history = $db_obj->execSQL($sql);
                
                if ($saldo_history){
                    if ($initial_saldo)array_unshift($saldo_history, $initial_saldo[0]);
                ?>
                <div class="slider-btn-up"></div>
                <div class="slider-container" style="height: 200px;">                    
                    <div class="slider-content">
                        <table class="data-dashboard">
                            <tr>
                                <th>TANGGAL</th>
                                <th>TRANSAKSI</th>
                                <th>NILAI</th>
                            </tr>
                            <?php foreach($saldo_history as $item)
                            {
                            echo "<tr title='".$item['trans_desc']."'>";
                                    echo "<td>".$item['trans_date']."</td>";
                                    echo "<td>".(strlen($item['trans_desc'])>16?substr($item['trans_desc'], 0, 12)."...":$item['trans_desc'])."</td>";
                                    if ($item['trans_debet']>0){
                                        echo "<td align='right'>-".number_format($item['trans_debet'],2,",",".")."</td>";
                                    }else{
                                        echo "<td align='right'>".number_format($item['trans_credit'],2,",",".")."</td>";
                                    }
                            echo "</tr>";
                            }?>                    
                        </table>
                    </div>
                </div>
                <div class="slider-btn-down"></div>
                <?php }?>
                <table class="data-dashboard">
                    <tr>
                        <td><strong>ALOKASI:</strong></td>
                        <td align="right"><strong><?php echo number_format(get_last_used_money("", SALDO_ALOCATION, $db_obj),2,",",".");?></strong></td>
                    </tr>
                    <tr>
                        <td><strong>SALDO:</strong></td>
                        <td align="right"><strong><?php echo number_format(get_last_saldo("", SALDO_ALOCATION, $db_obj),2,",",".");?></strong></td>
                    </tr>
                </table> 
            </div>
        </div>
        <!-- Posisi saldo realisasi-->
        <div class="box-container">
            <div class="dashboard-title">SALDO &amp; REALISASI</div>
            <div class="dashboard-content">
                <?php
                //get initial saldo first
                $sql = "SELECT trans_desc, DATE(trans_date) as trans_date, trans_debet, trans_credit
                        FROM saldo_real WHERE (trans_desc LIKE '%initial%')";
                $initial_saldo = $db_obj->execSQL($sql);
                
                $sql = "SELECT trans_desc, DATE(trans_date) as trans_date, trans_debet, trans_credit
                        FROM saldo_real WHERE (trans_desc NOT LIKE '%initial%')
                        ORDER BY trans_date DESC
                        LIMIT 50";
                $saldo_history = $db_obj->execSQL($sql);
                if ($saldo_history){
                    if ($initial_saldo)array_unshift($saldo_history, $initial_saldo[0]);
                ?>
                <div class="slider-btn-up"></div>
                <div class="slider-container" style="height: 200px;">                    
                    <div class="slider-content">
                        <table class="data-dashboard">
                            <tr>
                                <th>TANGGAL</th>
                                <th>TRANSAKSI</th>
                                <th>NILAI</th>
                            </tr>
                            <?php foreach($saldo_history as $item)
                            {
                            echo "<tr title='".$item['trans_desc']."'>";
                                    echo "<td>".$item['trans_date']."</td>";
                                    echo "<td>".(strlen($item['trans_desc'])>16?substr($item['trans_desc'], 0, 12)."...":$item['trans_desc'])."</td>";
                                    if ($item['trans_debet']>0){
                                        echo "<td align='right'>-".number_format($item['trans_debet'],2,",",".")."</td>";
                                    }else{
                                        echo "<td align='right'>".number_format($item['trans_credit'],2,",",".")."</td>";
                                    }
                            echo "</tr>";
                            }?>
                        </table>
                    </div>
                </div>
                <div class="slider-btn-down"></div>
                <?php }?>
                <table class="data-dashboard">
                    <tr>
                        <td colspan="2"><strong>REALISASI:</strong></td>
                        <td align="right"><strong><?php echo number_format(get_last_used_money("", SALDO_REAL,$db_obj),2,",",".");?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>SALDO:</strong></td>
                        <td align="right"><strong><?php echo number_format(get_last_saldo("", SALDO_REAL, $db_obj),2,",",".");?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- Progress program -->
        <div class="box-container">
            <div class="dashboard-title"><a href='programs'>PERKEMBANGAN PROGRAM</a></div>
            <div class="dashboard-content">
            	<?php
                $sql = "SELECT id,name FROM programs 
                        WHERE state=1
                        ORDER BY creation_date DESC
                        LIMIT 20";
                $programs = $db_obj->execSQL($sql);
                if ($programs){
                ?>
                <div class="slider-btn-up"></div>
                <div class="slider-container" style="height: 270px;">                    
                    <div class="slider-content">
                        <table class="data-dashboard">
                            <tr>
                                <th align="left">NAMA PROGRAM</th>
                                <th>PROGRESS</th>
                            </tr>
                            <?php foreach($programs as $item)
                            {
                            echo "<tr title='".$item['name']."'>";
                                    echo "<td><a href='#' onclick='programView(".$item['id'].");'>".(strlen($item['name'])>30?substr($item['name'], 0, 26)."...":$item['name'])."</a></td>";
                                    echo "<td align='right'>".number_format(program_progress($item['id'], $db_obj),2,",",".")."%</td>";
                            echo "</tr>";
                            }?>
                        </table>
                    </div>                    
                </div>
                <div class="slider-btn-down"></div>
                <?php }?>                 
            </div>
        </div>
        <!-- Daftar program per wilayah and budget-->
        <div class="box-container">
            <div class="dashboard-title"><a href='areas'>PROGRAM PER WILAYAH</a></div>
            <div class="dashboard-content">
                <div class="slider-btn-up"></div>
                <div class="slider-container" style="height: 270px;">                    
                    <div class="slider-content">
                        <table class="data-dashboard">
                            <tr>
                                <th align="left">WILAYAH</th>
                                <th>Orang</th>
                                <th>Unit</th>
                                <th align="right">BUDGET</th>
                            </tr>
                            <?php
                            $sql = "SELECT SUM( p.budget ) AS budget, SUM( p.benef_orang ) AS benef_orang, 
                                    SUM(p.benef_unit) AS benef_unit, u.uker, p.uker_wilayah
                                    FROM programs p, uker u
                                    WHERE (
                                        (p.uker_wilayah = u.id)AND(p.state=1)
                                    )
                                    GROUP BY p.uker_wilayah";
                            $result = $db_obj->execSQL($sql);
                            $total_budget = 0;
                            $total_benef_orang = 0;
                            $total_benef_unit = 0;
                            if ($result)foreach($result as $item){
                                echo "<tr>";
                                    echo "<td><a href=\"areas/".$item['uker_wilayah']."\">".$item['uker']."</a></td>";
                                    echo "<td align='right'>".number_format($item['benef_orang'],0,",",".")."</td>";
                                    echo "<td align='right'>".number_format($item['benef_unit'],0,",",".")."</td>";
                                    echo "<td align='right'>".number_format($item['budget'],0,",",".")."</td>";
                                echo "</tr>";
                                $total_budget+=$item['budget'];
                                $total_benef_orang+=$item['benef_orang'];
                                $total_benef_unit+=$item['benef_unit'];
                            }
                            echo "<tr>";
                                echo "<td><strong>TOTAL</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_benef_orang,0,",",".")."</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_benef_unit,0,",",".")."</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_budget,0,",",".")."</strong></td>";
                            echo "</tr>";
                            ?>
                        </table> 
                    </div>                    
                </div>
                <div class="slider-btn-down"></div>
            </div>
        </div>
        <!-- Daftar program per propinsi and budget-->
        <div class="box-container">
            <div class="dashboard-title"><a href='propinsi'>PROGRAM PER PROPINSI</a></div>
            <div class="dashboard-content">
                <div class="slider-btn-up"></div>
                <div class="slider-container" style="height: 270px;">                    
                    <div class="slider-content">
                        <table class="data-dashboard">
                            <tr>
                                <th align="left">PROPINSI</th>
                                <th>Orang</th>
                                <th>Unit</th>
                                <th align="right">BUDGET</th>
                            </tr>
                            <?php
                            $sql = "SELECT SUM( p.budget ) AS budget, SUM( p.benef_orang ) AS benef_orang, 
                                    SUM(p.benef_unit) AS benef_unit, u.uker, p.uker_wilayah, pr.propinsi, 
                                    u.propinsi as propinsi_id
                                    FROM programs p, uker u, propinsi pr
                                    WHERE (
                                        (p.uker_wilayah = u.id)AND(u.propinsi=pr.id)AND(p.state=1)
                                    )
                                    GROUP BY pr.propinsi";
                            $result = $db_obj->execSQL($sql);
                            $total_budget = 0;
                            $total_benef_orang = 0;
                            $total_benef_unit = 0;
                            if ($result)foreach($result as $item){
                                echo "<tr>";
                                    echo "<td><a href=\"propinsi/".$item['propinsi_id']."\">".$item['propinsi']."</a></td>";
                                    echo "<td align='right'>".number_format($item['benef_orang'],0,",",".")."</td>";
                                    echo "<td align='right'>".number_format($item['benef_unit'],0,",",".")."</td>";
                                    echo "<td align='right'>".number_format($item['budget'],0,",",".")."</td>";
                                echo "</tr>";
                                $total_budget+=$item['budget'];
                                $total_benef_orang+=$item['benef_orang'];
                                $total_benef_unit+=$item['benef_unit'];
                            }
                            echo "<tr>";
                                echo "<td><strong>TOTAL</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_benef_orang,0,",",".")."</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_benef_unit,0,",",".")."</strong></td>";
                                echo "<td align='right'><strong>".number_format($total_budget,0,",",".")."</strong></td>";
                            echo "</tr>";
                            ?>
                        </table>
                    </div>
                </div>
                <div class="slider-btn-down"></div>
            </div>
        </div>
    </div>
    <?php echo document_footer();?>
</body>
</html>