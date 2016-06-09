<?php
include_once MODEL_PATH . 'Authenticate.php';
foreach($_REQUEST as $key => $val){
    $$key = $this->common->correctstring($val);
}

/*if( SERVERSIDE_VALIDATION ) {
    include_once(APP_PATH.'/libs/class.validation_adapter.php');
    $validationAdapterObj = new validation_adapter();

    unset($_SESSION["form_errors"]);

    $validationAdapterObj->ignored_fields=Array("otherForename","forenameexp");
    $validationAdapterObj->non_compulsory_fields=Array();
    $validationAdapterObj->validate_form($_POST);
    $result_set=$validationAdapterObj->_unset();
    $result_set=$validationAdapterObj->_unset_ignored_fields();

    if( $result_set["error_count"] > 0 ) {
        $_SESSION["form_errors"]=$result_set;
    }
}*/

$adForenameArray = explode("~L~",$otherForename);  	
$names=array();
for($i=0;$i<count($adForenameArray);$i++)
{	      
	 $nmArr = explode("||",$adForenameArray[$i]);
	 $names[$i]['name']=$this->common->correctstring($nmArr[0]);
	 $names[$i]['fromMonth']=$nmArr[1];
	 $names[$i]['fromYear']=$nmArr[2];
	 $names[$i]['toMonth']=$nmArr[3];
	 $names[$i]['toyear']=$nmArr[4];
}

$adcount=count($adForenameArray);

if($names[$id]["fromYear"]==$FromYear && $names[$id]["fromMonth"]==$FromMonth)
{
    $tocount=$adcount-1;
}
else
{
    $tocount=$id;
}

for($i=0;$i<=$tocount;$i++)
{
	if($i<>$id)
	{
	   $nameValues=$names[$i]['name']."||".$names[$i]['fromMonth']."||".$names[$i]['fromYear']."||".$names[$i]['toMonth']."||".$names[$i]['toyear'];
	   if(isset($names[$i]["fromMonth"]))
	   $lastmonth=$names[$i]["fromMonth"];
	   if(isset($names[$i]["fromYear"]))
	   $lastyear=$names[$i]["fromYear"];
	}else{
	   $lastmonth=$FromMonth;
	   $lastyear=$FromYear;
	   $nameValues=$forname."||".$FromMonth."||".$FromYear."||".$livedUntilMonth."||".$livedUntilYear;
	}

	if(empty($otherForename1))
	$otherForename1=$nameValues;
	else
	$otherForename1.="~L~".$nameValues;
}

$accesstype = "addAdditionalForenames";

$postParams = array("accesstype" => $accesstype, "currentForName" => stripslashes($currentForName),"curmiddlename" => stripslashes($curmiddlename),"otherMiddleNameone" => stripslashes($otherMiddleNameone),"otherMiddleNametwo" => stripslashes($otherMiddleNametwo),"otherForename" => $otherForename1,"dateOfBirth" => $dateOfBirth,"lastyear" => $lastyear,"lastmonth" => $lastmonth);

require_once("formSubmission.php");
die();
?>
