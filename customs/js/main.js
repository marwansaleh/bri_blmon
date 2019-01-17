// JavaScript Document
$(document).ready( function () {  
    if ($('div#panel-content').length!=0){
        var window_h = $(window).height();
        $('div#panel-content').css('min-height',window_h+'px');
    }
    $('li.head-link').click(function(){
        $('li.head-link').removeClass("active");
        if ($('div.submenu',this).css('display')!='none'){
            //hide all submenu
            $('div.submenu.submenu').hide();
        }else{
            //hide all submenu
            $('div.submenu').hide();
            //show submenu in this head-link
            $(this).addClass("active");
            $('div.submenu',this).show();
        }
    })
        
    //create loader
    create_loader('my-loader');
});
Number.prototype.formatMoney = function(c, d, t){
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};
function get_current_date(){
    var tanggal = new Date();
    
    var tahun = tanggal.getFullYear();
    
    var bulan = tanggal.getMonth()+1;
    if (bulan<10) bulan = '0'+bulan;    
    
    var hari = tanggal.getDate();
    if(hari<10) hari = '0'+hari;
    
    var s = tahun+"-"+bulan+"-"+hari;
    return s;
}
function getIndonesiaMonth(month_num)
{
    var array_month = new Array("Januari","Pebruari","Maret","April","Mei","Juni",
                                "Juli","Agustus","September","Oktober","Nopember","Desember");
    
    if (month_num===undefined){
        var date = new Date();
        month_num = date.getMonth();
    }else{
        month_num-=1;
    }
    
    return array_month[month_num];
}
function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}
function create_loader(id)
{
    if ($('div#'+id).length==0){
        //create loader-bg
        var s="<div id='"+id+"' class='loader-bg'></div>";
    
        $('body').append(s);
        
        //set width & height
        var window_w = $(window).width();
        var window_h = $(window).height();
        
        
        $('div#'+id).css('width',window_w+'px');
        $('div#'+id).css('height',window_h+'px');
        
        //append the loader
        $('div#'+id).append("<div id='loader-circle' class='loader'></div>");
        //set margin to put in the center
        $('div#loader-circle').css('margin-top',(parseInt(window_h/2)-15)+'px');
        $('div#loader-circle').css('margin-left',(parseInt(window_w/2)-15)+'px');
    } 
}
function Dialog(id, title, dimension)
{
    this.id = id;
    this.title = title;
    if (dimension===undefined){
        this.dlg_width = 400;
        this.dlg_height = 150;
    }else{
        this.dlg_width = dimension[0];
        this.dlg_height = dimension[1];
    }
    this.close_button_width = 40;
    this.close_button_height = 30;
    
    if ($('div#'+id).length==0){
        //create dilog background
        var s="<div id='"+id+"' class='loader-bg'></div>";
        $('body').append(s);
        //set width & height
        var window_w = $(window).width();
        var window_h = $(window).height();
        $('div#'+id).css('width',window_w+'px');
        $('div#'+id).css('height',window_h+'px');
        
        //create dialog window
        var title_width = this.dlg_width - this.close_button_width-2;
        var content_box_width = this.dlg_width - 22;
        var content_box_height = this.dlg_height - 41- 22;
        
        s="<div id='dialog_"+this.id+"' style='float: left; width:"+this.dlg_width+"px;height:"+this.dlg_height+"px;border:solid 1px #ccc;background-color:#ffffff;box-shadow:0px 0px 30px rgba(50, 50, 50, 0.75);-moz-box-shadow:0px 0px 30px rgba(50, 50, 50, 0.75);-wekit-box-shadow:0px 0px 30px rgba(50, 50, 50, 0.75);'>";
            s+= "<div style='float:left;width:100%;border-bottom:solid 1px #ccc;height:"+this.close_button_height+"px;'>";
                s+="<div id='dialog_title_"+this.id+"' style='float:left;max-width:"+title_width+"px;height:"+this.close_button_height+"px;'>";
                    s+="<div style='padding:5px;font-size:16px;'>"+this.title+"</div>";
                s+="</div>";
                s+="<div id='dialog_close_"+this.id+"' style='float:right;width:"+this.close_button_width+"px;height:"+this.close_button_height+"px;cursor:pointer;' title='Close'>";
                    s+="<div style='padding: 5px;font-weight:bold;font-size:18px;text-align:center;'>X</div>";
                s+="</div>";
            s+= "</div>";
            
            //create content box
            s+="<div style='float:left; padding:10px;'>";
                s+="<div id='dialog_content_"+this.id+"' style='float:left;width:"+content_box_width+"px;height:"+content_box_height+"px;overflow:auto;'>Tes aja</div>";
            s+="</div>";
        s+="</div>";
        $('div#'+this.id).append(s);
        
        //set margin to put in the center
        $("div#dialog_"+this.id).css('margin-top',(parseInt(window_h/2)-parseInt(this.dlg_height/2))+'px');
        $("div#dialog_"+this.id).css('margin-left',(parseInt(window_w/2)-parseInt(this.dlg_width/2))+'px');
        
        $("div#dialog_close_"+this.id).click(function(){
            $('div#'+id).hide();
        })
    }
    
    this.resetDimension = function ()
    {
        //create dialog window
        var title_width = this.dlg_width - this.close_button_width-2;
        var content_box_width = this.dlg_width - 22;
        var content_box_height = this.dlg_height - 41- 22;
        
        //set title size
        $('div#dialog_title_'+this.id).css('width',title_width+'px').css('height',this.close_button_height+'px');
        
        //set content box
        $('div#dialog_content_'+this.id).css('width',content_box_width+'px').css('height',content_box_height+'px');
        
        //set margin of dialog relative to window
        $("div#dialog_"+this.id).css('margin-top',(parseInt(window_h/2)-parseInt(this.dlg_height/2))+'px');
        $("div#dialog_"+this.id).css('margin-left',(parseInt(window_w/2)-parseInt(this.dlg_width/2))+'px');
        
        //set container dimension
        $('div#dialog_'+this.id).css('width', this.dlg_width+'px').css('height', this.dlg_height+'px');
    }
    this.setDialogTitle = function (title){
        if (this.title != title){
            this.title = title;
            $("div#dialog_title_"+this.id).find('div').html(title);
        }
    }
    this.setDialogContent = function (content){
        $("div#dialog_content_"+this.id).html(content);
    }
    this.showDialog = function (){
        $('div#'+this.id).show();
    }
    this.closeDialog = function (){
        $('div#'+this.id).hide();
    }
    this.appendDialogContent = function(content){
        $("div#dialog_content_"+this.id).append(content);
    }
    this.setDimension = function (width, height){
        if (this.dlg_width!=width)
            this.dlg_width = width;
        if (this.dlg_height!= height)
            this.dlg_height = height;
        
        //reset position
        this.resetDimension();
    }
}
function objectLength(obj) {
    var result = 0;
    for(var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            // or Object.prototype.hasOwnProperty.call(obj, prop)
            result++;
        }
    }
    return result;
}