/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


//
// TRANSITIONS ROW ACTION FOR DATA TABLES
//

function DataTable_RowActions_Transitions(dataTable) {
	this.dataTable = dataTable;
	this.transitions = null;
}

DataTable_RowActions_Transitions.prototype = new DataTable_RowAction;

// override trigger method directly because we don't need the label
DataTable_RowActions_Transitions.prototype.trigger = function(tr, e, subTableLabel) {
	var link = tr.find('> td:first > a').attr('href');
	link = $('<textarea>').html(link).val(); // remove html entities
	this.openPopover(link);
};

DataTable_RowActions_Transitions.prototype.doOpenPopover = function(link) {
	if (this.transitions === null) {
		this.transitions = new Piwik_Transitions(link, this);
	} else {
		this.transitions.reset(link);
	}
	this.transitions.showPopover();
};


DataTable_RowActions_Registry.register({
	
	name: 'Transitions',
	
	dataTableIcon: 'plugins/Transitions/templates/transitions_rowaction.png',
	
	createInstance: function(dataTable) {
		return new DataTable_RowActions_Transitions(dataTable);
	},
	
	isAvailable: function(dataTableParams, tr) {
		return dataTableParams.module == 'Actions'
			&& dataTableParams.action == 'getPageUrls'
			&& tr.find('> td:first > a').size() > 0;
	}

});


//
// TRANSITIONS IMPLEMENTATION
//

function Piwik_Transitions(link, rowAction) {
	this.reset(link);
	this.rowAction = rowAction;
	
	this.ajax = new Piwik_Transitions_Ajax();
	this.model = new Piwik_Transitions_Model(this.ajax);
}

Piwik_Transitions.prototype.reset = function(link) {
	this.link = link;
	
	this.popover = null;
	this.canvas = null;
	this.centerBox = null;
};

/** Open the popover */
Piwik_Transitions.prototype.showPopover = function() {
	var self = this;

	// initialize popover (with loading message)
	var loading = $('div.loadingPiwik:first').clone();
	var box = $(document.createElement('div')).attr('id', 'Transitions_Popover').html(loading);
	box.dialog({
		title: '',
		modal: true,
		width: '900px',
		position: ['center', 'center'],
		resizable: false,
		autoOpen: true,
		close: function(event, ui) {
			box.dialog('destroy').remove();
		}
	});
	this.popover = box;

	// load the popover HTML
	this.ajax.callTransitionsController('renderPopover', function(html) {
		box.html(html);
		box.dialog({position: ['center', 'center']});

		var canvasDom = box.find('#Transitions_Canvas')[0];
		self.canvas = new Piwik_Transitions_Canvas(canvasDom, 850, 550);

		self.centerBox = box.find('#Transitions_CenterBox');

		self.centerBox.find('h2').html(self.addBreakpoints(self.link));

		self.model.loadData(self.link, function() {
			self.render();
		});
	});
};

