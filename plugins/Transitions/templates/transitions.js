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
	
	this.leftGroups = ['previousPages', 'searchEngines', 'websites', 'campaigns'];
	this.rightGroups = ['followingPages', 'downloads', 'outlinks'];
}

Piwik_Transitions.prototype.reset = function(link) {
	this.link = link;
	this.popover = null;
	this.canvas = null;
	this.centerBox = null;
	
	this.leftOpenGroup = 'previousPages';
	this.rightOpenGroup = 'followingPages';
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
		open: function(event, ui) { 
			$('.ui-widget-overlay').on('click.transitions',function(){ 
				box.dialog('close'); 
			}); 
		},
		close: function(event, ui) {
			box.dialog('destroy').remove();
			piwikHelper.abortQueueAjax(); 
			$('.ui-widget-overlay').off('click.rowEvolution'); 
		}
	});
	this.popover = box;

	// load the popover HTML
	this.ajax.callTransitionsController('renderPopover', function(html) {
		box.html(html);
		box.dialog({position: ['center', 'center']});

		var canvasDom = box.find('#Transitions_Canvas')[0];
		var canvasBgDom = box.find('#Transitions_Canvas_Background')[0];
		self.canvas = new Piwik_Transitions_Canvas(canvasDom, canvasBgDom, 850, 550);

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
	
	this.renderLeftSide();
	this.renderRightSide();
	
	this.renderLoops();
};

/** Render left side: referrer groups & direct entries */
Piwik_Transitions.prototype.renderLeftSide = function() {
	this.renderGroups(this.leftGroups, this.leftOpenGroup, 'left');
	this.renderEntries();
	
	this.reRenderIfNeededToCenter('left');
};

/** Render right side: following pages & exits */
Piwik_Transitions.prototype.renderRightSide = function() {
	this.renderGroups(this.rightGroups, this.rightOpenGroup, 'right');
	this.renderExits();
	
	this.reRenderIfNeededToCenter('right');
};

/** Helper method to render open and closed groups for both sides */
Piwik_Transitions.prototype.renderGroups = function(groups, openGroup, side) {
	for (var i = 0; i < groups.length; i++) {
		var groupName = groups[i];
		if (groupName == openGroup) {
			if (i != 0) {
				this.canvas.addBoxSpacing(13, side);
			}
			this.renderOpenGroup(groupName, side);
		} else {
			this.renderClosedGroup(groupName, side);
		}
	} 
	
	this.canvas.addBoxSpacing(13, side);
};

/**
 * If one side doesn't have much information, it doesn't look good to start from y=0.
 * In this case, add some spacing on top and redraw.
 */
Piwik_Transitions.prototype.reRenderIfNeededToCenter = function(side) {
	var height = (side == 'left' ? this.canvas.leftBoxPositionY : this.canvas.rightBoxPositionY) - 20;
	if (height < 460 && !this.reRendering) {
		var yOffset = (460 - height) / 2;
		this.canvas.clearSide(side);
		this.canvas.addBoxSpacing(yOffset, side);
		this.reRendering = true;
		side == 'left' ? this.renderLeftSide() : this.renderRightSide();
		this.reRendering = false;
	}
};

/** Render the center box with the main metrics */
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
	showMetric('PreviousPages', 'previousPagesNbTransitions');
	showMetric('SearchEngines', 'searchEnginesNbTransitions');
	showMetric('Websites', 'websitesNbTransitions');

	showMetric('FollowingPages', 'followingPagesNbTransitions');
	showMetric('Outlinks', 'outlinksNbTransitions');
	showMetric('Downloads', 'downloadsNbTransitions');
	showMetric('Exits', 'exits');
	showMetric('Bounces', 'bounces');

	box.find('.Transitions_CenterBoxMetrics').show();
};

