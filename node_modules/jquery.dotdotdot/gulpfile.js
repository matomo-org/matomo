// npm install

var gulp 	= require( 'gulp' ),
	uglify 	= require( 'gulp-uglify' ),
	umd		= require( 'gulp-umd' );



//	Default task 'gulp': Runs JS tasks
gulp.task( 'default', function(){
    return runJSTasks();
});



//	Watch task 'gulp watch': Starts a watch on JS tasks
gulp.task( 'watch', function() {
  gulp.watch( 'src/*.js', [ 'js' ] );
});



//	JS task 'gulp js': Runs all JS tasks
gulp.task( 'js', function() {
	return runJSTasks();
});

function runJSTasks(){
    return gulp.src( 'src/jquery.dotdotdot.js' )
        .pipe( uglify({ output : {
        	comments: '/License/'
            }
        }) )
        .pipe( umd({
            dependencies: function() { return [ {
                name 	: 'jquery',
                global 	: 'jQuery',
                param 	: 'jQuery'
            } ]; },
            exports: function() { return true; },
            namespace: sanitizeNamespaceForUmd
        }))
        .pipe( gulp.dest( 'dist' ) );
}

function sanitizeNamespaceForUmd( file ) {
	path = file.path.split( '\\' ).join( '/' ).split( '/' );
	path = path[ path.length - 1 ];
	return path.split( '.' ).join( '_' );
}

