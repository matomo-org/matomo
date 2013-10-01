<html>
<body>
<p>Hello, world!</p>

<p>Today looks like an ideal day to write & run tests.</p>

<ul>
    <li><a href='lib/visualphpunit/'>Run unit & integration tests in the browser.</a> <br/><i>(If you're using apache,
            make sure the mod_rewrite module is enabled.)</i></li>
    <li><a href="javascript/">Run piwik.js Javascript unit & integration tests</a>. <br/><i>Note: the Javascript tests
            are not executed in Jenkins so must be run manually on major browsers after any change to piwik.js</i></li>
</ul>

<h4>Configuring VisualPHPUnit:</h4>

<p>You'll have to configure VisualPHPUnit before you can use it. To do so:
<ul>
    <li>In path-to-piwik/tests/lib/visualphpunit/app/config/bootstrap.php, set the value of 'pear_path' to the path
        where PEAR is located.
    </li>
    <li>If you want to use a phpunit.xml file, copy the phpunit.xml.dist file in
        path-to-piwik/tests/PHPUnit/phpunit.xml.dist & rename it to phpunit.xml. Then add the following to it:
	<pre>
		<code>
            &lt;listeners&gt;
            &lt;listener class=&quot;PHPUnit_Util_Log_JSON&quot;&gt;&lt;/listener&gt;
            &lt;/listeners&gt;
        </code>
	</pre>
    </li>
</ul>
</p>

<p>If you are new to the wonderful world of testing, <a href='https://github.com/piwik/piwik/blob/master/tests/README.md'>see the README</a> for an introduction.</p>

<img src='resources/disturbing-image.jpg' alt='I find your lack of tests disturbing'>
<br/><i><a href='http://www.flickr.com/photos/sebastian_bergmann/2282734669/'>Photo source & license</a></i>
</body>
</html>
