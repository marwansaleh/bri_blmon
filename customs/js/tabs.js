// JavaScript Document
function Tabs(array_id_tabs,parent_id,arr_onclick) {	
	this.tabs = array_id_tabs;
	this.tabs_click = arr_onclick;
	this.tabsNum = countTabs;
	this.generatedid = Math.floor(Math.random()*1001);
	this.id = 'tab_page_'+this.generatedid;
	this.parentId = parent_id;
	this.activePage = 0;
	this.init = init;
        this.setActivePage = function(index){
            if (index >=0&&index<countTabs(this.tabs)){
                this.activePage = index;
                $('ul.tabs-caption-list li').eq(index).click();
            }
        }
	//this.init();
}

function countTabs(tabs){
	var i = 0;
	for (key in tabs){
		i++;
	}
	
	return i;
}
function init(active_index){
	if (active_index !== undefined) 
		this.activePage = active_index;
	//check if exist, if not created a container
	var s = "";
	if ($('#'+this.id).length==0)
	{
		s = "<div id='"+this.id+"' class='tabs-page'></div>";
		$('#'+this.parentId).prepend(s);
	}
	//create caption
	s = "<div class='tabs-caption'><ul class='tabs-caption-list'>";
	i=0;
	for (key in this.tabs){
		if (i==this.activePage)
			s+="<li class='active'>"+this.tabs[key]+"</li>";
		else
			s+="<li>"+this.tabs[key]+"</li>";
		i++;
	}
	s+= "</ul></div>"
	$('#'+this.id).prepend(s);
	
	//add content-page box
	$('#'+this.id).append("<div class='tabs-content-page'></div>");
	
	//now move the pages into tab
	i=0;	
	for (key in this.tabs){
		detach_page = $('#'+key).detach();
		if (i==this.activePage)
			detach_page.css('display','block');
		else
			detach_page.css('display','none');
			
		detach_page.appendTo('#'+this.id);	
		i++;
	}
	
	var tabs = this.tabs;
	var tabs_click = this.tabs_click;
	
	//execute if function exist the active page in first load
	if (tabs_click !==undefined && tabs_click[this.activePage]!=='')
		eval(tabs_click[this.activePage]);
		
	$('ul.tabs-caption-list li').click ( function () {
		var index = $(this).index();
		$('ul.tabs-caption-list li').removeClass('active');
		$(this).addClass('active');
		
		//hide old content;
		i=0;
		for (key in tabs){
                    if (i==index)
                        $('#'+key).show();
                    else
                        $('#'+key).hide();
			
                    i++;
		}
		if (tabs_click !==undefined){
			if (tabs_click[index]!=='')
				eval(tabs_click[index]);
		}
	});
}