<?php
require 'application/Application.php';
$galaxy   = Application::galaxyForum();
$channels = json_decode($galaxy->forum_list(), true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Forums</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Adam Venturella">
	<!-- Date: 2009-12-31 -->
</head>
<body>
	<h3>Forums</h3>
	<?php foreach($channels as $channel): ?>
	<div style="padding-bottom:10px">
		<div><a href="/topics/<?php echo $channel['id'] ?>"><?php echo $channel['label'] ?></a></div>
		<div style="font-size:11px; font-style:italic;"><?php echo $channel['description'] ?></div>
	</div>
	<?php endforeach;?>
</body>
</html>