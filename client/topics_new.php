<?php
if(count($_POST))
{
	require 'galaxy/Galaxy.php';
	require 'galaxy/models/forum/GalaxyForumTopic.php';

	$key         = 'c49b1e9c75c8e2dce99d602ae4bb1019';
	$secret      = 'aacc9ff43235f2ad1ff067ba3f3069c9';

	$options  = array('id'     =>'com.galaxy.community',
	                  'key'    => $key,
	                  'secret' => $secret,
	                  'type'   => Galaxy::kApplicationForum,
	                  'format' => Galaxy::kFormatJSON);
	
	$galaxy   = Galaxy::applicationWithOptions($options);
	$topic    = GalaxyForumTopic::topicWithSubjectAndMessage($_POST['inputSubject'], $_POST['inputMessage']);
	$galaxy->topics_new($topic, $_POST['inputChannel']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>topics_new</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Adam Venturella">
	<!-- Date: 2009-12-31 -->
</head>
<body>
<div><a href="/topics/<?php echo $_GET['id'] ?>">&laquo; Back</a></div>
<form action="/topics/new/<?php echo $_GET['id'] ?>" method="post" accept-charset="utf-8">
	<div><label for="inputSubject">Subject:</label><input type="text" name="inputSubject" value="" id="inputSubject" size="40"></div>
	<div><textarea name="inputMessage" id="inputMessage" cols="90" rows="20"></textarea></div>
	<input type="hidden" name="inputChannel" value="<?php echo $_GET['id'] ?>" id="inputChannel">
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
</body>
</html>