/** Render the loops (i.e. page refreshes) */
Piwik_Transitions.prototype.renderLoops = function() {
	if (this.model.loops == 0) {
		return;
	}
	this.popover.find('.Transitions_Loops').html(this.model.loops);
	this.popover.find('.Transitions_LoopsPercentage').html(this.model.getPercentage('loops', true));
	this.popover.find('#Transitions_Loops').show();
	this.canvas.renderLoops(this.model.getPercentage('loops'));
};

Piwik_Transitions.prototype.renderEntries = function() {
	if (this.model.directEntries > 0) {
		this.canvas.renderBox({
			side: 'left',
			share: this.model.getPercentage('directEntries'),
			gradient: this.canvas.createHorizontalGradient('#CFEDCA', '#91DE83', 'left'),
			boxText: 'Direct entries',
			boxTextNumLines: 1,
			boxTextCssClass: 'SingleLine',
			smallBox: true
		});
		this.canvas.addBoxSpacing(20, 'left');
	}
};

Piwik_Transitions.prototype.renderExits = function() {
	if (this.model.exits > 0) {
		this.canvas.renderBox({
			side: 'right',
			share: this.model.getPercentage('exits'),
			gradient: this.canvas.createHorizontalGradient('#CFEDCA', '#91DE83', 'right'),
			boxText: 'Exits',
			boxTextNumLines: 1,
			boxTextCssClass: 'SingleLine',
			smallBox: true
		});
		this.canvas.addBoxSpacing(20, 'right');
	}
};

/** Render the open group with the detailed data */
Piwik_Transitions.prototype.renderOpenGroup = function(groupName, side) {
	var self = this;
	
	// get data from the model
	var nbTransitionsVarName = groupName + 'NbTransitions';
	var nbTransitions = self.model[nbTransitionsVarName];
	if (nbTransitions == 0) {
		return;
	}
	
	var totalShare = this.model.getPercentage(nbTransitionsVarName);
	var details = self.model.getDetailsForGroup(groupName);
	
	// prepare gradients
	var gradientItems = this.canvas.createHorizontalGradient('#E3DFD1', '#E8E4D5', side);
	var gradientOthers = this.canvas.createHorizontalGradient('#F5F3EB', '#E8E4D5', side);
	var gradientBackground = this.canvas.createHorizontalGradient('#FFFFFF', '#B0CAE8', side);
	
	// remember current offsets to reset them later for drawing the background
	var boxPositionBefore, curvePositionBefore;
	if (side == 'left') {
		boxPositionBefore = this.canvas.leftBoxPositionY;
		curvePositionBefore = this.canvas.leftCurvePositionY;
	} else {
		boxPositionBefore = this.canvas.rightBoxPositionY;
		curvePositionBefore = this.canvas.rightCurvePositionY;
	}
	
	// headline of the open group
	var titleX, titleClass;
	if (side == 'left') {
		titleX = this.canvas.leftBoxBeginX + 10;
		titleClass = 'BoxTextLeft';
	} else {
		titleX = this.canvas.rightBoxBeginX - 1;
		titleClass = 'BoxTextRight';
	}
	var groupTitle = self.model.getGroupTitle(groupName);
	this.canvas.renderText(groupTitle, titleX , boxPositionBefore + 11, [titleClass, 'TitleOfOpenGroup']);
	this.canvas.addBoxSpacing(34, side);
	
	// draw detail boxes
	for (var i = 0; i < details.length; i++) {
		var data = details[i];
		var label = (typeof data.url != 'undefined' ? data.url : data.label);
		var isOthers = (label == 'Others');
		var onClick = false;
		if (!isOthers && (groupName == 'previousPages' || groupName == 'followingPages')) {
			onClick = (function(url) {
				return function() { self.reloadPopover(url); };
			})(label);
		}
		
		var tooltip = Transitions_Translations.Transitions_XOfY;
		tooltip = '<b>' + tooltip.replace(/%s/, data.referrals + '</b>').replace(/%s/, nbTransitions);
		
		this.canvas.renderBox({
			side: side,
			share: data.percentage / 100 * totalShare,
			gradient: isOthers ? gradientOthers : gradientItems,
			boxText: label,
			boxTextNumLines: 3,
			curveText: data.percentage + '%',
			curveTextTooltip: tooltip,
			onClick: onClick
		});
	}
	
	// draw background
	var boxPositionAfter, curvePositionAfter;
	if (side == 'left') {
		boxPositionAfter = this.canvas.leftBoxPositionY;
		curvePositionAfter = this.canvas.leftCurvePositionY;
		this.canvas.leftBoxPositionY = boxPositionBefore;
		this.canvas.leftCurvePositionY = curvePositionBefore;
	} else {
		boxPositionAfter = this.canvas.rightBoxPositionY;
		curvePositionAfter = this.canvas.rightCurvePositionY;
		this.canvas.rightBoxPositionY = boxPositionBefore;
		this.canvas.rightCurvePositionY = curvePositionBefore;
	}
	
	this.canvas.renderBox({
		side: side,
		boxHeight: boxPositionAfter - boxPositionBefore - this.canvas.boxSpacing - 2,
		curveHeight: curvePositionAfter - curvePositionBefore - this.canvas.curveSpacing,
		gradient: gradientBackground,
		bgCanvas: true
	});
	
	this.canvas.addBoxSpacing(15, side);
};

