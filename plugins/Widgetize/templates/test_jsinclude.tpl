<html>
<body>
<h2>Test getCountry table in a JS include</h2>

<script type="text/javascript" src="{$url1}"></script>
<noscript>Powered by <a href="http://piwik.org">Piwik</a></div></noscript>

{literal}
<style>
table.dataTable td {
	background-color:red;
}
</style>
{/literal}
<p>This test is after the JS INCLUDE</p>


<h2>Test tag cloud in a JS include</h2>

<script type="text/javascript" src="{$url2}"></script>
<noscript>Powered by <a href="http://piwik.org">Piwik</a></div></noscript>

<p>This test is after the JS INCLUDE</p>

</body>
</html>