<?php

class CommonLib extends Oracle {

    private $companyIds;
    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;
    public $CURRENT_MIDDLENAME = 9;

    function __construct() {
        parent::__construct();
        return true;
    }

    /*      @   function to calculate Age- parameter dd,mm,YYYY
     *      @   returns the age based on current date in years
     */

    function calculateAge($day, $month, $year) {
        if (($month = (date('m') - $month)) < 0) {
            $year++;
        } elseif ($month == 0 && date('d') - $day < 0) {
            $year++;
        }
        return date('Y') - $year;
    }

    /*     @  AddSlash Function of PHP re-written for Oracle DB
     *     @  Add quotes ('). Wil replace ' and "
     */

    function oci_addslashes($value) {
        // Replace all " with '
        $value = str_replace("\"", "'", "$value");

        // Replace all ' with ''
        $value = str_replace("'", "''", "$value");

        return $value;
    }

    /*     @  Function to add a variable to the session.
     *     @  not advised to use with session_register().
     */

    function addToSession($key, $value) {
        $_SESSION["$key"] = $value;
    }

    /*     @  This function gets the value of a session variable.
     */

    function getFromSession($key) {
        return $_SESSION["$key"];
    }

    /*     @  Correct String function
     */

    function correctstring($value) {
        if (strpos($value, "\'")) {
            do {
                $value = str_replace("\'", "'", "$value");
            } while (strpos($value, "\'"));
        } else if (strpos($value, "\"")) {
            do {
                $value = str_replace('\"', '"', "$value");
            } while (strpos($value, '\"'));
        }

        return $value;
    }

    /*     @  Correct Case function
     */

    function correctcase($value) {
        $value = ucwords(strtolower($value));
        return $value;
    }

    /*    @Function to generate Form Ref Number
     *     @Application type   : V/M
     *     @Role / Section     : L/S/D or R/B/G/S
     *     @Gender             : F/M
     *     @Firstname          : First Char
     *     @Surname            : First Char
     *     @DOB                : Y M M D D Y
     *     @Application Id     : Auto Increment
     */

    function generateUniqueRefNumber($appType, $role, $group_id, $gender, $firstname, $surname, $dob, $registrationId) {
        // Parameter 1
        $param1 = (isset($appType) && !empty($appType) && $appType == "V") ? $appType : "M";

        // Parameter 2
        if ($param1 == "V") {
            $listRoles = $this->getVolunteerRoles($role);
            $roleName = trim($listRoles[0]['name']);
            $param2 = substr($roleName, 0, 1);
        } else {
            $group = $this->getGroupType("G", $group_id);
            $groupName = trim($group[0]['name']);
            $param2 = substr($groupName, 0, 1);
        }

        // Parameter 3
        $param3 = (isset($gender) && !empty($gender) && $gender == "M") ? $gender : "F";

        // Parameter 4
        $firstname = trim($firstname);
        $param4 = substr($firstname, 0, 1);

        // Parameter 5
        $surname = trim($surname);
        $param5 = substr($surname, 0, 1);

        // Parameter 6
        $param6 = substr($dob["year"], 2, 1) . str_pad($dob["month"], 2, "0", STR_PAD_LEFT) . str_pad($dob["day"], 2, "0", STR_PAD_LEFT) . substr($dob["year"], 3, 1);

        // Parameter 7
        $param7 = $registrationId;

        $refNumber = $param1 . $param2 . $param3 . $param4 . $param5 . $param6 . "/" . $param7;

        return strtoupper($refNumber);
    }

    /*    @Function to get the last inserted ID using Transaction KEY
     *     @transactionKey : Mandatory Field
     */

    function getLastInsertedId($tableName, $fieldName, $keyField, $keyVal) {
        $sqlSelect = "select $fieldName as ID from $tableName where $keyField = '$keyVal' limit 1";
        $result = $this->getDBRecords($sqlSelect);

        return $result[0]['ID'];
    }

    /*    @Function to generate Random Passwords
     */

    public function getRandomPassword() {
        $chars = "ABCDEFGHKMNPRSTUVWXYabcdefghkmnprstuvwxy346789";
        $i = 0;
        $token = '';
        for ($i = 0; $i < 9; $i++) {
            ($i < 8) ? $num = rand(0, 40) : $num = rand(40, 46);
            $tmp = substr($chars, $num, 1);
            $token = $token . $tmp;
        }
        return $token;
    }

    /*    @Function to generate Random Numbers
     *     @min             : Minum Numbers
     *     @max          :     Maximum Numbers
     */

    public function getRandomNumbers($min, $max) {
        $generated = array();
        for ($i = 0; $i < 100; $i++) {
            $generated[] = mt_rand($min, $max);
        }
        shuffle($generated);
        $position = mt_rand(0, 99);
        return $generated[$position];
    }

    function getFormatedDate($dt, $format=0) {
        $d = substr($dt, 0, 2);
        $m = substr($dt, 2, 2);
        $y = substr($dt, 4, 4);

        $formatedDt = $dt;
        if (!empty($dt) && $dt != "") {
            switch ($format) {
                case 1 :
                    $formatedDt = date("d-m-Y", mktime(0, 0, 0, $m, $d, $y));
                    if (empty($formatedDt) || $y < 1970

                        )$formatedDt = $d . "-" . $m . "-" . $y;
                    break;
                case 2 :
                    $formatedDt = date("m-d-Y", mktime(0, 0, 0, $m, $d, $y));
                    if (empty($formatedDt) || $y < 1970

                        )$formatedDt = $m . "-" . $d . "-" . $y;
                    break;
                case 3 :
                    $formatedDt = @date("d/m/Y", mktime(0, 0, 0, $m, $d, $y));
                    if (empty($formatedDt) || $y < 1970

                        )$formatedDt = $d . "/" . $m . "/" . $y;
                    break;
                case 4 :
                    if ($m == '01' || $m == 1)
                        $month = "JAN";
                    if ($m == '02' || $m == 2)
                        $month = "FEB";
                    if ($m == '03' || $m == 3)
                        $month = "MAR";
                    if ($m == '04' || $m == 4)
                        $month = "APR";
                    if ($m == '05' || $m == 5)
                        $month = "MAY";
                    if ($m == '06' || $m == 6)
                        $month = "JUN";
                    if ($m == '07' || $m == 7)
                        $month = "JUL";
                    if ($m == '08' || $m == 8)
                        $month = "AUG";
                    if ($m == '09' || $m == 9)
                        $month = "SEP";
                    if ($m == '10' || $m == 10)
                        $month = "OCT";
                    if ($m == '11' || $m == 11)
                        $month = "NOV";
                    if ($m == '12' || $m == 12)
                        $month = "DEC";
                    $formatedDt = $d . "-" . $month . "-" . $y;
                    if (empty($formatedDt)

                        )$formatedDt = $d . "-" . $m . "-" . $y;
                    break;
                default :
                    $formatedDt = date("Y-m-d", mktime(0, 0, 0, $m, $d, $y));
                    break;
            }
        }

        return $formatedDt;
    }

    function generateRandomString($min=9, $max=10) {
        #generate random password
        $pwd = ""; // to store generated password

        for ($i = 0; $i < rand($min, $max); $i++) {
            $num = rand(50, 122);
            if (($num > 97 && $num < 122 && $num <> 108)) {
                $pwd.=chr($num);
            } else if (($num > 49 && $num < 57)) {
                $pwd.=chr($num);
            } else {
                $i--;
            }
        }
        return $pwd;
    }

    #Generate Reference Number

    function generateRef($user_id) {
        $num = rand(65, 90);
        $user_id+=8888;
        $ref = "EREF" . $user_id . chr($num);
        return $ref;
    }

    function getValueArray($stString) {
        $j = 0;
        $k = 1;
        if (!empty($stString)) {
            # print $hidAppStage1;
            $adArray = explode("~L~", $stString);
            for ($i = 0; $i < count($adArray); $i++) {
                if ($j <= 9) {
                    $j = $j + 1;
                } else {
                    $j = 1;
                    $k = $k + 1;
                }
                $keyVal = explode("||", $adArray[$i]);
                $params[$k][$keyVal[0]] = stripslashes($keyVal[1]);
                #debugPrint("<br><b>$i ".$keyVal[0]." : ".$params[$keyVal[0]]);
            }

            return $params;
        }else
            return null;
    }