/** Render a closed group without detailed data, only one box for the sum */
Piwik_Transitions.prototype.renderClosedGroup = function(groupName, side) {
	var self = this;
	var gradient = this.canvas.createHorizontalGradient('#DDE4ED', '#9BBADE', side);
	
	var nbTransitionsVarName = groupName + 'NbTransitions';
	
	if (self.model[nbTransitionsVarName] == 0) {
		return;
	}
	
	self.canvas.renderBox({
		side: side,
		share: self.model.getPercentage(nbTransitionsVarName),
		gradient: gradient,
		boxText: self.model.getGroupTitle(groupName),
		boxTextNumLines: 1,
		boxTextCssClass: 'SingleLine',
		boxIcon: 'themes/default/images/plus_blue.png',
		smallBox: true,
		onClick: function() {
			self.openGroup(side, groupName);
		}
	});
};

/** Reload the entire popover for a different URL */
Piwik_Transitions.prototype.reloadPopover = function(url) {
	this.popover.dialog('close');
	this.rowAction.openPopover(url);
};

/** Redraw the left or right sides with a different group opened */
Piwik_Transitions.prototype.openGroup = function(side, groupName) {
	
	this.canvas.clearSide(side);
	
	if (side == 'left') {
		this.leftOpenGroup = groupName;
		this.renderLeftSide();
	} else {
		this.rightOpenGroup = groupName;
		this.renderRightSide();
	}
	
	this.renderLoops();
};


// --------------------------------------
// CANVAS
// --------------------------------------

