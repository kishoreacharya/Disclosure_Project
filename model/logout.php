<?php

session_destroy();
$accesstype = "login";
$postParams = array("accesstype" => $accesstype);

require_once("formSubmission.php");
die();
?>
