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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo page_header();?>
<script language="javascript" type="text/javascript" src="customs/js/jquery.form.js"></script>
<script type="text/javascript">    
    var access_delete = "<?php echo (userHasAccess($access, "NEWS_DELETE")?1:0);?>";
    var access_delete_other = "<?php echo (userHasAccess($access, "NEWS_DELETE_OTHER")?1:0);?>";
    $(document).ready(function(){
        var options = { 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse,  // post-submit callback 
            type:           'post',
            dataType:       'json',
            resetForm: true
        }; 
 
        // bind form using 'ajaxForm' 
        $('form#frm_news').ajaxForm(options); 
        loadNews(0,'');
        
        $('li#btn_home').click(function(){
            window.location = "programs";
        })
        $('div#btn_search_content').click ( function ()
	{
            var keyword = $(this).parent().find('input').val();
            loadNews(0,keyword);
	});
	$("input#keyword").bind("keypress", function(event) 
	{
            if (event.which == '13'){
                $('div#btn_search_content').click();
            }			
	});
    })
    function loadNews(page,keyword)
    {        
        $('div#my-loader').show();
        $.post("ajax",{input_function:'loadNews',param:page,search_str:keyword},function(result){
            $('div#my-loader').hide();
            data = jQuery.parseJSON(result);
            //empty table
            $("ul.news-main").empty();
            if (data['found']>0){
                var s= drawComments(data, 0);
                $('ul.news-main').append(s);
                //create navigator buttons if needed
                createNavigator(page, data['pages'],keyword);
            }else{
                $('ul.navigation').empty();
            }
            
            $('ul.news-main li').hover(
                function(){
                    $(this).find('div.news-delete').show();
                },
                function(){
                    $(this).find('div.news-delete').hide();
                }
            )
        })
    }
    function drawComments(data_arr, parent_id){
        var s = '';
        if (parent_id>0)
            s+='<ul>';
        for(var i in  data_arr['parents'][parent_id])
        {
            var id = data_arr['parents'][parent_id][i];
            var data = data_arr['items'][id];
                    
            s+='<li id=\"block_'+data['id']+'\">';                
                s+="<div class='image-profile'>";
                    if (parent_id == 0)
                        s+="<img src='customs/profile/photo/"+data['avatar']+"' width='45' height='51' />";
                    else
                        s+="<img src='customs/profile/photo/"+data['avatar']+"' width='35' height='40' />";
                s+="</div>";
                s+="<div class='news-content'>";
                    if (parent_id == 0)
                        s+="<div class=\"link-comment\"><a class=\"comment-create\" onclick=\"showForm("+data['id']+");\");\">Tanggapi</a></div>";
                    s+="<p><span class=\"name\">"+data['news_by']+"</span><br />";
                    s+="<span class=\"date\">"+data['news_date']+"</span><br /></p>";
                    s+="<p class=\"text\">"+data['news_text']+"</p>";
                    if (data['view_by']!=data['news_by_id']){
                        if (access_delete_other=='1')
                            s+="<div class='news-delete' title='Hapus record ini' onclick='deleteNews("+data['id']+");'></div>";
                    }else if (access_delete=='1')
                        s+="<div class='news-delete' title='Hapus record ini' onclick='deleteNews("+data['id']+");'></div>";
                s+="</div>";
                if (parent_id == 0)
                    s+="<div class='form-place'></div>";
                if (objectLength(data_arr['parents'][data['id']])>0)
                        s+= drawComments(data_arr, data['id']);
            s+='</li>';
        }
        s+='</ul>';
            
        return s;
    }
    function showForm(parent){
        var form_window = $('div#form-window').detach();
        if (parent>0)
        {
            $('li#block_'+parent).find('div.form-place').append(form_window);
            $('div#form-window').show();
        }else{
            form_window.appendTo('div#initial-form');
            $('div#form-window').show();
        }
        
        //set input hidden parent
        $('input#param').val(parent);
        
        //set focus
        $('textarea#news_text').focus();
    }
    function createNavigator(page_active, num_of_pages, keyword)
    {
        //clear old navigation
        $('ul.navigation').empty();
        //only create navigation if num of pages > 1
        if (num_of_pages>1){
            for(var i;i<num_of_pages;i++){
                var s="<li onclick='loadNews("+i+",\""+keyword+"\");'>"+(i+1)+"</li>";
                $('ul.navigation').append(s);
            }            
        }
    }
    function showRequest(formData, jqForm, options) {
        if (jqForm[0]['news_text'].value==''){
            $('div#form-window').hide();
            return false; 
        }
        $('div#my-loader').show();
    }
    function showResponse(result)
    {
        $('div#my-loader').hide();
        if (result['success']==true){
            var s="";
            s+='<li id=\"block_'+result['items']['id']+'\">';
                s+="<div class='image-profile'>";
                    s+="<img src='customs/profile/photo/"+result['items']['avatar']+"' width='45' height='51' />";
                s+="</div>";
                s+="<div class='news-content'>";
                    if (result['items']['parent'] == 0)
                        s+="<div class=\"link-comment\"><a class=\"comment-create\" onclick=\"showForm("+result['items']['id']+");\");\">Tanggapi</a></div>";
                    s+="<p><span class=\"name\">"+result['items']['by']+"</span><br />";
                    s+="<span class=\"date\">"+result['items']['date']+"</span><br /></p>";
                    s+="<p class=\"text\">"+result['items']['text']+"</p>";
                    if (access_delete=='1'){
                        s+="<div class='news-delete' title='Hapus record ini' onclick='deleteNews("+result['items']['id']+");'></div>";
                    }
                s+="</div>";
                if (result['items']['parent'] == 0)
                    s+="<div class='form-place'></div>";
            s+='</li>';
                    
            if (result['items']['parent'] >0)
            {
                if ($('li#block_'+result['items']['parent']).find('ul').length==0)
                {
                    $('li#block_'+result['items']['parent']).append('<ul>'+s+'</ul>');                         
                }
                else
                {
                    $('li#block_'+result['items']['parent']).find('ul').prepend(s);
                }
            }
            else
                $('ul.news-main').prepend(s);
                    
            //$('ul.news-main').listView.refresh();
            
            $('ul.news-main li').hover(
                function(){
                    $(this).find('div.news-delete').show();
                },
                function(){
                    $(this).find('div.news-delete').hide();
                }
            )
        }
        if (result['error']!='') alert(result['error']);
    }
    function deleteNews(id)
    {
        if (confirm('Hapus record news ini ?\nSemua record yang berada di bawahnya akan ikut terhapus'))
        {
            $('div#my-loader').show();
            $.post("ajax",{input_function:'deleteNews',param:id},function(result){
                $('div#my-loader').hide();
                if (parseInt(result)==1){
                    $('li#block_'+id).remove();
                }else{
                    alert(result.substr(1));
                }
            })
        }
    }
</script>
</head>

<body>
    <!-- panel main buttons -->
    <?php echo document_header($access);?>
    
    <div id="panel-content">          
        <div class="news-window">
            <h1 style="margin-top: 5px;">Berbagi Berita &amp; Informasi</h1> 
            <div class="content">
                <a class="comment-create" onclick="showForm(0);">Tambah Info</a>
            </div>
            <div id="initial-form">
                <div id="form-window" class="news-form-container">
                    <form id="frm_news" name="frm_news" action="ajax" method="post">
                        <input type="hidden" id="input_function" name="input_function" value="saveNews" />
                        <input type="hidden" id="param" name="param" value="0" />
                        <table>
                            <tr>
                                <td>
                                    <textarea id="news_text" name="news_text"></textarea>
                                </td>
                                <td>
                                    <input type="submit" id="btn_submit" name="btn_submit" value="Kirim" />
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
            <div class="clr"></div>
            <div class="news-text-container" style="border-top: solid 2px #7aa3c9;">
                <ul class="news-main"></ul>
            </div>
            <div class="clr"></div>
            <div class="content">
                <ul class="navigation"></ul>
            </div>
        </div>        
    </div>
    <?php echo document_footer();?>
</body>
</html>