function Piwik_Transitions_Canvas(canvasDom, canvasBgDom, width, height) {
	if (!canvasDom.getContext) {
		alert('Your browser is not supported.');
		return;
	}

	/** DOM element that contains the canvas */
	this.container = $(canvasDom).parent();
	/** Drawing context of the canvas */
	this.context = canvasDom.getContext('2d');
	/** Drawing context of the background canvas */
	this.bgContext = canvasBgDom.getContext('2d');

	/** Width of the entire canvas */
	this.width = canvasDom.width = canvasBgDom.width = width;
	/** Height of the entire canvas */
	this.height = canvasDom.height = canvasBgDom.height = height;

	/** Current Y positions */
	this.leftBoxPositionY = this.originalBoxPositionY = 0;
	this.leftCurvePositionY = this.originalCurvePositionY = 110;
	this.rightBoxPositionY = this.originalBoxPositionY;
	this.rightCurvePositionY = this.originalCurvePositionY;

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
	this.curveSpacing = 1.5;

	/** The total net height (without curve spacing) of the curves as they connect to the center */
	this.totalHeightOfConnections = 205;

	/** X positions of the left box - begin means left, end means right */
	this.leftBoxBeginX = 0;
	this.leftCurveBeginX = this.leftBoxBeginX + this.boxWidth;
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
Piwik_Transitions_Canvas.prototype.renderText = function(text, x, y, cssClass, onClick, icon) {
	var div = this.addDomElement('div', 'Text');
	div.html('<span>' + Piwik_Transitions.prototype.addBreakpoints(text) + '</span>');
	div.css({
		left: x + 'px',
		top: y + 'px'
	});
	if (icon) {
		div.addClass('Transitions_HasBackground');
		div.css({backgroundImage: 'url(' + icon + ')'});
	}
	if (cssClass) {
		if (typeof cssClass == 'object') {
			for (var i = 0; i < cssClass.length; i++) {
				div.addClass('Transitions_' + cssClass[i]);
			}
		} else {
			div.addClass('Transitions_' + cssClass);
		}
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
 * Render a box.
 * This method automatically keeps track of the current position.
 *
 * PARAMS (pass as object):
 * side: left or right
 * share: of the box in the total amount of incoming transitions
 * gradient: for filling the box
 * boxText: to be placed inside the box (optional)
 * boxTextNumLines: the number of lines to be placed in the box (optional)
 * boxTextCssClass: for divs containing the texts (optional)
 * curveText: to be placed where the curve begins (optional)
 * curveTextTooltip: text for a tooltip that is shown when hovering the curve text (optional)
 * smallBox: use this.smallBoxHeight instead of this.boxHeight (optional)
 * boxIcon: path to an icon that is put in front of the text (optional)
 * onClick: click callback for the text in the box (optional)
 * 
 * Only used for background:
 * curveHeight: fix height in px instead of share
 * boxHeight: fix box height in px
 * bgCanvas: true to draw on background canvas
 */
Piwik_Transitions_Canvas.prototype.renderBox = function(params) {
	var curveHeight = params.curveHeight ? params.curveHeight :
			Math.round(this.totalHeightOfConnections * params.share);
	curveHeight = Math.max(curveHeight, 1);

	var boxHeight = this.boxHeight;
	if (params.smallBox)  boxHeight = this.smallBoxHeight;
	if (params.boxHeight) boxHeight = params.boxHeight;
	
	var context = params.bgCanvas ? this.bgContext : this.context;
	
	// background
	context.fillStyle = params.gradient;
	context.beginPath();
	if (params.side == 'left') {
		this.renderLeftBoxBg(context, boxHeight, curveHeight);
	} else {
		this.renderRightBoxBg(context, boxHeight, curveHeight);
	}
	if (typeof context.endPath == 'function') {
		context.endPath();
	}
	
	// text inside the box
	if (params.boxText) {
		var onClick = typeof params.onClick == 'function' ? params.onClick : false;
		var boxTextLeft, boxTextTop, el;
		if (params.side == 'left') {
			boxTextLeft = this.leftBoxBeginX + 10;
			boxTextTop = this.leftBoxPositionY + boxHeight / 2 - params.boxTextNumLines * this.lineHeight / 2;
			el = this.renderText(params.boxText, boxTextLeft, boxTextTop, 'BoxTextLeft', onClick, params.boxIcon);
		} else {
			boxTextLeft = this.rightBoxBeginX;
			boxTextTop = this.rightBoxPositionY + boxHeight / 2 - params.boxTextNumLines * this.lineHeight / 2;
			el = this.renderText(params.boxText, boxTextLeft, boxTextTop, 'BoxTextRight', onClick, params.boxIcon);
		}
		if (params.boxTextCssClass) {
			el.addClass('Transitions_' + params.boxTextCssClass);
		}
	}

	// text at the beginning of the curve
	if (params.curveText) {
		var curveTextLeft, curveTextTop;
		if (params.side == 'left') {
			curveTextLeft = this.leftBoxBeginX + this.boxWidth + 12;
			curveTextTop = this.leftBoxPositionY + boxHeight / 2 - this.lineHeight / 2;
		} else {
			curveTextLeft = this.rightBoxBeginX - 35;
			curveTextTop = this.rightBoxPositionY + boxHeight / 2 - this.lineHeight / 2;
		}
		var textDiv = this.renderText(params.curveText, curveTextLeft, curveTextTop,
			params.side == 'left' ? 'CurveTextLeft' : 'CurveTextRight');
		
		if (params.curveTextTooltip) {
			textDiv.hover(function() {
				Piwik_Tooltip.show(params.curveTextTooltip, 'Transitions_Tooltip_CurveText');
			}, function() {
				Piwik_Tooltip.hide();
			});
		}
	}
	
	if (params.side == 'left') {
		this.leftBoxPositionY += boxHeight + this.boxSpacing;
		this.leftCurvePositionY += curveHeight + this.curveSpacing;
	} else {
		this.rightBoxPositionY += boxHeight + this.boxSpacing;
		this.rightCurvePositionY += curveHeight + this.curveSpacing;
	}
};

Piwik_Transitions_Canvas.prototype.renderLeftBoxBg = function(context, boxHeight, curveHeight) {
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

	// the flow
	context.moveTo(leftUpper.x, leftUpper.y);
	context.bezierCurveTo(cp1Upper.x, cp1Upper.y, cp2Upper.x, cp2Upper.y, rightUpper.x, rightUpper.y);
	context.lineTo(rightLower.x, rightLower.y);
	context.bezierCurveTo(cp1Lower.x, cp1Lower.y, cp2Lower.x, cp2Lower.y, leftLower.x, leftLower.y);

	// the box
	context.lineTo(leftLower.x - this.boxWidth + 4, leftLower.y);
	context.lineTo(leftLower.x - this.boxWidth, leftUpper.y);
	context.lineTo(leftUpper.x, leftUpper.y);
	context.fill();
};

Piwik_Transitions_Canvas.prototype.renderRightBoxBg = function(context, boxHeight, curveHeight) {
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

	// the flow part 1
	context.moveTo(leftUpper.x, leftUpper.y);
	context.bezierCurveTo(cp1Upper.x, cp1Upper.y, cp2Upper.x, cp2Upper.y, rightUpper.x, rightUpper.y);

	// the box
	context.lineTo(rightUpper.x + this.boxWidth, rightUpper.y);
	context.lineTo(rightLower.x + this.boxWidth - 4, rightLower.y);
	context.lineTo(rightLower.x, rightLower.y);

	// the flow part 2
	context.bezierCurveTo(cp1Lower.x, cp1Lower.y, cp2Lower.x, cp2Lower.y, leftLower.x, leftLower.y);
	context.lineTo(leftUpper.x, leftUpper.y);
	context.fill();
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
	var dark = '#E3E1D8';
	gradient.addColorStop(0, dark);
	gradient.addColorStop(.5, light);
	gradient.addColorStop(1, dark);
	
	this.context.fillStyle = gradient;
	
	this.context.beginPath();
	
	// curve from the upper left connection to the center box to the lower left connection to the text box 
	var point1 = {x: this.leftCurveEndX, y: this.leftCurvePositionY};
	var point2 = {x: this.leftCurveEndX, y: 460};
	
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

/** Clear one side for redrawing */
Piwik_Transitions_Canvas.prototype.clearSide = function(side) {
	var x = (side == 'left' ? 0 : this.width / 2);
	var y = 0;
	var w = this.width / 2;
	var h = this.height;
	
	this.context.clearRect(x, y, w, h);
	this.bgContext.clearRect(x, y, w, h);
	
	if (side == 'left') {
		this.container.find('.Transitions_BoxTextLeft').remove();
		this.container.find('.Transitions_CurveTextLeft').remove();
		this.leftBoxPositionY = this.originalBoxPositionY;
		this.leftCurvePositionY = this.originalCurvePositionY;	
	} else {
		this.container.find('.Transitions_BoxTextRight').remove();
		this.container.find('.Transitions_CurveTextRight').remove();
		this.rightBoxPositionY = this.originalBoxPositionY;
		this.rightCurvePositionY = this.originalCurvePositionY;
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

	this.directEntries = 0;
	
	this.searchEnginesNbTransitions = 0;
	this.searchEngines = [];
	
	this.websitesNbTransitions = 0;
	this.websites = [];
	
	this.campaignsNbTransitions = 0;
	this.campaigns = [];

	this.previousPagesNbTransitions = 0;
	this.previousPages = [];
	
	this.followingPagesNbTransitions = 0;
	this.followingPages = [];
	
	this.downloadsNbTransitions = 0;
	this.downloads = [];
	
	this.outlinksNbTransitions = 0;
	this.outlinks = [];
	
	this.groupTitles = {
		previousPages: Transitions_Translations.Transitions_FromPreviousPages,
		followingPages: Transitions_Translations.Transitions_ToFollwoingPages,
		outlinks: Transitions_Translations.Transitions_ToOutlinks,
		downloads: Transitions_Translations.Transitions_ToDownloads
	};

	this.ajax.callApi('Transitions.getFullReport', {
			pageUrl: link,
			expanded: 1
		},
		function(report) {
			// load page metrics
			self.pageviews = report.pageMetrics.pageviews;
			self.exits = report.pageMetrics.exits;
			self.bounces = report.pageMetrics.bounces;
			self.loops = report.pageMetrics.loops;
			
			// load referrers: split direct entries and others
			for (var i = 0; i < report.referrers.length; i++) {
				var referrer = report.referrers[i];
				if (referrer.shortName == 'direct') {
					self.directEntries = referrer.visits;
				} else if (referrer.shortName == 'search') {
					self.searchEnginesNbTransitions = referrer.visits;
					self.searchEngines = referrer.details;
					self.groupTitles.searchEngines = referrer.label;
				} else if (referrer.shortName == 'website') {
					self.websitesNbTransitions = referrer.visits;
					self.websites = referrer.details;
					self.groupTitles.websites = referrer.label;
				} else if (referrer.shortName == 'campaign') {
					self.campaignsNbTransitions = referrer.visits;
					self.campaigns = referrer.details;
					self.groupTitles.campaigns = referrer.label;
				}
			}

			self.loadAndSumReport(report, 'previousPages');
			self.loadAndSumReport(report, 'followingPages');
			self.loadAndSumReport(report, 'downloads');
			self.loadAndSumReport(report, 'outlinks');

			callback();
		});
};

Piwik_Transitions_Model.prototype.loadAndSumReport = function(apiData, reportName) {
	var data = this[reportName] = apiData[reportName];
	var sumVarName = reportName + 'NbTransitions';
	
	this[sumVarName] = 0;
	for (var i = 0; i < data.length; i++) {
		this[sumVarName] += data[i].referrals;
	}
};

Piwik_Transitions_Model.prototype.getGroupTitle = function(groupName) {
	if (typeof this.groupTitles[groupName] != 'undefined') {
		return this.groupTitles[groupName];
	}
	return groupName;
};

Piwik_Transitions_Model.prototype.getDetailsForGroup = function(groupName) {
	return this.addPercentagesToData(this[groupName]);
};

Piwik_Transitions_Model.prototype.getPercentage = function(metric, formatted) {
	var percentage = (this.pageviews == 0 ? 0 : this[metric] / this.pageviews);

	if (formatted) {
		percentage = this.roundPercentage(percentage);
		percentage += '%';
	}

	return percentage;
};

Piwik_Transitions_Model.prototype.addPercentagesToData = function(data) {
	var total = 0;

	for (var i = 0; i < data.length; i++) {
		total += parseInt(data[i].referrals, 10);
	}

	for (i = 0; i < data.length; i++) {
		data[i].percentage = this.roundPercentage(data[i].referrals / total);
	}

	return data;
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
	if (params.period == 'range') {
		params.date = piwik.startDateString + ',' + params.date;
	}

	$.post('index.php', params, function(result) {
		if (typeof result.result != 'undefined' && result.result == 'error') {
			alert(result.message);
		} else {
			callback(result);
		}
	}, 'json');
};
