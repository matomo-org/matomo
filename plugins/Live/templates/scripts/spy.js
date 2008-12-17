/*
	jQuery Plugin spy (leftlogic.com/info/articles/jquery_spy2)
	(c) 2006 Remy Sharp (leftlogic.com)
	$Id$
*/
var spyRunning = 1;

$.fn.spy = function(settings) {
	var spy = this;
	spy.epoch = new Date(1970, 0, 1);
	spy.last = '';
	spy.parsing = 0;
	spy.waitTimer = 0;
	spy.json = null;
	
	if (!settings.ajax) {
		alert("An AJAX/AJAH URL must be set for the spy to work.");
		return;
	}
	
	spy.attachHolder = function() {
		// not mad on this, but the only way to parse HTML collections
		if (o.method == 'html')
			$('body').append('<div style="display: none!important;" id="_spyTmp"></div>');
	}

	// returns true for 'no dupe', and false for 'dupe found'
	// latest = is latest ajax return value (raw)
	// last = is previous ajax return value (raw)
	// note that comparing latest and last if they're JSON objects
	// always returns false, so you need to implement it manually.
	spy.isDupe = function(latest, last) {
		if ((last.constructor == Object) && (o.method == 'html'))
			return (latest.html() == last.html());
		else if (last.constructor == String)
			return (latest == last);
		else
			return 0;
	}
	
	spy.parse = function(e, r) {
		spy.parsing = 1; // flag to stop pull via ajax
		if (o.method == 'html') {
			$('div#_spyTmp').html(r); // add contents to hidden div
		} else if (o.method == 'json') {
			eval('spy.json = ' + r); // convert text to json
		}
		
		if ((o.method == 'json' && spy.json.constructor == Array) || o.method == 'html') {
			if (spy.parseItem(e)) {
				spy.waitTimer = window.setInterval(function() {
					if (spyRunning) {
						if (!spy.parseItem(e)) {
							spy.parsing = 0;
							clearInterval(spy.waitTimer);
						}
					}
				}, o.pushTimeout);
			} else {
				spy.parsing = 0;
			}
		} else if (o.method == 'json') { // we just have 1
			eval('spy.json = ' + r)
			spy.addItem(e, spy.json);
			spy.parsing = 0;
		}
	}
	
	// returns true if there's more to parse
	spy.parseItem = function(e) {
		if (o.method == 'html') {
			// note: pre jq-1.0 doesn't return the object
			var i = $('div#_spyTmp').find('div:first').remove();
			if (i.size() > 0) {
				i.hide();
				spy.addItem(e, i);
			}		
			return ($('div#_spyTmp').find('div').size() != 0);
		} else {
			if (spy.json.length) {
				var i = spy.json.shift();
				spy.addItem(e, i);
			}

			return (spy.json.length != 0);
		}
	}
	
	spy.addItem = function(e, i) {
		if (! o.isDupe.call(this, i, spy.last)) {
			spy.last = i; // note i is a pointer - so when it gets modified, so does spy.last
			$('#' + e.id + ' > div:gt(' + (o.limit - 1) + ')').remove();
			o.push.call(e, i);
			$('#' + e.id + ' > div:first').fadeIn(o.fadeInSpeed);
		}
	}
	
	spy.push = function(r) {
		$('#' + this.id).prepend(r);
	}
	
	var o = {
		limit: (settings.limit || 10),
		ajax: settings.ajax,
		timeout: (settings.timeout || 3000),
		pushTimeout: (settings.pushTimeout || settings.timeout || 3000),
		method: (settings.method || 'html').toLowerCase(),
		push: (settings.push || spy.push),
		fadeInSpeed: (settings.fadeInSpeed || 'slow'), // 1400 = crawl
		customParameterName: settings.customParameterName,
		customParameterValueCallback: settings.customParameterValueCallback,
		isDupe: (settings.isDupe || spy.isDupe),
	};

	spy.attachHolder();

	return this.each(function() {
		var e = this;
		var lr = ''; // last ajax return
		var parameters = new Object;
		spy.ajaxTimer = window.setInterval(function() {
			if (spyRunning && (!spy.parsing)) {
			    var customParameterValue = o.customParameterValueCallback.call();
				parameters[o.customParameterName] = customParameterValue;
				$.get(o.ajax, parameters, function(r) {
					spy.parse(e, r);
				});
			}	
		}, o.timeout);
	});
};

function pauseSpy() {
	spyRunning = 0; return false;
}

function playSpy() {
	spyRunning = 1; return false;
}
