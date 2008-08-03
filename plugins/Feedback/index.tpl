{literal}
<style>
input, textarea, p {
	font-family: Georgia,"Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:0.9em;
	padding:0.2em;
}
input {
	margin-top:0.8em;
}
</style>
{/literal}

<form method="post" action="?module=Feedback&action=sendFeedback">

<p><strong>your email	:</strong>
<br /><input type="text" name="email" size="47" /></p>
<p><strong>your feedback:</strong><br/>
<i>please be precise if you request for a feature or report a bug</i></p>
<textarea name="body" cols="45" rows="10"></textarea>
<input type="submit" value="Send feedback" />
</form>