    function getFormDate($dt) {
        $d = substr($dt, 0, 2);
        $m = substr($dt, 3, 2);
        $y = substr($dt, 6, 4);
        # print "<br>".$d.":".$m.":".$y;
        if (!empty($dt) && $dt != "")
            $formDate = @date("dmY", mktime(0, 0, 0, $m, $d, $y));
        #else
        #$formDate = $dt;
        if (empty($fromDate) || $formDate == "")
            $formDate = $d . $m . $y;

        return $formDate;
    }

    /*
     * Function to generate random padding character
     */

    function generateRandomPaddingChar($barcode) {

        $strChar = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < strlen($strChar); $i++) {
            $strCharArray[$i] = substr($strChar, $i, 1);
        }

        for ($i = 0; $i < strlen($barcode); $i++) {
            $barcodeArray[$i] = substr($barcode, $i, 1);
        }

        $diff = array_diff($strCharArray, $barcodeArray);

        $num = rand(0, count($diff) - 1);

        foreach ($diff as $key => $value) {
            //$tmp[] = "'$key'";
            $tmp[] = "$value";
        }
        $paddingChar = $tmp[$num];

        return $paddingChar;
    }

    function getMonthName($mnt) {
        $month = array();
        $month['01'] = "January";
        $month['02'] = "February";
        $month['03'] = "March";
        $month['04'] = "April";
        $month['05'] = "May";
        $month['06'] = "June";
        $month['07'] = "July";
        $month['08'] = "August";
        $month['09'] = "September";
        $month['10'] = "October";
        $month['11'] = "November";
        $month['12'] = "December";


        return $month[$mnt];
    }

    #Count the no of days from today when LPF start date has enetered

    function dateDiff($dformat, $endDate, $beginDate) {
        $date_parts1 = explode($dformat, $beginDate);
        $date_parts2 = explode($dformat, $endDate);
        $start_date = gregoriantojd($date_parts1[0], $date_parts1[1], $date_parts1[2]);
        $end_date = gregoriantojd($date_parts2[0], $date_parts2[1], $date_parts2[2]);
        return $end_date - $start_date;
    }

    function convertToTimestamp1($date) {
        $date_array = explode("/", $date);
        $day = intval(trim($date_array[0]));
        $month = intval(trim($date_array[1]));
        $year = intval(trim($date_array[2]));
        //echo $day."-".$month."-".$year;
        return mktime(0, 0, 0, $month, $day, $year);
    }

    function convertToTimestamp2($date) {
        $date_array = explode("/", $date);
        $day = intval(trim($date_array[0]));
        $month = intval(trim($date_array[1]));
        $year = intval(trim($date_array[2]));
        //echo $day."-".$month."-".$year;
        return mktime(23, 59, 59, $month, $day, $year);
    }

    /*     * ***************************************************************CRB Functions**************************************************************** */

    function getApplicantName($applicationId, $nameType) {
        $query = "SELECT psn.* FROM person_names psn,app_person_name appsn ";
        $query.=" WHERE psn.name_id=appsn.name_id AND ";
        $query.=" appsn.application_id='$applicationId' AND psn.name_type_id='$nameType'";
        $names = $this->getDBRecords($query);
        return $names;
    }

    function getUserId($username) {
        $query = "SELECT user_id FROM users WHERE username='$username'";
        $res = $this->getDBRecords($query);

        if (count($res) > 0 && $res[0]['user_id'] != 0 && !empty($res[0]['user_id']))
            return $res[0]['user_id'];
        else
            return false;
    }

    function updateApplicantName($nameArray, $applicationId, $nameType, $unique_id, $nameid) {
        $updqry = "UPDATE person_names SET used_from='" . $nameArray["used_from"] . "', used_to='" . $nameArray["used_to"] . "',form_tag='" . $nameArray["form_tag"] . "',unique_key='" . $unique_id . "',used_form_month='" . $nameArray["used_form_month"] . "', used_to_month='" . $nameArray["used_to_month"] . "' WHERE name_id='" . $nameid . "'";
        $this->Query($updqry);
    }

    function getMaxNameid($unique_id, $type_id) {
        $query = "SELECT max(name_id) AS name_id FROM person_names WHERE unique_key='$unique_id' AND name_type_id='$type_id'";
        $result = $this->getDBRecords($query);
        $surnameid = $result[0]['name_id'];
        return $surnameid;
    }

    function addAddress($address, $addressType, $unique_id) {
        if (empty($address["form_tag"]))
            $address["form_tag"] = "";

        $query = "INSERT INTO address (";
        $query.="address_id, address1, address2, town_city, county, country,";
        $query.="postcode,phone_no, fax, email, lived_from_month, lived_from_year, lived_until_month,";
        $query.="lived_until_year, address_type_id,form_tag,suplimentary_number,unique_key) VALUES (";
        $query.="\"\", \"" . $address["address1"] . "\", \"" . $address["address2"] . "\", \"" . $address["town"] . "\",";
        $query.="\"" . $address["county"] . "\", \"" . $address["country"] . "\", \"" . $address["postcode"] . "\",";
        $query.="\"" . $address["phone_no"] . "\", \"" . $address["fax"] . "\", \"" . $address["email"] . "\",";
        $query.="\"" . $address["lived_from_month"] . "\", \"" . $address["lived_from_year"] . "\",";
        $query.=" \"" . $address["lived_until_month"] . "\", \"" . $address["lived_until_year"] . "\",";
        $query.=" \"$addressType\",\"" . $address["form_tag"] . "\",\"" . $address["suplimentary_number"] . "\",\"" . $unique_id . "\")";

        $res = $this->Query($query);

        if ($res > 0) {
            $query = "SELECT MAX(address_id) AS address_id FROM address WHERE unique_key='$unique_id'";
            $result = $this->getDBRecords($query);
            if (!empty($result))
                return $result[0]["address_id"];
        }else
            return null;
    }

    function addApplicantAddress($address, $applicationId, $addressType, $unique_id) {
        $addressId = $this->addAddress($address, $addressType, $unique_id);

        if (!empty($addressId) && $addressId != null) {
            $query = "INSERT INTO app_address(application_id,address_id) values('$applicationId','$addressId')";
            $result = $this->Query($query);
        }
    }

    #Insert Applicant Name

    function addApplicantName($nameArray, $applicationId, $nameType, $unique_id) {
        $nameId = $this->addName($nameArray, $nameType, $unique_id);

        if (!empty($nameId) && $nameId != null) {
            $query = "INSERT INTO app_person_name(application_id,name_id) VALUES ('$applicationId','$nameId')";
            $this->Query($query);
        }
    }

    function getApplicantAddress($applicationId, $addressType) {
        $query = "select ad.* from address ad,app_address appad ";
        $query.=" where ad.address_id=appad.address_id and ";
        $query.=" appad.application_id='$applicationId' and ad.address_type_id='$addressType'";
        $address = $this->getDBRecords($query);
        return $address;
    }

    function getApplicantDob($applicationId) {
        $query = "select date_of_birth from applications where application_id='$applicationId'";
        $dobdate = $this->getDBRecords($query);
        $dob = $this->getFormatedDate($dobdate[0]["date_of_birth"], 3);
        return $dob;
    }

    function getFormErrors($appRefNo) {
        $query = "select *,unix_timestamp(errordate) senton from form_errors where appRefNo='$appRefNo'";
        $errors = $this->getDBRecords($query);
        return $errors;
    }

    function addName($nameArray, $nameType, $unique_id) {
        $query = "INSERT INTO person_names (";
        $query.=" name_id, name, used_from, used_to,used_form_month,used_to_month, name_type_id, form_tag, ";
        $query.=" suplimentary_number,unique_key) VALUES (";
        $query.="\"\", \"" . $nameArray["name"] . "\", ";
        $query.="\"" . $nameArray["used_from"] . "\", \"" . $nameArray["used_to"] . "\",";
        $query.="\"" . $nameArray["used_form_month"] . "\", \"" . $nameArray["used_to_month"] . "\",";
        $query.=" \"" . $nameType . "\",\"" . $nameArray["form_tag"] . "\",\"" . $nameArray["suplimentary_number"] . "\",\"" . $unique_id . "\")";

        $res = $this->Query($query);

        if ($res > 0) {
            $query = "SELECT max(name_id) AS name_id FROM person_names WHERE unique_key='$unique_id'";
            $result = $this->getDBRecords($query);
            if (!empty($result))
                return $result[0]["name_id"];
        }else
            return null;
    }

    function getOrgVolunteerOption($orgId) {
        $query = "SELECT volunteer FROM organisation WHERE org_id='$orgId'";
        $result = $this->getDBRecords($query);
        $orgVolunteer = $result[0]["volunteer"];
        return $orgVolunteer;
    }

    function getJobDetails($occupRole="") {
        if ($occupRole == 0) {
            $occupRole = 1;
        }
        $jquery = "SELECT * FROM jobs WHERE sno='$occupRole'";
        $jres = $this->getDBRecords($jquery);
        return $jres;
    }

    function getCountryCode($cname) {
        $query = "SELECT country_code FROM ebulk_iso_country_code WHERE country_name='" . addslashes($cname) . "'";
        $res = $this->getDBRecords($query);

        $code = "";
        if (!empty($res[0]['country_code']))
            $code = " [" . $res[0]['country_code'] . "]";

        return $code;
    }

    function getOrgName($orgId) {
        $query = "SELECT name FROM organisation WHERE org_id='$orgId'";
        $name = $this->getDBRecords($query);
        $orgName = $name[0]["name"];
        return $orgName;
    }

    /*     @  Update User Login Count
     *     @  Update on Each login
     */

    function trackUserLoginInfo($userId) {
        $host_ip = $_SERVER['REMOTE_ADDR'];
        $sqlUpdate = "update users set last_visit = CURRENT_TIMESTAMP,login_count= login_count+1,last_ip='$host_ip' where user_id='$userId' limit 1";
        $this->query($sqlUpdate);
    }

    /*    @  Function to update LOG details - Success
     *     @  Update on Each login
     */

    function logLoginSuccess($username) {
        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $browser_info = $_SERVER['HTTP_USER_AGENT'];
		$host_ip = $this->trackIp();
        $sqlInsert = "INSERT INTO user_logs (username,login_time, host,ip_address,user_agent,login_failed) VALUES ('$username', CURRENT_TIMESTAMP, '$hostname','$host_ip','$browser_info','N')";
        $this->query($sqlInsert);
    }

    /*    @  Function to update LOG details - Failure
     *     @  Update on Each login
     */

    function logLoginFailure($username, $password) {
        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $browser_info = $_SERVER['HTTP_USER_AGENT'];
		$host_ip = $this->trackIp();
        $sqlInsert = "INSERT INTO user_logs (username,password,login_time, host,ip_address,user_agent,login_failed) VALUES ('$username','$password', CURRENT_TIMESTAMP, '$hostname','$host_ip','$browser_info','Y')";
        $this->Query($sqlInsert);
    }

    /*    @  Function to get the restricted page access for given access level
     *     @  Each access will have its own access list
     */

    function getRestrictedFileIds($accessId) {
        $sqlSelect = "select access_rights from m_access_level where access_id = '$accessId' limit 1";
        $accessRes = $this->getDBRecords($sqlSelect);
        $file_id = $accessRes[0]['access_rights'];

        return $file_id;
    }

    /*  @ Function to get Ristricted file name for a logged in person
     *  @ file_ids : List of files I have access to
     */

    function getRestrictedFileNames($file_ids) {
        $sqlSelect = "select file_name,file_linked from m_system_access where file_id in ($file_ids) and deleted_on is NULL";
        $restrict_accesspage = $this->getDBRecords($sqlSelect);

        $accessArray = array();
        for ($m = 0; $m < count($restrict_accesspage); $m++) {
            $accessArray[] = $restrict_accesspage[$m]['file_name'];

            if (!empty($restrict_accesspage[$m]['file_linked'])) {
                $linkedFileNameArray = explode(",", $restrict_accesspage[$m]['file_linked']);

                for ($acc = 0; $acc < count($linkedFileNameArray); $acc++)
                    $accessArray[] = trim($linkedFileNameArray[$acc]);
            }
        }

        $accessArray = array_unique($accessArray);
        return $accessArray;
    }
    
		function getResetPwdAlert($user_id){
    $query = 'SELECT last_updated, initiate_activate FROM users WHERE user_id ='.$user_id; 
    $res = $this->getDBRecords($query);
    $current_time = time();
    $date_diff = 0;
    if(!empty($res[0]['last_updated'])){
      $date_diff = round((($current_time-$res[0]['last_updated'])/60/60/24));
    }
    if( ($res[0]['last_updated'] > 0 && $date_diff > 90) || ($res[0]['last_updated'] == 0 && $res[0]['initiate_activate'] =='Y') ){
        return $resetpwd = '0';
    }else{
        return $resetpwd = '1';
    }
}
    /*  @ Function to get Ristricted Page Links for menu for a logged in person
     *  @ file_ids : List of files I have access to
     */

    function getRestrictedPageLinks($file_ids) {
        $restrict_links = explode(',', $file_ids);
        $arr_restrict_links = array_flip($restrict_links);
        foreach ($arr_restrict_links as $key => $value) {
            $arr_restrict_links[$key] = 1;
        }
        return $arr_restrict_links;
    }

    /*    @Function to generate URN Number
     *     $nameArray          : User Details
     */

    public function getRandomURN($nameArray) {
        #Get the First two letter of the org name
        $firstSection = substr(strtoupper(preg_replace('/\s+/','',$nameArray['orgname'])), 0, 2);

        #Get the First two letter of the applicants surname
        $secondSection = substr(strtoupper(preg_replace('/\s+/','',$nameArray['lastname'])), 0, 2);

        #Generating Random Numbers of 6 numeric digits
        $thirdSection = $this->getRandomNumbers(111111, 999999);

        $username_nrand = $firstSection . $secondSection . $thirdSection;
        $chk_user_exist = "select username from user_registration where username='$username_nrand'";
        $resusr_exist = $this->getDBRecords($chk_user_exist);
        $username = $username_nrand;
        if (count($resusr_exist) > 0) {
            $thirdSection = $this->getRandomNumbers(111111, 999999);
            $i = 1;
            do {
                $username_new = $firstSection . $secondSection . $thirdSection;
                $query = "select username from user_registration where username='$username_new'";
                $userexists = $this->getDBRecords($query);
                $username = $username_new;
                $i++;
            } while (count($userexists) > 0);
        }
        return $username;
    }

    function getCountryDropdownListWithoutUK($name, $initVal="", $js="") {
        $dList = "<SELECT name=\"$name\" $js class=\"select\" id=\"$name\"><option value=\"\">Select from List</option>";
        $countries = $this->getDBRecords("select countryname as name,countryid as id from countries where countryname != 'UK' order by countryname");
        for ($i = 0; $i < count($countries); $i++) {

            if ($initVal == $countries[$i]["id"])
                $sel = "selected";
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . $countries[$i]["id"] . "\">" . $countries[$i]["name"] . "</option>";
        }
        $dList.= "</SELECT>";

        return $dList;
    }
    
    public function getAuthorityCouncilsDropdownList($name,$initVal="",$js="")
  {
    $dList = "<SELECT name=\"$name\" $js class=\"selectbox_2\" id=\"$name\"><option value=\"\">Select from List</option>";
    $countries = $this->getDBRecords("select authority as name,authority as id from local_authorities order by authority");
    for($i=0;$i<count($countries);$i++)
    {

      if($initVal==$countries[$i]["id"])
       $sel = "selected";
      else
        $sel=null;
      $dList.= "<option $sel value=\"".$countries[$i]["id"]."\">".$countries[$i]["name"]."</option>";

    }
   $dList.= "</SELECT>";

    return $dList;
  }

    # get the Month  Drop down list.

    function getMonthDropdownList($name, $initVal="", $js="") {
        $dList = "<SELECT id=\"$name\" name=\"$name\" $js class=\"selectbox\"><option value=\"\">Month</option>";

        for ($i = 1; $i <= 12; $i++) {
            $monthname = date("F", mktime(0, 0, 0, $i, 1, 2000));
            $month = date("m", mktime(0, 0, 0, $i, 1, 2000));

            if ($initVal == $month)
                $sel = "selected";
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . $month . "\">" . $monthname . "</option>";
        }
        $dList.= "</SELECT>";

        return $dList;
    }

    # get the Month  Drop down list.

    function getYearDropdownList($name, $initVal="", $js="") {
        $dList = "<SELECT id=\"$name\" name=\"$name\" $js class=\"selectbox\"><option value=\"\">Year</option>";
        $year = date("Y");
        for ($i = 0; $i < 110; $i++) {
            $monthname = date("F", mktime(0, 0, 0, $i, 1, 2000));
            $month = date("m", mktime(0, 0, 0, $i, 1, 2000));

            if ($initVal == $year)
                $sel = "selected";
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . $year . "\">" . $year . "</option>";

            $year = $year - 1;
        }
        $dList.= "</SELECT>";

        return $dList;
    }

    function matchtowncounty($townval, $countyval, $check) {

        switch ($check) {
            case btown : {
                    $query = "select town as townname from county_town_all where town like '$townval'";
                    $townres = $this->getDBRecords($query);
                    break;
                }
            case bcounty : {
                    $query = "select county  from county_town_all where county like '$countyval'";
                    $townres = $this->getDBRecords($query);
                    break;
                }
            case both : {
                    $query = "select town as townname from county_town_all where town like '$townval'";
                    if (!empty($countyval)) {
                        $query.=" and county LIKE '$countyval'";
                    }
                    $townres = $this->getDBRecords($query);
                    break;
                }
            default : {
                    $query = "select town as townname from county_town_all where town like '$townval'";
                    if (!empty($countyval)) {
                        $query.=" and county LIKE '$countyval'";
                    }
                    $townres = $this->getDBRecords($query);
                }
                break;
        }

        if (count($townres) == 0)
            return 0; //does not exists
 else
            return 1; //exists



































    }

    #Limit prev address to 200 only

    function limitPrevAddress($addresses) {
        $addArray = array();
        $addArray = $this->getValueArray($addresses);
        $addressCount = count($addArray);
        return $addressCount;
    }

    # get the Month  Drop down list.

    function getCountryDropdownList($name, $initVal="", $js="") {
        $dList = "<SELECT name=\"$name\" $js class=\"select\" id=\"$name\"><option value=\"\">Select from List</option><option value=\"UK\">United Kingdom</option>";
        $countries = $this->getDBRecords("select countryname as name,countryid as id from countries order by countryname");
        for ($i = 0; $i < count($countries); $i++) {

            if ($initVal == $countries[$i]["id"])
                $sel = "selected";
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . $countries[$i]["id"] . "\">" . $countries[$i]["name"] . "</option>";
        }
        $dList.= "</SELECT>";

        return $dList;
    }

    function check_spelling($name, $name_id) {
        if ($name_id == 1 || $name_id == 3 || $name_id == 5 || $name_id == 6) {//for surnames
            $query = "select name from old_person_names where (name_type_id=1 or name_type_id=3 or name_type_id=5 or
	     name_type_id=6) and name = '$name'";
        } elseif ($name_id == 2 || $name_id == 4 || $name_id == 7) {//for fornames
            $query = "select name from old_person_names where (name_type_id=2 or name_type_id=4 or name_type_id=7) and name = '$name'";
        }

        $value = $this->getDBRecords($query);
        if (count($value) == 0)
            return 0; //does not exists
 else
            return 1; //exists































    }

    #get child companies
