<?php
$comments = $_POST['comments'];
$name = $_POST['name'];
$file = $_POST['file'];
$selfurl = $_POST['url'];
$question = $_POST['question'];
$answer = $_POST['answer'];

$human = 0;
$question = trim(strtoupper($question));

// First, did the person answer the question right
if ($answer == 1) {
	// Question 1 - Answer is earth
	if (strcmp($question,"EARTH") == 0) {
		// Right
		$human = 1;
	} else {
		// Wrong dont post
		$human = 0;
	}
	
}

if ($human == 1) {
	$ourFileName = $file . ".comments.html";
	$comments = stripslashes($comments);
	$comments = htmlspecialchars($comments, ENT_COMPAT, 'UTF-8');
	$comments = str_replace("&amp;hellip;", "&hellip;", $comments);
    // Make URLs clickable
	$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
	$comments = preg_replace($url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $comments);
	
	if (file_exists($ourFileName)) { 
		$fh = fopen($ourFileName, 'a'); 
	} else {
		$fh = fopen($ourFileName, 'w') or die("can't open file");
	}
		
	fwrite($fh, "<tr><td bgcolor=#CCCCCC>On: " . date("m/d/y G:H"));
	fwrite($fh, "<b>" . $name . "</b> said: </td></tr><tr><td><pre>");
	
	fwrite($fh, $comments . "</pre></td></tr><p><p>");
	mail('chrisethompson@gmail.com', $ourFileName, $name . " said " . $comments);
	fclose($fh);
} else {
        // write nothing
        // email nothing	
}

header ("location: " . $selfurl);

?>
