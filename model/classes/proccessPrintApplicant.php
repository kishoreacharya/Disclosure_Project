<?php
include_once MODEL_PATH . 'Authenticate.php';
require  CLASS_PATH.'class.printApplicant.php';
require CLASS_PATH . 'mail.htmlMime.inc.php';

if(isset($_POST['appId']))
{
    $objPrintApplicant = new PrintApplicant();
    $objPrintApplicant->setPrintedCorrectly($_POST['appId']);
    $objPrintApplicant->sendMailToApplicant($_POST['appId']);
    echo 'Success';
}







?>