# this function returns the list of all child companies of the given company

    function getChildCompanies($company_id, $company_list="") {
        global $deptAccess;
        
        if (empty($deptAccess)) {
            if (empty($company_list))
                $company_list = $company_id;
            else
                $company_list.="," . $company_id;
            
            $query = "select company_id from company where parent_id in ($company_id)";
            $res = $this->getDBRecords($query);

            if (count($res) > 0) {
                $str = "";
                for ($i = 0; $i < count($res); $i++) {
                    if (!empty($str))
                        $str.=",";
                    $str.=$res[$i]['company_id'];
                }
                $company_list = $this->getChildCompanies($str, $company_list);
            }
            $company_list = explode(",", $company_list);
            $company_list = array_unique($company_list);
            sort($company_list);

            return implode(",", $company_list);
        }
        else {
            return $deptAccess;
        }
    }

    function getChildCompaniesHeirachy($company_id, $company_list="") {

        if (empty($company_list))
            $company_list = $company_id;
        else
            $company_list.="," . $company_id;

        $query = "select company_id,parent_id,name from company where company_id in ($company_id) ";
        $res = $this->getDBRecords($query);

        if (count($res) > 0) {
            $str = "";
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($str))
                    $str.=",";
                $str.=$res[$i]['parent_id'];
            }
            $company_list = $this->getChildCompaniesHeirachy($str, $company_list);
        }
        $company_list = explode(",", $company_list);
        $company_list = array_unique($company_list);
        sort($company_list);

        return implode(",", $company_list);
    }

    public function pageDetails($pageid) {
        $query = "select pagedetails from pagedetails where link_id='$pageid'";
        $pages = $this->getDBRecords($query);
        return $pages[0]['pagedetails'];
    }

    #To get All Officer Type Details

    public function getUserOfficerType($type) {
        $query = "select type_id from officer_types where officer_type='$type' limit 1";
        $user_type_res = $this->getDBRecords($query);
        $officer_type = $user_type_res[0]['type_id'];
        return $officer_type;
    }

    #Get System Files Access

    public function getSystemFiles($type) {
        #--------User access--------
        $query = "select file_id from system_files where access='$type' and active_yn ='Y'";
        $sysAccessRes = $this->getDBRecords($query);
        $chkboxvalues_access = "";

        for ($acc = 0; $acc < count($sysAccessRes); $acc++) {
            if (!empty($chkboxvalues_access))
                $chkboxvalues_access.=",";
            $chkboxvalues_access.=$sysAccessRes[$acc]['file_id'];
        }
        return $chkboxvalues_access;
    }

    #Insert Into Access Level

    public function initiateAccessLevel($arrDetail) {

        $this->Insert("user_access_level", $arrDetail);
    }

    #get Trust Id Details

    public function getTrustDetails($compId) {
        $trust = "";
        $trust_id = "";
        $trust_array = array("name" => "", "company_id" => "");
        do {
            $query = "select parent_id from company where company_id='$compId'";
            $res = $this->getDBRecords($query);

            $trust = $this->getCompName($compId);
            $trust_id = $compId;
            $compId = $res[0]['parent_id'];
        } while ($compId != 0);

        if ($trust == "")
            $trust = "@lantic Data Ltd";

        $trust_array = array("name" => $trust, "company_id" => $trust_id);

        return $trust_array;
    }

    #Get Function Name

    public function getCompName($compId) {
        $query = "SELECT * FROM company WHERE company_id='$compId' ORDER BY name ASC";
        $res = $this->getDBRecords($query);
        $name = $res[0]["name"];
        return $name;
    }

    #function to get userid of the logged in person

    public function getUserId_name($username) {
        $query = "select user_id,surname,name from users where username='$username'";
        $res = $this->getDBRecords($query);

        if (count($res) > 0 && $res[0]['user_id'] != 0 && !empty($res[0]['user_id']))
            return $res[0]['name'] . " " . $res[0]['surname'];
        else
            return false;
    }

