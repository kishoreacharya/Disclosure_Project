<?php
include_once MODEL_PATH . 'Authenticate.php';
require CLASS_PATH.'class.deactivateUser.php';
//  Languages For status
require LANGUAGE_PATH . 'lang.common.php';
include_once MODEL_PATH . 'langConstraints.php';
require CLASS_PATH . 'mail.htmlMime.inc.php';
$objDeactivatedUser = new DeactivateUser();


$flag         = $_POST["flag"];
$selUsers     = $_POST["selUsers"];

$strResult = "Please try again."; 
if(!empty($selUsers))
{
    $strResult = $objDeactivatedUser->updateDeactivatedUser($flag, $selUsers, $username,$arrLangData);
}

echo $strResult;



?>
