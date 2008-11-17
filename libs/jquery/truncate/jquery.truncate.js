
jQuery.fn.truncate = function(max) {
 return this.each(
 	function() {
 		var trail='...';
 		if(jQuery(this).children().length==0) {
 			v=jQuery.trim(jQuery(this).text());
 			while(max<v.length) {
 				c=v.charAt(max);
 				newStringTruncated=v.substring(0,max)+trail;
 				charToRemove='"';
 				regExp=new RegExp("["+charToRemove+"]","g");
 				vCleaned = v
 							.replace(regExp,"&amp;quot;")
 							.replace(/</g, '&amp;lt;')
 							.replace(/>/g, '&amp;gt;');
 				newStringTruncated = newStringTruncated
		 										.replace(regExp,"'")
		 										.replace(/</g, '&lt;')
		 										.replace(/>/g, '&gt;');
 				html='<span class="truncated" title="'+vCleaned+'">'+newStringTruncated+'</span>';
 				jQuery(this).html(html);
 				break;
 				max--;
 			}
 		}
 	}
 );
};