#-----------------------------------------

    function getJobs($orgId = NULL) {
        
        $orgProvides = $this->getOrgServiceProvides($orgId);             
        
        $sqlSelect = "SELECT sno,job,disclosure,homebasedquestion FROM jobs where org_provide_id in ($orgProvides) and active = 'Y' ";
        
        return $this->getDBRecords($sqlSelect);
    }
    
    /**
     * function to get unique org service provide id's
     * @return string 
     */
    function getOrgServiceProvides($orgId = NULL) {
        
        $accessLevel = $this->getFromSession("accessLevel_C");
        $company_id = $this->getFromSession("company_id_C");
        
        $sql = "select org_provides from organisation";        
        
        if($orgId){
            $sql .= " where org_id = $orgId";
        } elseif($accessLevel == 'company1'){
            $sql .= " where company_id = $company_id";
        } else {
            //get job roles of NCSC dept(registration page)
            $sql .= " where company_id = 2";
        }
        
        $res = $this->getDBRecords($sql);
        
        $orgProvidesArr = array();
        for($i=0;$i<count($res);$i++){
            $orgProvidesArr[$i] = $res[$i]['org_provides'];
        }
        
        $uniqueOrgProvides = array_unique($orgProvidesArr);
        
        return implode(",",$uniqueOrgProvides);
        
    }

    #Function To Fetch Org Name From Users

    public function getOrgNameFromUser($from) {
        $query = "select o.name from organisation o,liason_officer l1,lo_org l2
	      where l1.lo_id=l2.lo_id and l2.org_id=o.org_id
	      and l1.user_id='$from'
	      Union
	      select c.name from company c
	      where c.user_id='$from'
	      Union
	      select c.name from company c,comp_users cu
	      where c.company_id=cu.company_id
	      and cu.user_id='$from'
	    ";
        $orgname = $this->getDBRecords($query);

        if (!empty($orgname[0]['name']))
            $orgname = $orgname[0]['name'];
        else
            $orgname="";

        return $orgname;
    }

    #function to get the count of children below a company

    public function scantree($x, &$arr, $level="") {
        if (empty($level))
            $level = 0;

        $query = "SELECT company_id FROM company WHERE parent_id=" . $x . " ORDER BY name";
        $result = $this->getDBRecords($query);

        if (count($result) > 0) {
            $level++;
            for ($i = 0; $i < count($result); $i++) {
                $arr[$level]['company_id'][] = $result[$i]['company_id'];
                $this->scantree($result[$i]['company_id'], $arr, $level);
            }
        }

        $childrens = count($arr);
        return $childrens;
    }

    # this function returns the list of all child companies of the given company irrespective of department level for Single Central Report

    public function getChildCompanies_1($company_id, $company_list="") {
        if (empty($company_list))
            $company_list = $company_id;
        else
            $company_list.="," . $company_id;

        $query = "SELECT company_id FROM company WHERE parent_id in ($company_id)";
        $res = $this->getDBRecords($query);

        if (count($res) > 0) {
            $str = "";
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($str))
                    $str.=",";
                $str.=$res[$i]['company_id'];
            }
            $company_list = $this->getChildCompanies_1($str, $company_list);
        }
        $company_list = explode(",", $company_list);
        $company_list = array_unique($company_list);
        sort($company_list);

        $list = implode(",", $company_list);
        return $list;
    }

    #Function To Fetch All recipiets Of Admin Access

    public function getAllAdmins($compId, $name, $js, $index, $level) {

        #Get the COmpany structure
        $deptArray = $this->getOrgStructure();

        $query = "SELECT distinct company_id,name
            FROM company
            WHERE parent_id ='$compId' order by name";
        if (!empty($compId))
            $orglist = $this->getDBRecords($query);

        $nextindex = $index + 1;
        $nextDropdown = $index + 2;
        $deptPossition = $deptArray[$nextindex];
        $js = "onChange=\"populateCompList(this,'combo_$nextDropdown','compdiv_$nextDropdown',$nextindex,$level,false)\"";
//}

        $dList .= "<label>" . $deptPossition . "</label>";
        if (count($orglist) == 0) {
            $dList .= "<SELECT class=\"select\"  name=\"$name\" id=\"$name\" $js>
	           <option value=\"\">Select from the list</option>";
            $dList.= "</SELECT>";
        } else {
            $dList .= "<SELECT class=\"select\" name=\"$name\" id=\"$name\" $js>
	           <option value=\"\">Select from the list</option>";

            if ($allOrg == "true") {
                #if all Company facility is there
                $dList.="<option value=\"ALL\">Select All</option>";
            }
            for ($i = 0; $i < count($orglist); $i++) {
                $dList.= "<option value=\"" . $orglist[$i]["company_id"] . "\">" . $orglist[$i]["name"] . "</option>";
            }
            $dList.= "</SELECT>";
        }
        return $dList;
    }

    #Function to Get Org Stucture

    public function getOrgStructure() {
        $res = array();
        $query = "Select * from org_structure where type='C' order by id";
        $result = $this->getDBRecords($query);
        foreach ($result as $key => $value) {
            $res[] = $value['structure'];
        }
        return $res;
    }

    function getVisibilityText($key) {
        $strVisibleText = $_COOKIE[$key];
        $strVisibilityStyle = '';
        switch ($strVisibleText) {
            case 'smallText':
                $strVisibilityStyle = 'font-size:8px;';
                break;

            case 'mediumText':
                $strVisibilityStyle = 'font-size:12px;';
                break;

            case 'largeText':
                $strVisibilityStyle = 'font-size:15px;';
                break;

            default:
                $strVisibilityStyle = 'font-size:12px;';
                break;
        }
        return $strVisibilityStyle;
    }

    function curPageName() {
        return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
    }

    function getTextOnly($key) {
        $strTextOnly = $_COOKIE[$key];
        return $strTextOnly;
    }

    #Function to Fetch Message Counts

    public function getMessageCount($adminid) {
        $query = "select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id='$adminid' and s.checked = 'N' and s.deleted = 'N' and s.msg_read='N' and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";
        $max_limit = $this->getDBRecords($query);
        return $max_limit;
    }

    function getUserInfo($user_id) {
        $user = "";
        if (!empty($user_id) && $user_id != 0) {
            if ($user_id == "-1")
                $user = "Updated By System";
            else {
                $query = "select name,surname from users where user_id='$user_id'";
                $res = $this->getDBRecords($query);
                $user = $res[0]['name'] . " " . $res[0]['surname'];
            }
        }
        return $user;
    }

    #get Org services

    public function getOrgServiceList($name, $initVal="", $js="") {
        $dList = "<SELECT name=\"$name\" id=\"$name\" $js class=\"select\"><option value=\"\">Select from List</option>";

        $orgSrvc = $this->getDBRecords("select  service_name as name,org_provide_id as id from org_services order by service_name");

        for ($i = 0; $i < count($orgSrvc); $i++) {

            if ($initVal == $orgSrvc[$i]["id"])
                $sel = "selected";
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . $orgSrvc[$i]["id"] . "\">" . $orgSrvc[$i]["name"] . "</option>";
        }
        $dList.= "</SELECT>";

        return $dList;
    }

    # Funtion to get Specified Non Working Days

    public function getSpecifiedNonWorkingDays($dayBack) {
        $timedayback = mktime(9, 0, 0, date("m"), date("d"), date("Y"));
        $counter = 1;

        while ($counter <= $dayBack) {
            $timedayback-=86400;
            $counter++;
        }
        return $timedayback;
    }

    # Funtion to get Specified Working Days

    public function getSpecifiedWorkingDays($dayBack) {
        $timedayback = mktime(9, 0, 0, date("m"), date("d"), date("Y"));
        $counter = 1;

        while ($counter <= $dayBack) {
            $oneDayBack = date("l", $timedayback - 86400);
            if ($oneDayBack == "Sunday" || $oneDayBack == "Saturday") {
                $timedayback-=86400;
            } else {
                $timedayback-=86400;
                $counter++;
            }
        }
        return $timedayback;
    }

    #Get Application Comments

    public function getAppCommentDetails($applicationId) {
        $query = "select * from application_comments where application_id='$applicationId' order by entered_on desc";
        $commentsres = $this->getDBRecords($query);


        for ($i = 0; $i < count($commentsres); $i++) {
            if (!empty($commentsres[$i]["entered_on"])) {
                $commentsres[$i]["enteredon"] = date("d/m/Y - h:i", $commentsres[$i]["entered_on"]);
            }
            $commentsres[$i]["enteredby"] = $this->correctcase($this->getUserInfo($commentsres[$i]["entered_by"]));
            $commentsres[$i]["showcomments"] = $this->correctstring($commentsres[$i]["comments"]);
        }
        return $commentsres;
    }

    #Function to get month

    public function getMonth($month) {
        if (preg_match('/Jan/', $month))
            return 1;
        elseif (preg_match('/Feb/', $month))
            return 2;
        elseif (preg_match('/Mar/', $month))
            return 3;
        elseif (preg_match('/Apr/', $month))
            return 4;
        elseif (preg_match('/May/', $month))
            return 5;
        elseif (preg_match('/Jun/', $month))
            return 6;
        elseif (preg_match('/Jul/', $month))
            return 7;
        elseif (preg_match('/Aug/', $month))
            return 8;
        elseif (preg_match('/Sep/', $month))
            return 9;
        elseif (preg_match('/Oct/', $month))
            return 10;
        elseif (preg_match('/Nov/', $month))
            return 11;
        elseif (preg_match('/Dec/', $month))
            return 12;
    }

    #function to convert date to timestamp

    public function convertToTimestamp($date) {
        $date = trim($date);
        if (!empty($date)) {
            $date_array = explode(" ", $date);
            $day = intval(trim($date_array[0]));
            $month = intval(getMonth(trim($date_array[1])));
            $year = intval(trim($date_array[2]));
            return mktime(5, 0, 0, $month, $day, $year);
        }
    }

    #Get the specified working Day from the past

    public function getSpecifiedTime($dayBack) {
        $timedayback = time();
        $counter = 1;

        while ($counter <= $dayBack) {
            $oneDayBack = date("l", $timedayback - 86400);
            if ($oneDayBack == "Sunday" || $oneDayBack == "Saturday") {
                $timedayback-=86400;
            } else {
                $timedayback-=86400;
                $counter++;
            }
        }
        return $timedayback;
    }

    #function to check if the dates are greater than given date

    public function isGreater($date1, $date2) {
        if ($date2 > $date1)
            return true;
        else
            return false;
    }

    #Compare system dates

    public function compareSystemDates($sysdate, $crbdate) {
        $flag = false;
        if (!empty($sysdate)) {
            if (empty($crbdate)) {
                $flag = true;
            } else {
                if (intval(date("dmY", $sysdate)) != intval(date("dmY", $crbdate)))
                    $flag = true;
            }
        }
        return $flag;
    }

    public function curl_post_page($url, $cookie_jar="", $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.6) Gecko/20050328 Fedora/1.7.6-1.2.5");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        if ($cookie_jar != "") {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        }

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $tmp = curl_exec($ch);
        curl_close($ch);
        return $tmp;
    }

    #Function to get Redirect After QSG Part

    public function redirectLogin($confId) {
        $location = "";

        #Check if the logged in person have access to this page
        $query = "select o.access_type from officer_types o,users u where o.type_id=u.officer_type and u.user_id='$confId' limit 1";
        $restrict_access = $this->getDBRecords($query);
        $pid = $restrict_access[0]['access_type'];

        #Get all file names which I have access to
        $query = "select file_id,file_name from system_files where file_id in ($pid) and file_sub_id='0' and active_yn ='Y'";
        $restrict_accesspage = $this->getDBRecords($query);

        if (count($restrict_accesspage) > 0) {
            $location = $restrict_accesspage[0]['file_name'];
        }
        return $location;
    }

    #Get Parent Company ID

    function isParentCompany($company_id) {
        $query = "select company_id from company where parent_id='$company_id'";
        $res = $this->getDBRecords($query);
        if (count($res) == 0)
            return false;
        else
            return true;
    }

    #Get Level

    function getLevel($compId, $level=0) {
        $query = "SELECT parent_id FROM company WHERE company_id='$compId'";
        $res = $this->getDBRecords($query);

        if ($res[0]['parent_id'] != 0) {
            $level++;
            $level = $this->getLevel($res[0]['parent_id'], $level);
        }
        return $level;
    }

    #Function Edit Access Rights

    function editUserRights($access_people, $users_access) {
        $flag = "N";
        if (in_array($users_access, $access_people))
            $flag = "Y";

        return $flag;
    }

	function trackIp()
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			 $ipId=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}		
		else
		{ 
			$ipId=$_SERVER['REMOTE_ADDR'];
		}
		return $ipId;
	}
        
        
        function getMessageDate($date)
        {
            $sent_year=substr($date,0,4);
            $sent_month=substr($date,5,2);
            $sent_day=substr($date,8,2);
            $date=$sent_day."/".$sent_month."/".$sent_year;
            return $date;
        }
        
    function WorksWithDropDown($name,$default = ''){
    
    $sql = "select id,people works_with from org_works_with";
    $result = $this->getDBRecords($sql);

    $dropDown = "<select name=\"$name\" id=\"$name\" class=\"select\"><option value=\"\">Select from List</option>";

    for($i=0;$i<count($result);$i++){

        $selected = "";
        
        if($default == $result[$i]['id']){
            $selected = "selected=\"selected\"";
        }

        $dropDown .= "<option value=\"".$result[$i]['id']."\" $selected>".$result[$i]['works_with']."</option>";
   
    }

    $dropDown .= "</select>";

    return $dropDown;

}

