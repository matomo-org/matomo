<form method="post" action="?module=Feedback&action=sendFeedback">

<p><strong>Your name:</strong>
<br /><input type="text" name="name" size="50" /></p>

<p><strong>Your e-mail:</strong>
<br /><input type="text" name="email" size="50" /></p>

<p><strong>Choose category:</strong>
<br /><select name="category">
	<option value="bug">Bug report</option>
	<option value="feature">Feature missing</option>
</select>
</p>

<p><strong>Topic:</strong>
<br /><input type="text" name="topic" size="50" /></p>

<p><strong>Body:</strong>
<br /><textarea name="body" cols="50" rows="15"></textarea></p>

<p><input type="submit" value="Send feedback" />

</form>
