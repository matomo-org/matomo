/*
 *	jQuery dotdotdot 3.2.3
 *	@requires jQuery 1.7.0 or later
 *
 *	dotdotdot.frebsite.nl
 *
 *	Copyright (c) Fred Heusschen
 *	www.frebsite.nl
 *
 *	License: CC-BY-NC-4.0
 *	http://creativecommons.org/licenses/by-nc/4.0/
 */

(function( $ ) {
	'use strict';
	
	var _PLUGIN_    = 'dotdotdot';
	var _VERSION_   = '3.2.3';

	if ( $[ _PLUGIN_ ] && $[ _PLUGIN_ ].version > _VERSION_ )
	{
		return;
	}



	/*
		The class
	*/
	$[ _PLUGIN_ ] = function( $container, opts )
	{
		this.$dot 	= $container;
		this.api	= [ 'getInstance', 'truncate', 'restore', 'destroy', 'watch', 'unwatch' ];
		this.opts	= opts;

		var oldAPI = this.$dot.data( _PLUGIN_ );
		if ( oldAPI )
		{
			oldAPI.destroy();
		}

		this.init();
		this.truncate();

		if ( this.opts.watch )
		{
			this.watch();
		}

		return this;
	};

	$[ _PLUGIN_ ].version 	= _VERSION_;
	$[ _PLUGIN_ ].uniqueId 	= 0;

	$[ _PLUGIN_ ].defaults  = {
		ellipsis		: '\u2026 ',
		callback		: function( isTruncated ) {},
		truncate 		: 'word',
		tolerance		: 0,
		keep			: null,
		watch			: 'window',
		height 			: null
	};


	$[ _PLUGIN_ ].prototype = {

		init: function()
		{
			this.watchTimeout		= null;
			this.watchInterval		= null;
			this.uniqueId 			= $[ _PLUGIN_ ].uniqueId++;
			this.originalStyle		= this.$dot.attr( 'style' ) || '';
			this.originalContent 	= this._getOriginalContent();
			
			if ( this.$dot.css( 'word-wrap' ) !== 'break-word' )
			{
				this.$dot.css( 'word-wrap', 'break-word' );
			}
			if ( this.$dot.css( 'white-space' ) === 'nowrap' )
			{
				this.$dot.css( 'white-space', 'normal' );
			}

			if ( this.opts.height === null )
			{
				this.opts.height = this._getMaxHeight();
			}

			if ( typeof this.opts.ellipsis == 'string' )
			{
				this.opts.ellipsis = document.createTextNode( this.opts.ellipsis );
			}
		},

		getInstance: function()
		{
			return this;
		},

		truncate: function()
		{
			var that = this;


			//	Add inner node for measuring the height
			this.$inner = this.$dot
				.wrapInner( '<div />' )
				.children()
				.css({
					'display'	: 'block',
					'height'	: 'auto',
					'width'		: 'auto',
					'border'	: 'none',
					'padding'	: 0,
					'margin'	: 0
				});


			//	Set original content
			this.$inner
				.empty()
				.append( this.originalContent.clone( true ) );


			//	Get max height
			this.maxHeight = this._getMaxHeight();


			//	Truncate the text
			var isTruncated = false;
			if ( !this._fits() )
			{
				isTruncated = true;
				this._truncateToNode( this.$inner[ 0 ] );
			}

			this.$dot[ isTruncated ? 'addClass' : 'removeClass' ]( _c.truncated );


			//	Remove inner node
			this.$inner.replaceWith( this.$inner.contents() );
			this.$inner = null;


			//	 Callback
			this.opts.callback.call( this.$dot[ 0 ], isTruncated );

			return isTruncated;
		},

		restore: function()
		{
			this.unwatch();

			this.$dot
				.empty()
				.append( this.originalContent )
				.attr( 'style', this.originalStyle )
				.removeClass( _c.truncated );
		},

		destroy: function()
		{
			this.restore();
			this.$dot.data( _PLUGIN_, null );
		},

		watch: function()
		{
			var that = this;

			this.unwatch();

			var oldSizes = {};

			if ( this.opts.watch == 'window' )
			{
				$wndw.on(
					_e.resize + that.uniqueId,
					function( e )
					{
						if ( that.watchTimeout )
						{
							clearTimeout( that.watchTimeout );
						}
						that.watchTimeout = setTimeout(
							function() {

								oldSizes = that._watchSizes( oldSizes, $wndw, 'width', 'height' );

							}, 100
						);
					}
				);

			}
			else
			{
				this.watchInterval = setInterval(
					function()
					{
						oldSizes = that._watchSizes( oldSizes, that.$dot, 'innerWidth', 'innerHeight' );

					}, 500
				);
			}
		},

		unwatch: function()
		{
			$wndw.off( _e.resize + this.uniqueId );

			if ( this.watchInterval )
			{
				clearInterval( this.watchInterval );
			}

			if ( this.watchTimeout )
			{
				clearTimeout( this.watchTimeout );
			}
		},

		_api: function()
		{
			var that = this,
				api = {};

			$.each( this.api,
				function( i )
				{
					var fn = this;
					api[ fn ] = function()
					{
						var re = that[ fn ].apply( that, arguments );
						return ( typeof re == 'undefined' ) ? api : re;
					};
				}
			);
			return api;
		},

		_truncateToNode: function( _elem )
		{

			var that = this;

			var _coms = [],
				_elms = [];

			//	Empty the node 
			//		-> replace all contents with comments
			$(_elem)
				.contents()
				.each(
					function()
					{
						var $e = $(this);
						if ( !$e.hasClass( _c.keep ) )
						{
							var c = document.createComment( '' );
							$e.replaceWith( c );

							_elms.push( this );
							_coms.push( c );
						}
					}
				);

			if ( !_elms.length )
			{
				return;
			}

			//	Re-fill the node 
			//		-> replace comments with contents until it doesn't fit anymore
			for ( var e = 0; e < _elms.length; e++ )
			{

				$(_coms[ e ]).replaceWith( _elms[ e ] );

				$(_elms[ e ]).append( this.opts.ellipsis );
				var fits = this._fits();
				$(this.opts.ellipsis, _elms[ e ]).remove();

				if ( !fits )
				{
					if ( this.opts.truncate == 'node' && e > 1 )
					{
						$(_elms[ e - 2 ]).remove();
						return;
					}
					break;
				}
			}

			//	Remove left over comments
			for ( var c = e; c < _coms.length; c++ )
			{
				$(_coms[ c ]).remove();
			}

			//	Get last node 
			//		-> the node that overflows

			var _last = _elms[ Math.max( 0, Math.min( e, _elms.length - 1 ) ) ];

			//	Border case
			//		-> the last node with only an ellipsis in it...
			if ( _last.nodeType == 1 )
			{

				var $e = $('<' + _last.nodeName + ' />');
				$e.append( this.opts.ellipsis );

				$(_last).replaceWith( $e );

				//	... fits
				//		-> Restore the full last node
				if ( this._fits() )
				{
					$e.replaceWith( _last );
				}

				//	... doesn't fit
				//		-> remove it and go back one node
				else
				{
					$e.remove();
					_last = _elms[ Math.max( 0, e - 1 ) ];
				}
			}

			//	Proceed inside last node
			if ( _last.nodeType == 1 )
			{
				this._truncateToNode( _last );
			}
			else
			{
				this._truncateToWord( _last );
			}
		},

		_truncateToWord: function( _elem )
		{

			var e = _elem;

			var that = this;

			var txt = this.__getTextContent( e ),
				sep = ( txt.indexOf( ' ' ) !== -1 ) ? ' ' : '\u3000',
				arr = txt.split( sep ),
				str = '';

			for ( var a = arr.length; a >= 0; a-- )
			{
				str = arr.slice( 0, a ).join( sep );

				that.__setTextContent( e, that._addEllipsis( str ) );

				if ( that._fits() )
				{
					if ( that.opts.truncate == 'letter' )
					{
						that.__setTextContent( e, arr.slice( 0, a + 1 ).join( sep ) );
						that._truncateToLetter( e );
					}
					break;
				}
			}
		},

		_truncateToLetter: function( e )
		{
			var that = this;

			var txt = this.__getTextContent( e ),
				arr = txt.split( '' ),
				str = '';

			for ( var a = arr.length; a >= 0; a-- )
			{
				str = arr.slice( 0, a ).join( '' );

				if ( !str.length )
				{
					continue;
				}

				that.__setTextContent( e, that._addEllipsis( str ) );

				if ( that._fits() )
				{
					break;
				}
			}
		},

		_fits: function()
		{
			return ( this.$inner.innerHeight() <= this.maxHeight + this.opts.tolerance );
		},

		_addEllipsis: function( txt )
		{
			var remove = [' ', '\u3000', ',', ';', '.', '!', '?'];

			while ( $.inArray( txt.slice( -1 ), remove ) > -1 )
			{
				txt = txt.slice( 0, -1 );
			}
			txt += this.__getTextContent( this.opts.ellipsis );

			return txt;
		},

		_getOriginalContent: function()
		{
			var that = this;

			//	Add "keep" class to nodes to keep
			this.$dot
				.find( 'script, style' )
				.addClass( _c.keep );

			if ( this.opts.keep )
			{
				this.$dot
					.find( this.opts.keep )
					.addClass( _c.keep );
			}

			//	Filter out unneeded whitespace
			this.$dot
				.find( '*' )
				.not( '.' + _c.keep )
				.add( this.$dot )
				.contents()
				.each(
					function()
					{

						var e = this,
							$e = $(this);

						//	Text nodes
						if ( e.nodeType == 3 )
						{

							//	Remove whitespace where it does not take up space in the DOM
							if ( $.trim( that.__getTextContent( e ) ) == '' )
							{
								if ( $e.parent().is( 'table, thead, tbody, tfoot, tr, dl, ul, ol, video' ) )
								{
									$e.remove();
									return;
								}
								if ( $e.prev().is( 'div, p, table, td, td, dt, dd, li' ) )
								{
									$e.remove();
									return;
								}
								if ( $e.next().is( 'div, p, table, td, td, dt, dd, li' ) )
								{
									$e.remove();
									return;
								}
								if ( !$e.prev().length )
								{
									$e.remove();
									return;
								}
								if ( !$e.next().length )
								{
									$e.remove();
									return;
								}
							}
						}

						//	Comment nodes
						else if ( e.nodeType == 8 )
						{
							$e.remove();
						}

					}
				);

			return this.$dot.contents();
		},

		_getMaxHeight: function()
		{
			if ( typeof this.opts.height == 'number' )
			{
				return this.opts.height;
			}

			//	Find smallest CSS height
			var arr = [ 'maxHeight', 'height' ],
				hgh = 0;
 
			for ( var a = 0; a < arr.length; a++ )
			{
				hgh = window.getComputedStyle( this.$dot[ 0 ] )[ arr[ a ] ];
				if ( hgh.slice( -2 ) == 'px' )
				{
					hgh = parseFloat( hgh );
					break;
				}
			}

			//	Remove padding-top/bottom when needed.
			var arr = [];
			switch ( this.$dot.css( 'boxSizing' ) )
			{
				case 'border-box':
					arr.push( 'borderTopWidth' );
					arr.push( 'borderBottomWidth' );
					//	no break -> padding needs to be added too

				case 'padding-box':
					arr.push( 'paddingTop' );
					arr.push( 'paddingBottom' );
					break;
			}
			for ( var a = 0; a < arr.length; a++ )
			{
				var p = window.getComputedStyle( this.$dot[ 0 ] )[ arr[ a ] ];
				if ( p.slice( -2 ) == 'px' )
				{
					hgh -= parseFloat( p );
				}
			}

			//	Sanitize
			return Math.max( hgh, 0 );
		},

		_watchSizes: function( oldSizes, $elem, width, height )
		{
			if ( this.$dot.is( ':visible' ) )
			{
				var newSizes = {
					'width'		: $elem[ width  ](),
					'height'	: $elem[ height ]()
				};

				if ( oldSizes.width != newSizes.width || oldSizes.height != newSizes.height )
				{
					this.truncate();
				}

				return newSizes;
			}
			return oldSizes;
		},

		__getTextContent: function( elem )
		{
			var arr = [ 'nodeValue', 'textContent', 'innerText' ];
			for ( var a = 0; a < arr.length; a++ )
			{
				if ( typeof elem[ arr[ a ] ] == 'string' )
				{
					return elem[ arr[ a ] ];
				}
			}
			return '';
		},
		__setTextContent: function( elem, content )
		{
			var arr = [ 'nodeValue', 'textContent', 'innerText' ];
			for ( var a = 0; a < arr.length; a++ )
			{
				elem[ arr[ a ] ] = content;
			}
		}
	};



	/*
		The jQuery plugin
	*/
	$.fn[ _PLUGIN_ ] = function( opts )
	{
		initPlugin();

		opts = $.extend( true, {}, $[ _PLUGIN_ ].defaults, opts );

		return this.each(
			function()
			{
				$(this).data( _PLUGIN_, new $[ _PLUGIN_ ]( $(this), opts )._api() );
			}
		);
	};



	/*
		Global variables
	*/
	var _c, _d, _e, $wndw;

	function initPlugin()
	{
		$wndw = $(window);

		//	Classnames, Datanames, Eventnames
		_c = {};
		_d = {};
		_e = {};

		$.each( [ _c, _d, _e ],
			function( i, o )
			{
				o.add = function( a )
				{
					a = a.split( ' ' );
					for ( var b = 0, l = a.length; b < l; b++ )
					{
						o[ a[ b ] ] = o.ddd( a[ b ] );
					}
				};
			}
		);

		//	Classnames
		_c.ddd = function( c ) { return 'ddd-' + c; };
		_c.add( 'truncated keep' );

		//	Datanames
		_d.ddd = function( d ) { return 'ddd-' + d; };

		//	Eventnames
		_e.ddd = function( e ) { return e + '.ddd'; };
		_e.add( 'resize' );


		//	Only once
		initPlugin = function() {};

	}


})( jQuery );