function getWorksWithDB($id){
    $sql = "select people works_with from org_works_with where id = $id limit 1";
    $result = $this->getDBRecords($sql);
    return $result[0]['works_with'];
}

function getWorkForce($workswith){

    $sql = "select work_force_text from org_works_with where people = '$workswith' limit 1";
    $result = $this->getDBRecords($sql);

    return $result[0]['work_force_text'];
}

function certificate_seen_log($timenow,$appid,$logged_in_user,$status=''){
      $selquery="select certno,result,disc_issue_date from form_status where application_id='$appid'";
      $res_apps=$this->getDBRecords($selquery);
      $cert_no=$res_apps[0]['certno'];
      $issue_date=$res_apps[0]['disc_issue_date'];
      if(empty($status)){
          $cert_result=$res_apps[0]['result'];
      }else{
          $cert_result=$status;
      }

      $arrayLogCer["application_id"] = $appid;
      $arrayLogCer["certificate_number"] = $cert_no;
      $arrayLogCer["certificate_seen_by"] = $logged_in_user;
      $arrayLogCer["certificate_seen_date"] = $timenow;
      $arrayLogCer["disclosure_status_type"] = $cert_result;
      $arrayLogCer["certificate_issue_date"] = $issue_date;
      $this->Insert("log_certificate_seen", $arrayLogCer);

  }
  
    function log_cert_seen($timenow, $appid, $logged_in_user, $crbresult, $certno) {  
      $arrayLogCer["application_id"] = $appid;
      $arrayLogCer["result"] = $crbresult;
      $arrayLogCer["certno"] = $certno;
      $arrayLogCer["certificate_seen_date"] = $timenow;
      $arrayLogCer["created_by"] = $logged_in_user;
      $arrayLogCer["created_date"] = $timenow;
      $this->Insert("log_cert_seen", $arrayLogCer);
  }
  
      function log_replacing_certificate($timenow, $appid, $logged_in_user,$replacing_certno,$requested_by,$date_of_reprint) {
          $dt = explode('/', $date_of_reprint);
           $dt = mktime(0, 0, 0, $dt[1], $dt[0], $dt[2]);
            $arrayLogCer["application_id"] = $appid;
            $arrayLogCer["date_of_reprint"] = $dt;
            $arrayLogCer["requested_by"] = $requested_by;
            $arrayLogCer["replacing_certificate_number"] = $replacing_certno;
            $arrayLogCer["created_by"] = $logged_in_user;
            $arrayLogCer["created_on"] = $timenow;
            $this->Insert("log_replacement_certificate", $arrayLogCer);
       
  }

  
  function appendCertSeenStatus($strCertSeeFlag, $strResult, $dtCertSeen,$certseen,$certnotseen)
{
    
      $strCertStatus = "";
      switch($strResult)
      {
          case BMS_CLEAR_RESULT_TEXT:
                if($strCertSeeFlag == 'B' || $strCertSeeFlag == 'C')
                {
                    $strCertStatus = $certnotseen;
                    if($dtCertSeen > 0)
                    {
                        $strCertStatus = $certseen;
                    }
                }
                break;

          case BMS_POSITIVE_RESULT_TEXT:
                if($strCertSeeFlag == 'B' || $strCertSeeFlag == 'P')
                {
                    $strCertStatus = $certnotseen;
                    if($dtCertSeen > 0)
                    {
                        $strCertStatus = $certseen;
                    }
                }
                break;
      }

      return $strCertStatus;
}

   /**
   * Function to Insert Legacy Log
   *
   * @param integer $application_id
   * @param integer $completed_by
   * @return bool
   */
 public function insertLegacyLog($application_id,$completed_by) {
      
      $time = time();
      $query = "insert into log_legacy_application(application_id,created_by,created_datentime) values('$application_id','$completed_by','$time')";
      $this->Query($query);
      
  }
