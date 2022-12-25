<?php 
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'root';
$DATABASE_NAME = 'forum';	

if ($reaction == "likes")
			$sql = "UPDATE `posts` SET `likes` = '$count' WHERE `posts` . `id` = '$id'";
header("Location: index.html);

?>