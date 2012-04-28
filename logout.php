<?
$logout=true;
include_once("session.php");
session_destroy();
header("Location:index.php");
?>
