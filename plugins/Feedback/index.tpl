{literal}
<style>
input, textarea, p {
	font-family: Georgia,"Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	font-size:0.9em;
	padding:0.2em;
}
</style>
{/literal}

<form method="post" action="?module=Feedback&action=sendFeedback">

<p><strong>Your e-mail:</strong>
<br /><input type="text" name="email" size="40" /></p> 

<p><strong>Body:</strong>
<br /><textarea name="body" cols="40" rows="15"></textarea></p>

<p><input type="submit" value="Send feedback" />

</form>
