$.fn.test = function(){
	if( location.search == '?notest' )
		return this;

	testScrollable();
	
	$.scrollTo.defaults.axis = 'xy';
	
	this._scrollable().find('div').html(
		navigator.userAgent +
		'<br />' +
		'document.compatMode is "' + document.compatMode + '"' +
		'<br />' +
		'scrolling the ' + this._scrollable().prop('nodeName')
	);

		/*var orig = [ $(window).scrollLeft(), $(window).scrollTop() ];
		
		scrollTo(0,1);
		var elem = document.documentElement.scrollTop ? document.documentElement : document.body;
		scrollTo(0,9e9);
		var max = $(window).scrollTop();			
		scrollTo(orig[0],orig[1]);
		
		setTimeout(function(){
			alert( elem.nodeName + ' ' + max );
		}, 1000 );*/
	return this.scrollTo('max', 1000).scrollTo(0, 1000)
};

function assert( bool, message ){
	if( !bool ){
		alert('FAIL: ' + message);
		throw new Error();
	}
};

function f( name ){
	return $(name)[0];
}

function testScrollable(){
	
	$.each([ window, document, f('body'), f('html') ], function(i, elem){
		var s = $(elem)._scrollable();
		assert( s.length == 1, 'scrollable must always return exactly 1 element' );
		assert( s.is('html,body'), 'scrollable must either html or body for window scrolling' );
	});
		
	$('body :not(iframe)').each(function(){
		var s = $(this)._scrollable();
		assert( s.length == 1, 'scrollable must always return exactly 1 element' );
		assert( s[0] == this, 'scrollable must return the same element for normal element scrolling' );
	});
};

$(function(){
	if( location.search == '?notest' )
		$('h1').hide();
});