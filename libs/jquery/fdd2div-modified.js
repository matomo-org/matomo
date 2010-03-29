/*
 * jQuery fdd2div (Form Drop Down into Div plugin
 *
 * version 1.0 (6 May 2008)
 *
 * Licensed under GPL licenses:
 *  http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * The fdd2div() method provides a simple way of converting form drop down <select> into <div>.  
 *
 * fdd2div() takes 2 string and 2 integer argument:  $().fdd2div({css class name, open status of the menu, create html hyper links})
 *
 *   CssClassName: It will take the css class name or it will take the class name from the <div>. 
 *            		 If you don't specify an css class, default css will be used.
 *
 *	 OpenStatus: It will be let the menu open or close. By default it will be closed. 1 for open and 0 for closed
 *
 * @example $('#form_wrapper').fdd2div({CssClassName: "OverWrite_Default_Css_Class",OpenStatus: 1});
 * @desc Convert form drop down into div menu with css my own class (OverWrite_Default_Css_Class), menu will be open, take page name from <option> and create normal hyperlinks 
 *
 * @example $('#form_wrapper').fdd2div();
 * @desc Convert form drop down into div menu with default css class which is (fdd2div_default), OpenStatus: 0 (closed)
 * @name fdd2div
 * @type jQuery
 * @param 2 String and 2 Integers Options which control the drop down menu style and status
 * @cat Plugins/fdd2div
 * @return jQuery
 * @author Aamir Afridi (aamirafridi97@hotmail.com)
 * @author Sam Clark (sam@clark.name)
 */

(function($){
	$.fn.fdd2div = function(options)
	{
		var MianCssClassName="";

		var defaults = { 
			CssClassName: "fdd2div_default"
		}
		var options = $.extend(defaults, options);
		
		if($(this).attr('class') == null || $(this).attr('class').length == 0) {
			MianCssClassName=defaults.CssClassName;
			$(this).attr('class', MianCssClassName);
		} else {
			MianCssClassName=$(this).attr('class');
		}
		
		var unique_id = $(this).attr("id");
		var form = $(this).find('form');
		
		if($(this).find('form').length == 0) {
			alert("There is no/bad markup for form tag");
		} else {
			var SelectName = $(form).find('select').attr('name');
			var SelectOptions = $(form).find('option');

			if(SelectOptions.length == 1) {
				$(this).html(SelectOptions.html()).attr('class','test');
				return;
			}
			
			var FormMethod = $(form).attr('method');
			if(FormMethod!=null && FormMethod!="get") {
				FormMethod="post";
			}
			
			var FormAction = $(form).attr('action');
			if(FormAction == null) {
				FormAction="?";
			} else if (FormAction.indexOf('?') < 0) {
				FormAction+="?";
			} else {
				FormAction+="&";
			}
			
			var main_option;
			var child_options="";
			SelectOptions.each (
			 	function(n,i) {
					var OptionValue="";
					if( $(i).attr('value') != "" ) {
						OptionValue=$(i).attr('value');
					} else {
						OptionValue=i.firstChild.nodeValue;
					}
			
					if($(i).attr('selected') == true) {
						main_option="<a class=\""+MianCssClassName+"_main_link collapsed\" href='"+FormAction+SelectName+"="+OptionValue+"'>"+i.firstChild.nodeValue+"</a>\n";
					} else {
						if(FormMethod=="post") {
								var newForm = CreateHiddenForm("form"+unique_id+"_"+n,FormAction,SelectName,OptionValue);
								$('body').append("<div style=\"position:absolute\">"+newForm+"</div>");
								child_options+="<li><a href='"+FormAction+"' onclick=\"document.form"+unique_id+"_"+n+".submit();return false;\">"+i.firstChild.nodeValue+"</a></li>\n";
						} else {
							if($(i).attr('href')) {
								child_options+="<li><b><a target='_blank' href='"+$(i).attr('href')+"'>"+i.firstChild.nodeValue+"</a></b></li>\n";
							} else {
								child_options+="<li><a href='"+FormAction+SelectName+"="+OptionValue+"'>"+i.firstChild.nodeValue+"</a></li>\n";
							}
						}
					}
				});
			
			var menu = main_option+"<ul class=\""+MianCssClassName+"_ul_list\" style=\"position:relative\" >"+child_options+"</ul>";
			$(this).html(menu);
			
			var child_options = "#" + unique_id + " ul";
			var main_option = "#" + unique_id + " a."+MianCssClassName+"_main_link";

			// hide by default			
			$(child_options).hide();
						
			$(main_option).click(function () {
				if( $(this).attr("class") == MianCssClassName+"_main_link collapsed" ) {
					$(this).attr("class", MianCssClassName+"_main_link expanded");
					$(child_options).slideToggle(1);
				} else {
					$(this).attr("class", MianCssClassName+"_main_link collapsed");
					$(child_options).slideToggle(1);
				}
				return false;
			});
			
			$(window).click( function(){
				slideUpMenu();
			});
			
			$(main_option).blur( function() {
				if(!jQuery.browser.opera) {
					slideUpMenu();
				}
			});
			
			function slideUpMenu(){ 
				timeout = setTimeout( function(){ 
					// timeout after 100ms to make sure the click is triggered before the blur
					$(child_options).slideUp(1);
					$(main_option).attr("class", MianCssClassName+"_main_link collapsed");
					return false;
				}, 150);
			}
		}
		
		function CreateHiddenForm(FormName,FormAction,SelectName,OptionValue) {
			var HiddenForm;
			HiddenForm="<form method=\"post\" name='"+FormName+"' action='"+FormAction+"'><input type='hidden' name='"+SelectName+"' value='"+OptionValue+"'></form>";
			return HiddenForm;
		}
	}
})
(jQuery);