/**
 * Function to get all application with certificate log error
 *
 * @param application id
 * @return array
 */
public function getAllApplicationsWithBMSErrors($company_id,$application_id=NULL){

  $logged_in_user = $this->getFromSession('user_id_M');

  $listComp=$this->getChildCompanies($company_id);

  $selectAppLists="select straight_join l.application_id, group_concat(l.error_type_id) error_ids,concat(upper(pnf.name) ,' ', upper(pns.name)) app_name,o.name org_name,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.discType as discType from log_ebulk_xml_error l
  inner join applications a on l.application_id = a.application_id
  inner join organisation o on a.org_id = o.org_id
  inner join company c on o.company_id = c.company_id
  inner join sectionx s on a.application_id = s.application_id ";
  $selectAppLists.= " INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
  INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id
   AND pnf.name_type_id =4
  INNER JOIN app_person_name apns ON a.application_id = apns.application_id
  INNER JOIN person_names pns ON apns.name_id = pns.name_id
  AND pns.name_type_id =3
  inner join form_status f on s.application_id = f.application_id
  where s.app_ref_no is null and f.app_ref_no is null and f.dCrbDate is null and (l.answered_datentime is NULL or l.answered_datentime is null) and c.company_id in ($listComp) and a.cancelled <> 'Y' ";

  if(!empty($application_id))
  $selectAppLists.=" and a.application_id='$application_id'";


  $selectAppLists.=" group by l.application_id  ";

  $resultAppLists = $this->getDBRecords($selectAppLists);


  $result = array();
  for($i=0;$i<count($resultAppLists);$i++){
      $result[$i]['app_name'] = $this->correctcase($resultAppLists[$i]['app_name']);
      $result[$i]['org_name'] = $resultAppLists[$i]['org_name'];
      $result[$i]['app_id'] = $resultAppLists[$i]['application_id'];
      $result[$i]['dob'] = $resultAppLists[$i]['dob'];
      $result[$i]['discType'] = $resultAppLists[$i]['discType'];
      $result[$i]['error_ids'] = $resultAppLists[$i]['error_ids'];
  }

  return $result;
 }


 /**
 * Function to get all Different Error Types
 * @param error type id
 * @return array
 */
 public function getAllSelectedErrorTypes($error_type_id=NULL){

  $selectErrorTypes = "select * from ebulk_error_types ";

  if(!empty($error_type_id)){
    $selectErrorTypes.=" WHERE error_type_id IN($error_type_id)";
  }

  $resultErrorTypes = $this->getDBRecords($selectErrorTypes);

  return $resultErrorTypes;
 
 }

 /**
   * Function to update applicant's Work Home address
   *
   * @param integer $application_id
   * @param integer $error_id
   * @param string $workathome
   * @return bool
   */
  public function updateApplicantWorkingHomeAddress($application_id,$error_id,$workathome='') {
      
    
    $query="update sectionx set workingathomeaddress='$workathome' where application_id='$application_id'";
    $this->Query($query);
      
  }


  /**
   * Function to update applicant's Work Force
   *
   * @param integer $application_id
   * @param integer $error_id
   * @param integer $works_with
   * @return bool
   */
  function updateApplicantWorkForce($application_id,$error_id,$works_with='') {
      
      // Get Work Force Details
      $work_force = $this->correctstring($this->getWorkForceFinal($works_with));

      // Update Work Force
      $query="update applications set work_force='$work_force' where application_id='$application_id'";
      $this->Query($query);
      
  }


    /**
   * Function to update applicant's Ebulk XML log 
   * for answered date and answered by
   *
   * @param integer $application_id
   * @param integer $error_id
   * @param integer $works_with
   * @return bool
   */
  function updateApplicantXMLLog($application_id,$error_id,$completed_by) {
      
      // Update Ebulk xml Error log
      $time = time();
      $query="update log_ebulk_xml_error set answered_datentime='$time',answered_by='$completed_by' where application_id='$application_id' and error_type_id='$error_id'";
      $this->Query($query);
      
  }

    /**
   * Function to update applicant's 
   * work force and Home Address
   *
   * @param array $appForm
   * @return bool
   */
  public function updateApplicantWorkForceAWorkGroup($appForm){
        
        $application_id = $appForm['appId'];
        $workathome = $appForm['workathome'];
        $error_ids = $appForm['error_ids'];
        $works_with = $appForm['works_with'];
        $discType = $appForm['discType'];

        $completed_by = $this->getFromSession('user_id_M');
        
        // Looping on errors for application id
        #Exploading Error ids
        $exploded_error_ids = explode(",",$error_ids);
        for ($i=0; $i < count($exploded_error_ids) ; $i++) {
            # code...
            $error_id = null;
            $error_id = $exploded_error_ids[$i];
            switch($error_id)
            {
                case "301":
                {
                    // Function to update Working Home Address
                    $this->updateApplicantWorkingHomeAddress($application_id,$error_id,$workathome);
                    //Function to update Ebulk xml Log
                    $this->updateApplicantXMLLog($application_id,$error_id,$completed_by);
                    break;
                }
                case "307":
                {
                    // Function to update Working Home Address
                    $this->updateApplicantWorkForce($application_id,$error_id,$works_with);
                    //Function to update Ebulk xml Log
                    $this->updateApplicantXMLLog($application_id,$error_id,$completed_by);
                    break;
                }
                default:
                    break;
            }
        }
        
        // Information Requested By ADL Flag Update
        $fieldArray = null;
        $fieldArray["info_requested_by_adl"] = 'N';
        $condition  = " application_id='$application_id'";
        $this->Update('form_status', $fieldArray, $condition);
//        $query="update form_status set info_requested_by_adl='N' where application_id='$application_id'";
//        updateDBRecord($query);

    }

    function getWorkForceFinal($id){

        $sql = "select work_force_text from org_works_with where id = $id limit 1";
        $result = $this->getDBRecords($sql);

        return $result[0]['work_force_text'];
    }

    /**
 * Function to get all Legacy Applications
 *
 * @param application id
 * @return array
 */
