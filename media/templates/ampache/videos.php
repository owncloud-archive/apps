<?php
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
?>
<root>
<?php foreach ($_['videos'] as $video): ?>
	<video id='<?php echo $video['id'];?>'>
		<title><?php echo $video['name'];?></title>
		<mime><?php echo $video['mime'];?></mime>
		<resolution><?php echo $video['resolution'];?></resolution>
		<size><?php echo $video['size'];?></size>
		<url><?php echo $video['url'];?></url>
	</video>
<?php endforeach;?>
</root>
