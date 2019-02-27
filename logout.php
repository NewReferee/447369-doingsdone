<?php 
require_once ('functions.php');
require_once ('config.php');

session_destroy();

header("Location: ./");
die ();
?>