public function getAllLegacyApplicationsReports($application_id=NULL,$refno=NULL){

  global $company_id,$orgId;
  
  if(empty($company_id)){
      $company_id = 1;
  }
          
  $logged_in_user = $this->getFromSession('user_id_M');
  
  $listComp=$this->getChildCompanies($company_id);
        
  $selectAppLists="select straight_join a.application_id,concat(upper(pnf.name) ,' ', upper(pns.name)) app_name,o.name org_name,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.discType as discType,s.jobwork jobWork,f.current_status as status,a.work_force as work_force,s.workingathomeaddress as work_home,f.app_ref_no,f.info_requested_by_dbs,o.org_id from applications a
  inner join sectionx s on a.application_id = s.application_id
  inner join organisation o on a.org_id = o.org_id
  inner join company c on o.company_id = c.company_id INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
  INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id
   AND pnf.name_type_id =4
  INNER JOIN app_person_name apns ON a.application_id = apns.application_id
  INNER JOIN person_names pns ON apns.name_id = pns.name_id
  AND pns.name_type_id =3
  inner join form_status f on s.application_id = f.application_id
   LEFT JOIN log_legacy_application lla ON a.application_id = lla.application_id     
  where s.app_ref_no is NOT NULL and f.app_ref_no is NOT NULL and f.dCrbDate is NOT NULL and c.company_id in ($listComp) and a.cancelled <> 'Y' and ((a.work_force = '' and s.discType <> 'standard') or s.workingathomeaddress = '') and f.current_status not in ('Application Withdrawn','Application Form Lost') and f.rRDate is NULL and lla.application_id IS NULL";

  if(!empty($application_id))
  $selectAppLists.=" and a.application_id='$application_id'";

  if(!empty($refno))
  $selectAppLists.=" and f.app_ref_no='$refno'";
  

  if(!empty($orgId))
  $selectAppLists.=" and o.org_id='$orgId'";

  $selectAppLists.=" group by a.application_id ORDER by f.dCrbDate DESC";

  $resultAppLists = $this->getDBRecords($selectAppLists);

  return $resultAppLists;
 }
  /**
 * Function to get all Legacy Applications
 *
 * @param application id
 * @return array
 */