/** Add break points to string so that it can be displayed more compactly */
Piwik_Transitions.prototype.addBreakpoints = function(text) {
	return text.replace(/([\/&=?\.%#:])/g, '$1<wbr>');
};

/** Render the popover content */
Piwik_Transitions.prototype.render = function() {
	this.renderCenterBox();
	
	this.renderEntriesAndExits();

	this.renderPreviousPages();
	this.renderFollowingPages();

	this.renderReferrers();
	
	this.renderLoops();
};

Piwik_Transitions.prototype.renderCenterBox = function() {
	var box = this.centerBox;
	box.removeClass('Transitions_Loading');

	box.find('.Transitions_Pageviews').html(this.model.pageviews);

	var self = this;
	var showMetric = function(cssClass, modelProperty) {
		box.find('.Transitions_' + cssClass).html(self.model[modelProperty]);
		box.find('.Transitions_' + cssClass + 'Percentage').html(self.model.getPercentage(modelProperty, true));
	};

	showMetric('DirectEntries', 'directEntries');
	showMetric('SearchEngines', 'searchEngineReferrals');
	showMetric('Websites', 'websiteReferrals');

	showMetric('Exits', 'exits');
	showMetric('Bounces', 'bounces');

	box.find('.Transitions_CenterBoxMetrics').show();
};

Piwik_Transitions.prototype.renderLoops = function() {
	if (this.model.loops == 0) {
		return;
	}
	this.popover.find('.Transitions_Loops').html(this.model.loops);
	this.popover.find('.Transitions_LoopsPercentage').html(this.model.getPercentage('loops', true));
	this.popover.find('#Transitions_Loops').show();
	this.canvas.renderLoops(this.model.getPercentage('loops'));
};

Piwik_Transitions.prototype.renderEntriesAndExits = function() {
	if (this.model.directEntries > 0) {
		this.canvas.renderBoxLeft({
			share: this.model.getPercentage('directEntries'),
			gradient: this.canvas.createHorizontalGradient('#CFEDCA', '#91DE83', 'left'),
			boxText: 'Direct entries',
			boxTextNumLines: 1,
			boxTextCssClass: 'SingleLine',
			smallBox: true
		});
		this.canvas.addBoxSpacing(20, 'left');
	}

	if (this.model.exits > 0) {
		this.canvas.renderBoxRight({
			share: this.model.getPercentage('exits'),
			gradient: this.canvas.createHorizontalGradient('#EBCDCC', '#D98E8D', 'right'),
			boxText: 'Exits',
			boxTextNumLines: 1,
			boxTextCssClass: 'SingleLine',
			smallBox: true
		});
		this.canvas.addBoxSpacing(20, 'right');
	}
};

Piwik_Transitions.prototype.renderPreviousPages = function() {
	this.renderPages(this.model.getPreviousPages(), 'left');
};

Piwik_Transitions.prototype.renderFollowingPages = function() {
	this.renderPages(this.model.getFollowingPages(), 'right');
};

Piwik_Transitions.prototype.renderPages = function(pagesData, side) {
	var self = this;
	var gradientPages = this.canvas.createHorizontalGradient('#F5F4F0', '#CFCBB6', side);
	var gradientOthers = this.canvas.createHorizontalGradient('#F5F4F0', '#E3E1D5', side);

	var totalShare = this.model.getPercentage(side == 'left' ? 'internalTrafficIn' : 'internalTrafficOut');
	totalShare -= this.model.getPercentage('loops');
	
	for (var i = 0; i < pagesData.length; i++) {
		var pageData = pagesData[i];

		var isOthers = pageData.url == 'Others';
		if (isOthers) {
			this.canvas.addBoxSpacing(10, side);
		}

		var params = {
			share: pageData.percentage / 100 * totalShare,
			gradient: isOthers ? gradientOthers : gradientPages,
			boxText: pageData.url + ' (' + pageData.referrals + ')',
			boxTextNumLines: 3,
			curveText: pageData.percentage + '%',
			onClick: (function(url) {
				return function() { self.reloadPopover(url) };
			})(pageData.url)
		};

		if (side == 'left') {
			this.canvas.renderBoxLeft(params);
		} else {
			this.canvas.renderBoxRight(params);
		}
	}
};

Piwik_Transitions.prototype.renderReferrers = function() {
	var self = this;
	var gradient = this.canvas.createHorizontalGradient('#DDE4ED', '#9BBADE', 'left');
	this.canvas.addBoxSpacing(20, 'left');

	var renderBox = function(modelVariable, text) {
		if (self.model[modelVariable] == 0) {
			return;
		}
		self.canvas.renderBoxLeft({
			share: self.model.getPercentage(modelVariable),
			gradient: gradient,
			boxText: text,
			boxTextNumLines: 1,
			boxTextCssClass: 'SingleLine',
			smallBox: true
		});
	};

	renderBox('searchEngineReferrals', 'From Search Engines');
	renderBox('websiteReferrals', 'From other Websites');
	renderBox('campaignReferrals', 'From Campaigns');
};

Piwik_Transitions.prototype.reloadPopover = function(link) {
	this.popover.dialog('close');
	this.rowAction.openPopover(link);
};


// --------------------------------------
// CANVAS
// --------------------------------------

function Piwik_Transitions_Canvas(canvasDom, width, height) {
	if (!canvasDom.getContext) {
		alert('Your browser is not supported.');
		return;
	}

	/** DOM element that contains the canvas */
	this.container = $(canvasDom).parent();
	/** Drawing context of the canvas */
	this.context = canvasDom.getContext('2d');

	/** Width of the entire canvas */
	this.width = canvasDom.width = width;
	/** Height of the entire canvas */
	this.height = canvasDom.height = height;

	/** Current Y positions */
	this.leftBoxPositionY = 0;
	this.leftCurvePositionY = 110;
	this.rightBoxPositionY = 0;
	this.rightCurvePositionY = 110;

	/** Width of the rectangular box */
	this.boxWidth = 140;
	/** Height of the rectangular box */
	this.boxHeight = 53;
	/** Height of a smaller rectangular box */
	this.smallBoxHeight = 30;
	/** Width of the curve that connects the boxes to the center */
	this.curveWidth = 180;
	/** Line-height of the text */
	this.lineHeight = 14;
	/** Spacing between rectangular boxes */
	this.boxSpacing = 7;
	/** Spacing between the curves where they connect to the center */
	this.curveSpacing = 2;

	/** The total net height (without curve spacing) of the curves as they connect to the center */
	this.totalHeightOfConnections = 170;

	/** X positions of the left box - begin means left, end means right */
	this.leftBoxBeginX = 0;
	this.leftBoxEndX = this.leftCurveBeginX = this.leftBoxBeginX + this.boxWidth;
	this.leftCurveEndX = this.leftCurveBeginX + this.curveWidth;

	/** X positions of the right box - begin means left, end means right */
	this.rightBoxEndX = this.width;
	this.rightBoxBeginX = this.rightCurveEndX = this.rightBoxEndX - this.boxWidth;
	this.rightCurveBeginX = this.rightCurveEndX - this.curveWidth;
}

/**
 * Helper to create horizontal gradients
 * @param	position	left|right
 */
Piwik_Transitions_Canvas.prototype.createHorizontalGradient = function(lightColor, darkColor, position) {
	var fromX, toX, fromColor, toColor;

	if (position == 'left') {
		// gradient is used to fill a box on the left
		fromX = this.leftBoxBeginX + 50;
		toX = this.leftCurveEndX - 20;
		fromColor = lightColor;
		toColor = darkColor;
	} else {
		// gradient is used to fill a box on the right
		fromX = this.rightCurveBeginX + 20;
		toX = this.rightBoxEndX - 50;
		fromColor = darkColor;
		toColor = lightColor;
	}

	var gradient = this.context.createLinearGradient(fromX, 0, toX, 0);
	gradient.addColorStop(0, fromColor);
	gradient.addColorStop(1, toColor);

	return gradient;
};

/** Render text using a div inside the container */
Piwik_Transitions_Canvas.prototype.renderText = function(text, x, y, cssClass, onClick) {
	var div = this.addDomElement('div', 'Text');
	div.html(Piwik_Transitions.prototype.addBreakpoints(text));
	div.css({
		left: x + 'px',
		top: y + 'px'
	});
	if (cssClass) {
		div.addClass('Transitions_' + cssClass);
	}
	if (onClick) {
		div.css('cursor', 'pointer').hover(function() {
			$(this).addClass('Transitions_Hover');
		}, function() {
			$(this).removeClass('Transitions_Hover');
		}).click(onClick);
	}
	return div;
};

/** Add a DOM element inside the container (as a sibling of the canvas) */
Piwik_Transitions_Canvas.prototype.addDomElement = function(tagName, cssClass) {
	var el = $(document.createElement('div')).addClass('Transitions_' + cssClass);
	this.container.append(el);
	return el;
};

/**
 * Render a box on the left hand side.
 * This method automatically keeps track of the current position.
 *
 * OPTIONS (pass as object)
 * share: of the box in the total amount of incoming transitions
 * gradient: for filling the box
 * boxText: to be placed inside the box
 * boxTextNumLines: the number of lines to be placed in the box
 * boxTextCssClass: for divs containing the texts (optional)
 * curveText: to be placed where the curve begins (optional)
 * smallBox: use this.smallBoxHeight instead of this.boxHeight (optional)
 */
Piwik_Transitions_Canvas.prototype.renderBoxLeft = function(params) {
	var curveHeight = Math.round(this.totalHeightOfConnections * params.share);
	curveHeight = Math.max(curveHeight, 1);

	var boxHeight = params.smallBox ? this.smallBoxHeight : this.boxHeight;

	// DRAW BACKGROUND
	this.context.fillStyle = params.gradient;

	// derive coordinates for ths curve
	var leftUpper = {x: this.leftCurveBeginX, y: this.leftBoxPositionY};
	var leftLower = {x: this.leftCurveBeginX, y: this.leftBoxPositionY + boxHeight};
	var rightUpper = {x: this.leftCurveEndX, y: this.leftCurvePositionY};
	var rightLower = {x: this.leftCurveEndX, y: this.leftCurvePositionY + curveHeight};

	// derive control points for bezier curve
	var center = (this.leftCurveBeginX + this.leftCurveEndX) / 2;
	var cp1Upper = {x: center, y: leftUpper.y};
	var cp2Upper = {x: center, y: rightUpper.y};
	var cp1Lower = {x: center, y: rightLower.y};
	var cp2Lower = {x: center, y: leftLower.y};

	this.context.beginPath();

	// the flow
	this.context.moveTo(leftUpper.x, leftUpper.y);
	this.context.bezierCurveTo(cp1Upper.x, cp1Upper.y, cp2Upper.x, cp2Upper.y, rightUpper.x, rightUpper.y);
	this.context.lineTo(rightLower.x, rightLower.y);
	this.context.bezierCurveTo(cp1Lower.x, cp1Lower.y, cp2Lower.x, cp2Lower.y, leftLower.x, leftLower.y);

	// the box
	this.context.lineTo(leftLower.x - this.boxWidth + 4, leftLower.y);
	this.context.lineTo(leftLower.x - this.boxWidth, leftUpper.y);
	this.context.lineTo(leftUpper.x, leftUpper.y);
	this.context.fill();

	if (typeof this.context.endPath == 'function') {
		this.context.endPath();
	}


	// ADD TEXT

	// inside box
	var boxTextLeft = this.leftBoxBeginX + 10;
	var boxTextTop = this.leftBoxPositionY + boxHeight / 2 - params.boxTextNumLines * this.lineHeight / 2;
	var onClick = typeof params.onClick == 'function' ? params.onClick : false; 
	var el = this.renderText(params.boxText, boxTextLeft, boxTextTop, 'BoxTextLeft', onClick);
	if (params.boxTextCssClass) {
		el.addClass('Transitions_' + params.boxTextCssClass);
	}

	// at the beginning of the curve
	if (params.curveText) {
		var curveTextLeft = this.leftBoxBeginX + this.boxWidth + 12;
		var curveTextTop = this.leftBoxPositionY + boxHeight / 2 - this.lineHeight / 2;
		this.renderText(params.curveText, curveTextLeft, curveTextTop, 'CurveText');
	}


	this.leftBoxPositionY += boxHeight + this.boxSpacing;
	this.leftCurvePositionY += curveHeight + this.curveSpacing;
};

/**
 * Render a box on the right hand side.
 * This method automatically keeps track of the current position.
 *
 * OPTIONS (pass as object)
 * share: of the box in the total amount of incoming transitions
 * gradient: for filling the box
 * boxText: to be placed inside the box
 * boxTextNumLines: the number of lines to be placed in the box
 * boxTextCssClass: for divs containing the texts (optional)
 * curveText: to be placed where the curve begins (optional)
 */
Piwik_Transitions_Canvas.prototype.renderBoxRight = function(params) {
	var curveHeight = Math.round(this.totalHeightOfConnections * params.share);
	curveHeight = Math.max(curveHeight, 1);

	var boxHeight = params.smallBox ? this.smallBoxHeight : this.boxHeight;

	// DRAW BACKGROUND
	this.context.fillStyle = params.gradient;

	// derive coordinates for curve
	var leftUpper = {x: this.rightCurveBeginX, y: this.rightCurvePositionY};
	var leftLower = {x: this.rightCurveBeginX, y: this.rightCurvePositionY + curveHeight};
	var rightUpper = {x: this.rightCurveEndX, y: this.rightBoxPositionY};
	var rightLower = {x: this.rightCurveEndX, y: this.rightBoxPositionY + boxHeight};

	// derive control points for bezier curve
	var center = (this.rightCurveBeginX + this.rightCurveEndX) / 2;
	var cp1Upper = {x: center, y: leftUpper.y};
	var cp2Upper = {x: center, y: rightUpper.y};
	var cp1Lower = {x: center, y: rightLower.y};
	var cp2Lower = {x: center, y: leftLower.y};

	this.context.beginPath();

	// the flow part 1
	this.context.moveTo(leftUpper.x, leftUpper.y);
	this.context.bezierCurveTo(cp1Upper.x, cp1Upper.y, cp2Upper.x, cp2Upper.y, rightUpper.x, rightUpper.y);

	// the box
	this.context.lineTo(rightUpper.x + this.boxWidth, rightUpper.y);
	this.context.lineTo(rightLower.x + this.boxWidth - 4, rightLower.y);
	this.context.lineTo(rightLower.x, rightLower.y);

	// the flow part 2
	this.context.bezierCurveTo(cp1Lower.x, cp1Lower.y, cp2Lower.x, cp2Lower.y, leftLower.x, leftLower.y);
	this.context.lineTo(leftUpper.x, leftUpper.y);
	this.context.fill();

	if (typeof this.context.endPath == 'function') {
		this.context.endPath();
	}


	// ADD TEXT

	// inside box
	var boxTextLeft = this.rightBoxBeginX;
	var boxTextTop = this.rightBoxPositionY + boxHeight / 2 - params.boxTextNumLines * this.lineHeight / 2;
	var onClick = typeof params.onClick == 'function' ? params.onClick : false;
	var el = this.renderText(params.boxText, boxTextLeft, boxTextTop, 'BoxTextRight', onClick);
	if (params.boxTextCssClass) {
		el.addClass('Transitions_' + params.boxTextCssClass);
	}

	// at the beginning of the curve
	if (params.curveText) {
		var curveTextLeft = this.rightBoxBeginX - 35;
		var curveTextTop = this.rightBoxPositionY + boxHeight / 2 - this.lineHeight / 2;
		this.renderText(params.curveText, curveTextLeft, curveTextTop, 'CurveText');
	}


	this.rightBoxPositionY += boxHeight + this.boxSpacing;
	this.rightCurvePositionY += curveHeight + this.curveSpacing;
};

/** Add spacing after the current box */
Piwik_Transitions_Canvas.prototype.addBoxSpacing = function(spacing, side) {
	if (side == 'left') {
		this.leftBoxPositionY += spacing;
	} else {
		this.rightBoxPositionY += spacing;
	}
};

Piwik_Transitions_Canvas.prototype.renderLoops = function(share) {
	var curveHeight = Math.round(this.totalHeightOfConnections * share);
	curveHeight = Math.max(curveHeight, 1);
	
	// create gradient
	var gradient = this.context.createLinearGradient(this.leftCurveEndX - 50, 0, this.rightCurveBeginX + 50, 0);
	var light = '#F5F4F0';
	var dark = '#E3E1D5';
	gradient.addColorStop(0, dark);
	gradient.addColorStop(.5, light);
	gradient.addColorStop(1, dark);
	
	this.context.fillStyle = gradient;
	
	this.context.beginPath();
	
	// curve from the upper left connection to the center box to the lower left connection to the text box 
	var point1 = {x: this.leftCurveEndX, y: this.leftCurvePositionY};
	var point2 = {x: this.leftCurveEndX, y: 385};
	
	var cpLeftX = (this.leftCurveBeginX + this.leftCurveEndX) / 2 + 30;
	var cp1 = {x: cpLeftX, y: point1.y};
	var cp2 = {x: cpLeftX, y: point2.y};
	
	this.context.moveTo(point1.x, point1.y);
	this.context.bezierCurveTo(cp1.x, cp1.y, cp2.x, cp2.y, point2.x, point2.y);
	
	// lower line of text box
	var point3 = {x: this.rightCurveBeginX, y: point2.y};
	this.context.lineTo(point3.x, point3.y);
	
	// curve to upper right connection to the center box
	var point4 = {x: this.rightCurveBeginX, y: this.rightCurvePositionY};
	var cpRightX = (this.rightCurveBeginX + this.rightCurveEndX) / 2 - 30;
	var cp3 = {x: cpRightX, y: point3.y};
	var cp4 = {x: cpRightX, y: point4.y};
	this.context.bezierCurveTo(cp3.x, cp3.y, cp4.x, cp4.y, point4.x, point4.y);
	
	// line to lower right connection to the center box
	var point5 = {x: point4.x, y: point4.y + curveHeight};
	this.context.lineTo(point5.x, point5.y);
	
	// curve to upper right connection to the text box
	var point6 = {x: point5.x, y: point2.y - 25};
	cpRightX -= 30;
	var cp5 = {x: cpRightX, y: point5.y};
	var cp6 = {x: cpRightX, y: point6.y};
	this.context.bezierCurveTo(cp5.x, cp5.y, cp6.x, cp6.y, point6.x, point6.y);
	
	// upper line of the text box
	var point7 = {x: point1.x, y: point6.y};
	this.context.lineTo(point7.x, point7.y);
	
	// line to lower left connection to the center box
	var point8 = {x: point1.x, y: point1.y +  + curveHeight};
	cpLeftX += 30;
	var cp7 = {x: cpLeftX, y: point7.y};
	var cp8 = {x: cpLeftX, y: point8.y};
	this.context.bezierCurveTo(cp7.x, cp7.y, cp8.x, cp8.y, point8.x, point8.y);
	
	this.context.fill();

	if (typeof this.context.endPath == 'function') {
		this.context.endPath();
	}

};


// --------------------------------------
// MODEL
// --------------------------------------

function Piwik_Transitions_Model(ajax) {
	this.ajax = ajax;
}

Piwik_Transitions_Model.prototype.loadData = function(link, callback) {
	var self = this;
	
	this.pageviews = 0;
	this.exits = 0;
	this.bounces = 0;
	this.loops = 0;

	this.internalTrafficIn = 0;
	this.internalTrafficOut = 0;

	this.directEntries = 0;
	this.searchEngineReferrals = 0;
	this.websiteReferrals = 0;
	this.campaignReferrals = 0;

	this.followingPages = [];
	this.previousPages = [];

	this.ajax.callApi('Transitions.getFullReport', {
			pageUrl: link,
			expanded: 1
		},
		function(report) {
			console.log(report);
			// load page metrics
			self.pageviews = report.pageMetrics.pageviews;
			self.exits = report.pageMetrics.exits;
			self.bounces = report.pageMetrics.bounces;
			self.loops = report.pageMetrics.loops;
			self.internalTrafficIn = report.pageMetrics.internalTrafficIn;
			self.internalTrafficOut = report.pageMetrics.internalTrafficOut;

			// load referrers: split direct entries and others
			for (var i = 0; i < report.referrers.length; i++) {
				var referrer = report.referrers[i];
				if (referrer.shortName == 'direct') {
					self.directEntries = referrer.visits;
				} else if (referrer.shortName == 'search') {
					self.searchEngineReferrals = referrer.visits;
				} else if (referrer.shortName == 'website') {
					self.websiteReferrals = referrer.visits;
				} else if (referrer.shortName == 'campaign') {
					self.campaignReferrals = referrer.visits;
				}
			}

			// load previous and following pages
			self.previousPages = report.previousPages;
			self.followingPages = report.followingPages;

			callback();
		});
};

Piwik_Transitions_Model.prototype.getPercentage = function(metric, formatted) {
	var percentage = (this.pageviews == 0 ? 0 : this[metric] / this.pageviews);

	if (formatted) {
		percentage = this.roundPercentage(percentage);
		percentage += '%';
	}

	return percentage;
};

Piwik_Transitions_Model.prototype.getPreviousPages = function() {
	var total = 0;
	for (var i = 0; i < this.previousPages.length; i++) {
		total += parseInt(this.previousPages[i].referrals, 10);
	}

	for (var i = 0; i < this.previousPages.length; i++) {
		this.previousPages[i].percentage = this.roundPercentage(this.previousPages[i].referrals / total);
	}

	return this.previousPages;
};

Piwik_Transitions_Model.prototype.getFollowingPages = function() {
	var total = 0;

	for (var i = 0; i < this.followingPages.length; i++) {
		total += parseInt(this.followingPages[i].referrals, 10);
	}

	for (var i = 0; i < this.followingPages.length; i++) {
		this.followingPages[i].percentage = this.roundPercentage(this.followingPages[i].referrals / total);
	}

	return this.followingPages;
};

Piwik_Transitions_Model.prototype.roundPercentage = function(value) {
	if (value < .1) {
		return Math.round(value * 1000) / 10.0;
	} else {
		return Math.round(value * 100);
	}
};


// --------------------------------------
// AJAX
// --------------------------------------

function Piwik_Transitions_Ajax() {
}

Piwik_Transitions_Ajax.prototype.callTransitionsController = function(action, callback) {
	$.post('index.php', {
		module: 'Transitions',
		action: action,
		date: piwik.currentDateString,
		idSite: piwik.idSite,
		period: piwik.period
	}, callback);
};

Piwik_Transitions_Ajax.prototype.callApi = function(method, params, callback) {
	params.module = 'API';
	params.method = method;
	params.date = piwik.currentDateString;
	params.idSite = piwik.idSite;
	params.period = piwik.period;
	params.token_auth = piwik.token_auth;
	params.format = 'JSON';

	$.post('index.php', params, function(result) {
		if (typeof result.result != 'undefined' && result.result == 'error') {
			alert(result.message);
		} else {
			callback(result);
		}
	}, 'json');
};
