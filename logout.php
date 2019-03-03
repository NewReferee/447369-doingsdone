<?php 
require_once ('functions.php');
require_once ('init.php');

unset($_SESSION);
session_destroy();

header("Location: ./");
die ();
?>