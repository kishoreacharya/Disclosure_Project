<?php
include_once MODEL_PATH . 'Authenticate.php';
foreach ($_REQUEST as $key => $val) {
    $$key = $val;
}
/*if (SERVERSIDE_VALIDATION) {
    include_once(APP_PATH . '/libs/class.validation_adapter.php');
    $validationAdapterObj = new validation_adapter();

    unset($_SESSION["form_errors"]);

    $validationAdapterObj->ignored_fields = Array("appexp", "postexp", "addresses");
    $validationAdapterObj->non_compulsory_fields = Array("address2");
    $validationAdapterObj->validate_form($_POST);
    $result_set = $validationAdapterObj->_unset();
    $result_set = $validationAdapterObj->_unset_ignored_fields();

    if ($result_set["error_count"] > 0) {
        $_SESSION["form_errors"] = $result_set;
    }
}*/

$adArray = $this->common->getValueArray(stripslashes($addresses));
$adcount = count($adArray);

if ($adArray[$id]["livedFromYear"] == $livedFromYear && $adArray[$id]["livedFromMonth"] == $livedFromMonth) {
    $tocount = $adcount;
} else {
    $tocount = $id;
}

for ($i = 1; $i <= $tocount; $i++) {
    if ($i <> $id) {
        $maddress = "address1||" . stripslashes($adArray[$i]["address1"]);
        $maddress2 = "address2||" . stripslashes($adArray[$i]["address2"]);
        $mtown = "town||" . stripslashes($adArray[$i]["town"]);
        $mcounty = "county||" . stripslashes($adArray[$i]["county"]);
        $mcountry = "country||" . $adArray[$i]["country"];
        $mpostcode = "postcode||" . $adArray[$i]["postcode"];
        $mlivedFromMonth = "livedFromMonth||" . $adArray[$i]["livedFromMonth"];
        $mlivedFromYear = "livedFromYear||" . $adArray[$i]["livedFromYear"];
        $mlivedUntilMonth = "livedUntilMonth||" . $adArray[$i]["livedUntilMonth"];
        $mlivedUntilYear = "livedUntilYear||" . $adArray[$i]["livedUntilYear"];

        $caddresses = trim($maddress) . "~L~" . trim($maddress2) . "~L~" . trim($mtown) . "~L~" . trim($mcounty) . "~L~" . trim($mcountry) . "~L~" . ($mpostcode) . "~L~" . trim($mlivedFromMonth) . "~L~" . trim($mlivedFromYear) . "~L~" . trim($mlivedUntilMonth) . "~L~" . trim($mlivedUntilYear);
        if (isset($address[$i]["livedFromMonth"]))
            $lastmonth = $address[$i]["livedFromMonth"];
        if (isset($address[$i]["livedFromYear"]))
            $lastyear = $address[$i]["livedFromYear"];
    }
    else {
        $lastmonth = $livedFromMonth;
        $lastyear = $livedFromYear;

        $address1 = "address1||" . stripslashes($address1);
        $address2 = "address2||" . stripslashes($address2);
        $town = "town||" . stripslashes($town);
        $county = "county||" . stripslashes($county);
        $country = "country||" . $country;
        $postcode = "postcode||" . $postcode;
        $livedFromMonth = "livedFromMonth||" . $livedFromMonth;
        $livedFromYear = "livedFromYear||" . $livedFromYear;
        $livedUntilMonth = "livedUntilMonth||" . $livedUntilMonth;
        $livedUntilYear = "livedUntilYear||" . $livedUntilYear;
        $caddresses = trim($address1) . "~L~" . trim($address2) . "~L~" . trim($town) . "~L~" . trim($county) . "~L~" . trim($country) . "~L~" . trim($postcode) . "~L~" . trim($livedFromMonth) . "~L~" . trim($livedFromYear) . "~L~" . trim($livedUntilMonth) . "~L~" . trim($livedUntilYear);
    }


    if (empty($addresses1))
        $addresses1 = $caddresses;
    else
        $addresses1.="~L~" . $caddresses;
}

$numberofyears = 0;
$adArray = $this->common->getValueArray($addresses1);
$adcount = count($adArray);

for ($i = 1; $i <= $adcount; $i++) {
    $livedFromMonth = $adArray[$i]["livedFromMonth"];
    $livedFromYear = $adArray[$i]["livedFromYear"];
    $livedUntilMonth = $adArray[$i]["livedUntilMonth"];
    $livedUntilYear = $adArray[$i]["livedUntilYear"];

    $lastmonth = $livedFromMonth;
    $lastyear = $livedFromYear;
    $numberofyears = $numberofyears + (($livedUntilYear - $livedFromYear) * 12 + $livedUntilMonth) - $livedFromMonth;
}
$totalnow = $numberofyears;

$yearnow = date("Y");
$monthnow = date("m");

$oldtotal = ($yearnow - $firstyear) * 12 + $monthnow - $firstmonth;

$totalnow = $totalnow + $oldtotal;


$accesstype = "addAddresses";

$postParams = array("accesstype" => $accesstype, "addresses" => $addresses1, "previoustotal" => $totalnow, "lastyear" => $lastyear, "lastmonth" => $lastmonth);

require_once("formSubmission.php");
die();
?>

