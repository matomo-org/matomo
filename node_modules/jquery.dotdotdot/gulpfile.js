// npm install gulp-uglify gulp-rename gulp-umd --save-dev

var gulp 			= require( 'gulp' ),
	uglify 			= require( 'gulp-uglify' ),
	rename 			= require( 'gulp-rename' ),
	umd				= require( 'gulp-umd' );



//	Default task 'gulp': Runs JS tasks
gulp.task( 'default', function() {
    gulp.start( 'js' );
});



//	Watch task 'gulp watch': Starts a watch on JS tasks
gulp.task( 'watch', function() {
  gulp.watch( 'src/*.js', [ 'js' ] );
});



//	JS task 'gulp js': Runs all JS tasks
gulp.task( 'js', function() {
	return gulp.src( 'src/jquery.dotdotdot.js' )
//		.pipe( jshint('.jshintrc') )
//		.pipe( jshint.reporter( 'default' ) )
		.pipe( uglify({ preserveComments: 'license' }) )
		.pipe( rename({ suffix: '.min' }) )
		.pipe( gulp.dest( 'src' ) )
		.pipe( umd({
			dependencies: function() { return [ 'jQuery' ]; },
			exports: function() { return true; },
			namespace: sanitizeNamespaceForUmd
		}))
		.pipe( rename({ suffix: '.umd' }) )
		.pipe( gulp.dest( 'src' ) );
});

function sanitizeNamespaceForUmd( file ) {
	path = file.path.split( '\\' ).join( '/' ).split( '/' );
	path = path[ path.length - 1 ];
	return path.split( '.' ).join( '_' );
}

