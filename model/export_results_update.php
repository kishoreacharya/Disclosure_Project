<?php

include_once MODEL_PATH . 'Authenticate.php';

#Default Report Functions
require CLASS_PATH . 'class.aV.main.DBGrid.php';
$objDBGrid = new DBGrid();

require CLASS_PATH . 'class.reports.php';
$objReports = new Reports();


$form=$_REQUEST;

$fromDateSpecified    = date("d/m/y",  strtotime("-1 month",time()));
$fromdate = $form["fromdate"];
$todate = $form["todate"];

if(empty($fromdate))
    $fromdate=$fromDateSpecified;
if(empty($todate))
    $todate=date("d/m/Y");
 $fromdate = $objReports->convertToTimestamp1($fromdate);
 $todate = $objReports->convertToTimestamp2($todate);

$objReports->setExportedResult($orgId,$fromdate,$todate,$username);
exit;
?>
