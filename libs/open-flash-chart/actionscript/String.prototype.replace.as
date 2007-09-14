// -----------------------
// Replace string in a string with a string; ;)
// 2006.08
// Raimundas Banevicius (Deril)
// mail: raima156@yahoo.com
// -----------------------

// originalString = "This is the original fucking text. What the hell are you typing?"
// replacedText = originalString.replace('fuck','***');
// trace("-----------------------------");
// trace("original was: " + originalString);
// trace("replaced is: " + replacedText);


String.prototype.replace = function() {
	var arg_search:String = arguments[0], arg_replace:String = arguments[1];
	var preText:String = this, newText:String = "";
	var tempArr:Array = preText.split(arg_search);
	newText = tempArr[0];
	for (var i = 1; i<tempArr.length; i++) {
		newText += arg_replace+tempArr[i];
	}
	return newText;
};

var TAB   = 9;
var LINEFEED = 10;
var CARRIAGE = 13; 
var SPACE = 32; 

String.prototype.LTrim = function()
{
   var s = this.toString();
   var i = 0;   
   while(s.charCodeAt(i) == SPACE 
      || s.charCodeAt(i) == CARRIAGE 
      || s.charCodeAt(i) == LINEFEED 
      || s.charCodeAt(i) == TAB) {
      i++;
   }   
   return s.substring(i,s.length);
};