public function getAllInfoToBeSentToDBSReports($application_id=NULL,$filter=''){
  
  global $company_id,$orgId;
  
  if(empty($company_id)){
      $company_id = 1;
  }
          
  $logged_in_user = $this->getFromSession('user_id_M');
  
  $listComp=$this->getChildCompanies($company_id);
   
   $selectAppLists="select a.application_id,concat(upper(pnf.name) ,' ', upper(pns.name)) app_name,o.name org_name,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.discType as discType,f.current_status as status,a.work_force as work_force,s.workingathomeaddress as work_home,o.org_id,f.app_ref_no 
  from applications a
  inner join sectionx s on a.application_id = s.application_id
  inner join organisation o on a.org_id = o.org_id
  inner join company c on o.company_id = c.company_id INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
  INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id AND pnf.name_type_id =4
  INNER JOIN app_person_name apns ON a.application_id = apns.application_id
  INNER JOIN person_names pns ON apns.name_id = pns.name_id AND pns.name_type_id =3
  inner join form_status f on s.application_id = f.application_id
  INNER JOIN log_legacy_application lla ON a.application_id = lla.application_id
  left join log_print_dbsinforequest lpd on a.application_id = lpd.application_id
  where 
  s.app_ref_no is NOT NULL and f.app_ref_no is NOT NULL and f.dCrbDate is NOT NULL and c.company_id in ($listComp) and a.cancelled <> 'Y' 
  and f.current_status not in ('Application Withdrawn','Application Form Lost') and f.rRDate is NULL ";

    if($filter == 'P'){
      $selectAppLists .= " and lpd.application_id is NOT NULL";
  } elseif ($filter == "NP") {
      $selectAppLists .= " and lpd.application_id is NULL";
  }
  
  if(!empty($application_id))
  $selectAppLists.=" and a.application_id='$application_id'";

  if(!empty($refno))
  $selectAppLists.=" and f.app_ref_no='$refno'";
  

  if(!empty($orgId))
  $selectAppLists.=" and o.org_id='$orgId'";

  $selectAppLists.=" group by a.application_id ORDER by f.dCrbDate DESC";

  $resultAppLists = $this->getDBRecords($selectAppLists);
  return $resultAppLists;
 }
 
/**
 * function to insert record into log table for Inforamtionr requested by DBS
 * @param int $application_id
 * @param int $userid 
 */
public function insertIntoReports($application_id,$userid) {

     $query="select message_email,email,name,surname from users where user_id ='$userid' limit 1";
     $email_option=$this->getDBRecords($query);
     $email = $email_option[0]["email"];
    $curdate=date("YmdHis");
    $selectEbulkXml = "select application_id from log_ebulk_xml_error_email where application_id='$application_id' and sent_to_user_id='$userid'";
    $resultEbulkXml = $this->getDBRecords($selectEbulkXml);

    if (count($resultEbulkXml) == 0) {

        $query = "INSERT INTO `log_ebulk_xml_error_email` (`sent_to_user_id`, `application_id`, `sent_datentime`,`email`) VALUES ('$userid', '$application_id','$curdate','$email')";
        $res = $this->query($query);

        $query = "UPDATE form_status SET info_requested_by_dbs = 'Y' WHERE application_id = '$application_id'";
        $res = $this->query($query);
    }




    $selectEbulkXml = "select application_id from log_ebulk_xml_error where application_id='$application_id' and error_type_id='301'";
    $resultEbulkXml = $this->getDBRecords($selectEbulkXml);

    if (count($resultEbulkXml) == 0) {
        $query = "INSERT INTO `log_ebulk_xml_error` (`error_type_id`,`application_id`,`created_datentime`) VALUES ('301', '$application_id', '$curdate')";
        $res = $this->query($query);
    }
}

//Get company id from the organisation id
public function getCompId($orgId) {
        $query = "SELECT company_id FROM organisation WHERE org_id='$orgId'";
        $res = $this->getDBRecords($query);
        $compId = $res[0]["company_id"];
        return $compId;
    }
    
    /*************Function to send email to important people in case CRN number is not generated*********************/
    public function sendEmailToADL($applicationId) {
        if (!empty($applicationId)) {
            $arrRecipent[0] = 'niranjan.karkera@atlanticdata.co.uk';
            $arrRecipent[1] = 'naveen.kumar@atlanticdata.co.uk';
            $arrRecipent[2] = 'shrikantha.vadiraja@atlanticdata.co.uk';
            

            foreach ($arrRecipent as $value) {
                $msg = "
         Due to technical reasons, referral letter could not be generated. Please login again and try printing your referral letter after some time.<br/><br/><br/><b>Application Id : $applicationId</b><br/><br/>Kind Regards<br/> CQC";


                $msg2 = "Due to technical reasons, referral letter could not be generated. Please login again and try printing your referral letter after some time.
            Application Id : $applicationId
                Kind Regards
                CQC";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
                /* additional headers */
                $headers .= "From: Disclosures.co.uk\r\n";

                $mail = new htmlMimeMail();

                $text = $msg2;
                $html = $msg;

                $mail->setHtml($html, $text);
                $mail->setReturnPath('dbs@cqc.disclosures.co.uk');
                $mail->setFrom('dbs@cqc.disclosures.co.uk');
                $mail->setSubject('CQC Connection Failure URGENT!!!');
                $mail->setHeader('X-Mailer', 'HTML Mime mail class');
                $result = $mail->send(array($value), 'smtp');
            }
        }
    }
    /*************Function to send email to important people in case CRN number is not generated*********************/

	public  function checkValidCountyTown($town,$county,$postcode=''){
     $query="SELECT count(id) cnt FROM master_towncounty WHERE town='$town' AND county='$county'";
     if(!empty($postcode)){
         $firstpostcode=explode(" ",$postcode);
         $query.=" AND postcode='$firstpostcode[0]'";
     }
     $query.=" limit 1";
     $res=$this->getDBRecords($query);
     return $res[0]['cnt'];
 }
    
  public function getHMForceCardDropdownList($name,$initVal="",$js="")
  {
    $dList = "<SELECT name=\"$name\" $js class=\"selectbox\" id=\"$name\" onchange=\"changeHMFImg();\"><option value=\"\">Select from List</option>";
    $cards = $this->getDBRecords("select hm_forces_idcard_name as name, hm_forces_idcard_name as id from m_hm_forces_idcard_name where (deactivated_datentime IS NULL OR deactivated_datentime='') order by m_hm_forces_idcard_name_id");
    for($i=0;$i<count($cards);$i++)
    {

      if($initVal==$cards[$i]["id"])
       $sel = "selected";
      else
        $sel=null;
      $dList.= "<option $sel value=\"".$cards[$i]["id"]."\">".$cards[$i]["name"]."</option>";

    }
   $dList.= "</SELECT>";

    return $dList;
  }

}
?>
