<?php
$currentFile = $_SERVER["PHP_SELF"];
$parts = Explode('/', $currentFile);
$file = $parts[count($parts) - 1];
?>
<b>Enter Comments Here:</b>
<form method="post" action="Comment.php">
<table>
<tr>
<td colspan="2">
<textarea rows="3" cols="80" wrap="physical" name="comments"> </textarea>
<input type="hidden" name="file" value="<?php echo $file ?>" /><br>
<tr valign=top>
<td>
<b>Name / Ground station name: </b>
<textarea rows="1" cols="30" wrap="physical" name="name"> </textarea>
<input style="border:10px solid white;" type="submit" value="Submit">
</td>
<td>
The third planet from the sun is called what?
<textarea rows="1" cols="30" wrap="physical" name="question"> </textarea><br>
<b>Answer this question to help prevent spam: </b><br>
(one word, not case sensitive)
<input type="hidden" name="answer" value="1" /><br>

</td>
</tr>
</table>
</form>
<h2>Comments on this Image </h2>
<table width="800" cellspacing=20>
<?php
	if (file_exists($file . "comments.html")) { 
		include ($file . "comments.html");
	} else {
		echo ("No comments so far.");
	}
?>
</table>

