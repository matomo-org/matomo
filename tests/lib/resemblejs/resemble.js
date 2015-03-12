/*
James Cryer / Huddle 2014
URL: https://github.com/Huddle/Resemble.js
*/

(function(_this){
	'use strict';

	var pixelTransparency = 1;

	var errorPixelColor = { // Color for Error Pixels. Between 0 and 255.
		red: 255,
		green: 0,
		blue: 255,
		alpha: 255
	};

	function colorsDistance(c1, c2){
		return (Math.abs(c1.r - c2.r) + Math.abs(c1.g - c2.g) + Math.abs(c1.b - c2.b))/3;
	}

	var errorPixelTransform = {
		flat : function (d1, d2){
			return {
				r: errorPixelColor.red,
				g: errorPixelColor.green,
				b: errorPixelColor.blue,
				a: errorPixelColor.alpha
			}
		},
		movement: function (d1, d2){
			return {
				r: ((d2.r*(errorPixelColor.red/255)) + errorPixelColor.red)/2,
				g: ((d2.g*(errorPixelColor.green/255)) + errorPixelColor.green)/2,
				b: ((d2.b*(errorPixelColor.blue/255)) + errorPixelColor.blue)/2,
				a: d2.a
			}
		},
		flatDifferenceIntensity: function (d1, d2){
			return {
				r: errorPixelColor.red,
				g: errorPixelColor.green,
				b: errorPixelColor.blue,
				a: colorsDistance(d1, d2)
			}
		},
		movementDifferenceIntensity: function (d1, d2){
			var ratio = colorsDistance(d1, d2)/255 * 0.8;
			return {
				r: ((1-ratio)*(d2.r*(errorPixelColor.red/255)) + ratio*errorPixelColor.red),
				g: ((1-ratio)*(d2.g*(errorPixelColor.green/255)) + ratio*errorPixelColor.green),
				b: ((1-ratio)*(d2.b*(errorPixelColor.blue/255)) + ratio*errorPixelColor.blue),
				a: d2.a
			}
		}
	};

	var errorPixelTransformer = errorPixelTransform.flat;

	var largeImageThreshold = 1200;
	
	var httpRegex = /^https?:\/\//;
	var document = typeof window != "undefined" ? window.document : {};
	var documentDomainRegex = new RegExp('^https?://' + document.domain);

	_this['resemble'] = function( fileData ){

		var data = {};
		var images = [];
		var updateCallbackArray = [];

		var tolerance = { // between 0 and 255
			red: 16,
			green: 16,
			blue: 16,
			alpha: 16,
			minBrightness: 16,
			maxBrightness: 240
		};

		var ignoreAntialiasing = false;
		var ignoreColors = false;

		function triggerDataUpdate(){
			var len = updateCallbackArray.length;
			var i;
			for(i=0;i<len;i++){
				if (typeof updateCallbackArray[i] === 'function'){
					updateCallbackArray[i](data);
				}
			}
		}

		function loop(x, y, callback){
			var i,j;

			for (i=0;i<x;i++){
				for (j=0;j<y;j++){
					callback(i, j);
				}
			}
		}

		function parseImage(sourceImageData, width, height){

			var pixelCount = 0;
			var redTotal = 0;
			var greenTotal = 0;
			var blueTotal = 0;
			var brightnessTotal = 0;

			loop(height, width, function(verticalPos, horizontalPos){
				var offset = (verticalPos*width + horizontalPos) * 4;
				var red = sourceImageData[offset];
				var green = sourceImageData[offset + 1];
				var blue = sourceImageData[offset + 2];
				var brightness = getBrightness(red,green,blue);

				pixelCount++;

				redTotal += red / 255 * 100;
				greenTotal += green / 255 * 100;
				blueTotal += blue / 255 * 100;
				brightnessTotal += brightness / 255 * 100;
			});

			data.red = Math.floor(redTotal / pixelCount);
			data.green = Math.floor(greenTotal / pixelCount);
			data.blue = Math.floor(blueTotal / pixelCount);
			data.brightness = Math.floor(brightnessTotal / pixelCount);

			triggerDataUpdate();
		}

		function loadImageData( fileData, callback ){
			var fileReader;
			var hiddenImage = new Image();
			
			if (httpRegex.test(fileData) && !documentDomainRegex.test(fileData)) {
				hiddenImage.setAttribute('crossorigin', 'anonymous');
			}

			hiddenImage.onload = function() {

				var hiddenCanvas =  document.createElement('canvas');
				var imageData;
				var width = hiddenImage.width;
				var height = hiddenImage.height;

				hiddenCanvas.width = width;
				hiddenCanvas.height = height;
				hiddenCanvas.getContext('2d').drawImage(hiddenImage, 0, 0, width, height);
				imageData = hiddenCanvas.getContext('2d').getImageData(0, 0, width, height);

				images.push(imageData);

				callback(imageData, width, height);
			};

			if (typeof fileData === 'string') {
				hiddenImage.src = fileData;
				if (hiddenImage.complete) {
					hiddenImage.onload();
				}
			} else if (typeof fileData.data !== 'undefined'
					&& typeof fileData.width === 'number'
					&& typeof fileData.height === 'number') {
				images.push(fileData);
				callback(fileData, fileData.width, fileData.height);
			} else {
				fileReader = new FileReader();
				fileReader.onload = function (event) {
					hiddenImage.src = event.target.result;
				};
				fileReader.readAsDataURL(fileData);
			}
		}

		function isColorSimilar(a, b, color){

			var absDiff = Math.abs(a - b);

			if(typeof a === 'undefined'){
				return false;
			}
			if(typeof b === 'undefined'){
				return false;
			}

			if(a === b){
				return true;
			} else if ( absDiff < tolerance[color] ) {
				return true;
			} else {
				return false;
			}
		}

		function isNumber(n) {
			return !isNaN(parseFloat(n));
		}

		function isPixelBrightnessSimilar(d1, d2){
			var alpha = isColorSimilar(d1.a, d2.a, 'alpha');
			var brightness = isColorSimilar(d1.brightness, d2.brightness, 'minBrightness');
			return brightness && alpha;
		}

		function getBrightness(r,g,b){
			return 0.3*r + 0.59*g + 0.11*b;
		}

		function isRGBSame(d1,d2){
			var red = d1.r === d2.r;
			var green = d1.g === d2.g;
			var blue = d1.b === d2.b;
			return red && green && blue;
		}

		function isRGBSimilar(d1, d2){
			var red = isColorSimilar(d1.r,d2.r,'red');
			var green = isColorSimilar(d1.g,d2.g,'green');
			var blue = isColorSimilar(d1.b,d2.b,'blue');
			var alpha = isColorSimilar(d1.a, d2.a, 'alpha');

			return red && green && blue && alpha;
		}

		function isContrasting(d1, d2){
			return Math.abs(d1.brightness - d2.brightness) > tolerance.maxBrightness;
		}

		function getHue(r,g,b){

			r = r / 255;
			g = g / 255;
			b = b / 255;
			var max = Math.max(r, g, b), min = Math.min(r, g, b);
			var h;
			var d;

			if (max == min){
				h = 0; // achromatic
			} else{
				d = max - min;
				switch(max){
					case r: h = (g - b) / d + (g < b ? 6 : 0); break;
					case g: h = (b - r) / d + 2; break;
					case b: h = (r - g) / d + 4; break;
				}
				h /= 6;
			}

			return h;
		}

		function isAntialiased(sourcePix, data, cacheSet, verticalPos, horizontalPos, width){
			var offset;
			var targetPix;
			var distance = 1;
			var i;
			var j;
			var hasHighContrastSibling = 0;
			var hasSiblingWithDifferentHue = 0;
			var hasEquivilantSibling = 0;

			addHueInfo(sourcePix);

			for (i = distance*-1; i <= distance; i++){
				for (j = distance*-1; j <= distance; j++){

					if(i===0 && j===0){
						// ignore source pixel
					} else {

						offset = ((verticalPos+j)*width + (horizontalPos+i)) * 4;
						targetPix = getPixelInfo(data, offset, cacheSet);

						if(targetPix === null){
							continue;
						}

						addBrightnessInfo(targetPix);
						addHueInfo(targetPix);

						if( isContrasting(sourcePix, targetPix) ){
							hasHighContrastSibling++;
						}

						if( isRGBSame(sourcePix,targetPix) ){
							hasEquivilantSibling++;
						}

						if( Math.abs(targetPix.h - sourcePix.h) > 0.3 ){
							hasSiblingWithDifferentHue++;
						}

						if( hasSiblingWithDifferentHue > 1 || hasHighContrastSibling > 1){
							return true;
						}
					}
				}
			}

			if(hasEquivilantSibling < 2){
				return true;
			}

			return false;
		}

		function errorPixel(px, offset, data1, data2){
			var data = errorPixelTransformer(data1, data2);
			px[offset] = data.r;
			px[offset + 1] = data.g;
			px[offset + 2] = data.b;
			px[offset + 3] = data.a;
		}

		function copyPixel(px, offset, data){
			px[offset] = data.r; //r
			px[offset + 1] = data.g; //g
			px[offset + 2] = data.b; //b
			px[offset + 3] = data.a * pixelTransparency; //a
		}

		function copyGrayScalePixel(px, offset, data){
			px[offset] = data.brightness; //r
			px[offset + 1] = data.brightness; //g
			px[offset + 2] = data.brightness; //b
			px[offset + 3] = data.a * pixelTransparency; //a
		}

		function getPixelInfo(data, offset, cacheSet){
			var r;
			var g;
			var b;
			var d;
			var a;

			r = data[offset];

			if(typeof r !== 'undefined'){
				g = data[offset+1];
				b = data[offset+2];
				a = data[offset+3];
				d = {
					r: r,
					g: g,
					b: b,
					a: a
				};

				return d;
			} else {
				return null;
			}
		}

		function addBrightnessInfo(data){
			data.brightness = getBrightness(data.r,data.g,data.b); // 'corrected' lightness
		}

		function addHueInfo(data){
			data.h = getHue(data.r,data.g,data.b);
		}

		function analyseImages(img1, img2, width, height){

			var hiddenCanvas = document.createElement('canvas');

			var data1 = img1.data;
			var data2 = img2.data;

			hiddenCanvas.width = width;
			hiddenCanvas.height = height;

			var context = hiddenCanvas.getContext('2d');
			var imgd = context.createImageData(width,height);
			var targetPix = imgd.data;

			var mismatchCount = 0;
			var diffBounds = {
				top: height,
				left: width,
				bottom: 0,
				right: 0
			};
			var updateBounds = function(x, y) {
				diffBounds.left = Math.min(x, diffBounds.left);
				diffBounds.right = Math.max(x, diffBounds.right);
				diffBounds.top = Math.min(y, diffBounds.top);
				diffBounds.bottom = Math.max(y, diffBounds.bottom);
			}

			var time = Date.now();

			var skip;

			if(!!largeImageThreshold && ignoreAntialiasing && (width > largeImageThreshold || height > largeImageThreshold)){
				skip = 6;
			}

			loop(height, width, function(verticalPos, horizontalPos){

				if(skip){ // only skip if the image isn't small
					if(verticalPos % skip === 0 || horizontalPos % skip === 0){
						return;
					}
				}

				var offset = (verticalPos*width + horizontalPos) * 4;
				var pixel1 = getPixelInfo(data1, offset, 1);
				var pixel2 = getPixelInfo(data2, offset, 2);

				if(pixel1 === null || pixel2 === null){
					return;
				}

				if (ignoreColors){

					addBrightnessInfo(pixel1);
					addBrightnessInfo(pixel2);

					if( isPixelBrightnessSimilar(pixel1, pixel2) ){
						copyGrayScalePixel(targetPix, offset, pixel2);
					} else {
						errorPixel(targetPix, offset, pixel1, pixel2);
						mismatchCount++;
						updateBounds(horizontalPos, verticalPos);
					}
					return;
				}

				if( isRGBSimilar(pixel1, pixel2) ){
					copyPixel(targetPix, offset, pixel1, pixel2);

				} else if( ignoreAntialiasing && (
						addBrightnessInfo(pixel1), // jit pixel info augmentation looks a little weird, sorry.
						addBrightnessInfo(pixel2),
						isAntialiased(pixel1, data1, 1, verticalPos, horizontalPos, width) ||
						isAntialiased(pixel2, data2, 2, verticalPos, horizontalPos, width)
					)){

					if( isPixelBrightnessSimilar(pixel1, pixel2) ){
						copyGrayScalePixel(targetPix, offset, pixel2);
					} else {
						errorPixel(targetPix, offset, pixel1, pixel2);
						mismatchCount++;
						updateBounds(horizontalPos, verticalPos);
					}
				} else {
					errorPixel(targetPix, offset, pixel1, pixel2);
					mismatchCount++;
					updateBounds(horizontalPos, verticalPos);
				}

			});

			data.misMatchPercentage = (mismatchCount / (height*width) * 100).toFixed(2);
			data.diffBounds = diffBounds;
			data.analysisTime = Date.now() - time;

			data.getImageDataUrl = function(text){
				var barHeight = 0;

				if(text){
					barHeight = addLabel(text,context,hiddenCanvas);
				}

				context.putImageData(imgd, 0, barHeight);

				return hiddenCanvas.toDataURL("image/png");
			};
		}

		function addLabel(text, context, hiddenCanvas){
			var textPadding = 2;

			context.font = '12px sans-serif';

			var textWidth = context.measureText(text).width + textPadding*2;
			var barHeight = 22;

			if(textWidth > hiddenCanvas.width){
				hiddenCanvas.width = textWidth;
			}

			hiddenCanvas.height += barHeight;

			context.fillStyle = "#666";
			context.fillRect(0,0,hiddenCanvas.width,barHeight -4);
			context.fillStyle = "#fff";
			context.fillRect(0,barHeight -4,hiddenCanvas.width, 4);

			context.fillStyle = "#fff";
			context.textBaseline = "top";
			context.font = '12px sans-serif';
			context.fillText(text, textPadding, 1);

			return barHeight;
		}

		function normalise(img, w, h){
			var c;
			var context;

			if(img.height < h || img.width < w){
				c = document.createElement('canvas');
				c.width = w;
				c.height = h;
				context = c.getContext('2d');
				context.putImageData(img, 0, 0);
				return context.getImageData(0, 0, w, h);
			}

			return img;
		}

		function compare(one, two){

			function onceWeHaveBoth(){
				var width;
				var height;
				if(images.length === 2){
					width = images[0].width > images[1].width ? images[0].width : images[1].width;
					height = images[0].height > images[1].height ? images[0].height : images[1].height;

					if( (images[0].width === images[1].width) && (images[0].height === images[1].height) ){
						data.isSameDimensions = true;
					} else {
						data.isSameDimensions = false;
					}

					data.dimensionDifference = { width: images[0].width - images[1].width, height: images[0].height - images[1].height };

					analyseImages( normalise(images[0],width, height), normalise(images[1],width, height), width, height);

					triggerDataUpdate();
				}
			}

			images = [];
			loadImageData(one, onceWeHaveBoth);
			loadImageData(two, onceWeHaveBoth);
		}

		function getCompareApi(param){

			var secondFileData,
				hasMethod = typeof param === 'function';

			if( !hasMethod ){
				// assume it's file data
				secondFileData = param;
			}

			var self = {
				ignoreNothing: function(){

					tolerance.red = 16;
					tolerance.green = 16;
					tolerance.blue = 16;
					tolerance.alpha = 16;
					tolerance.minBrightness = 16;
					tolerance.maxBrightness = 240;

					ignoreAntialiasing = false;
					ignoreColors = false;

					if(hasMethod) { param(); }
					return self;
				},
				ignoreAntialiasing: function(){

					tolerance.red = 32;
					tolerance.green = 32;
					tolerance.blue = 32;
					tolerance.alpha = 32;
					tolerance.minBrightness = 64;
					tolerance.maxBrightness = 96;

					ignoreAntialiasing = true;
					ignoreColors = false;

					if(hasMethod) { param(); }
					return self;
				},
				ignoreColors: function(){

					tolerance.alpha = 16;
					tolerance.minBrightness = 16;
					tolerance.maxBrightness = 240;

					ignoreAntialiasing = false;
					ignoreColors = true;

					if(hasMethod) { param(); }
					return self;
				},
				repaint: function(){
					if(hasMethod) { param(); }
					return self;
				},
				onComplete: function( callback ){

					updateCallbackArray.push(callback);

					var wrapper = function(){
						compare(fileData, secondFileData);
					};

					wrapper();

					return getCompareApi(wrapper);
				}
			};

			return self;
		}

		return {
			onComplete: function( callback ){
				updateCallbackArray.push(callback);
				loadImageData(fileData, function(imageData, width, height){
					parseImage(imageData.data, width, height);
				});
			},
			compareTo: function(secondFileData){
				return getCompareApi(secondFileData);
			}
		};

	};

	_this['resemble'].outputSettings = function(options){
		var key;
		var undefined;

		if(options.errorColor){
			for (key in options.errorColor) {
				errorPixelColor[key] = options.errorColor[key] === undefined ? errorPixelColor[key] : options.errorColor[key];
			}
		}

		if(options.errorType && errorPixelTransform[options.errorType] ){
			errorPixelTransformer = errorPixelTransform[options.errorType];
		}

		pixelTransparency = isNaN(Number(options.transparency)) ? pixelTransparency : options.transparency;

		if (options.largeImageThreshold !== undefined) {
			largeImageThreshold = options.largeImageThreshold;
		}

		return this;
	};

}(this));
