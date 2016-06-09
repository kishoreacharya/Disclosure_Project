<?php
class Registration extends CommonLib {

    function __construct() {
        parent::__construct();
        return true;
    }
    
    /*
     * Function to fetch registration data
     */
    function getRegistrationData($intUserId) {
        $sqlRegistryData = "SELECT ur.answer FROM user_registration ur,users u WHERE ur.username=u.username and u.user_id = '".$intUserId."'";
        return $this->getDBRecords($sqlRegistryData);
    }

    /*
     * Function to insert all registration data
     * @$arrGlobalData : All Posted Data from Registration Page
     */
    function initiateRegistration($arrGlobalData) {
        #storing All Allowed POSTED fields into an array
        $allowAccessFields = array('firstname', 'lastname', 'email', 'orgname', 'orgpostcode','phone', 'securityQuestion', 'answer','orgtype','job_role');

        #Array Posting to Function
        $arrRegistryData = array();

        $arrRegistryData['firstname'] = trim($arrGlobalData['firstname']);
        $arrRegistryData['lastname'] = trim($arrGlobalData['lastname']);
        $arrRegistryData['middlename'] = trim($arrGlobalData['middlename']);
        $arrRegistryData['email'] = trim($arrGlobalData['email']);
        $arrRegistryData['orgname'] = trim($arrGlobalData['orgname']);
        $arrRegistryData['orgpostcode'] = trim($arrGlobalData['orgpostcode']);
        $arrRegistryData['phone'] = trim($arrGlobalData['telephoneNo']);
        $arrRegistryData['securityQuestion'] = trim($arrGlobalData['secutityQuestion']);
        $arrRegistryData['answer'] = trim($arrGlobalData['answer']);
        $arrRegistryData['orgtype'] = trim($arrGlobalData['orgtype']);
        $arrRegistryData['job_role'] = trim($arrGlobalData['jobposition']);
        $arrRegistryData['user_registration_type'] = empty($arrGlobalData['user_registration_type']) ? 'U' : trim($arrGlobalData['user_registration_type']);

        #Get Random Generated URN
        if(empty($arrGlobalData['oldusername']))
        $username = $this->getRandomURN($arrRegistryData);
        else
        $username = $arrGlobalData['oldusername'];

        #Get Random Generated Password
        if(empty($arrGlobalData['oldpassword']))
        $password = $this->getRandomPassword();
        else
        $password = $arrGlobalData['oldpassword'];

        #Get Current Unix Time
        $timeNow = time();

        #Generating unique Key with username and time
        $unique_key = $username."-".$timeNow;

        #Generating Transaction Key
        $transaction_key = strtoupper(md5(trim($unique_key)));

        #Put username value in the Posted Array
        $arrRegistryData["username"] = $username;

        #Put password value in the Posted Array
        $arrRegistryData["password"] = $password;

        #Put unique_key value in the Posted Array
        $arrRegistryData["unique_key"] = $unique_key;

        #Put transaction_key value in the Posted Array
        $arrRegistryData["transaction_key"] = $transaction_key;

        #Insert All Posted Value to the table "user_registration" table
        $result = $this->Insert("user_registration", $arrRegistryData);

        if (isset($result) && !empty($result)) {
          if (!empty($arrRegistryData['email'])) {
                #   convert the email to lowercase
                $arrRegistryData['email'] = stripslashes(strtolower($arrRegistryData['email']));
                #   validation for excat email and initate the funtion
                if (preg_match("/^[A-Za-z0-9'\.\\-_]+@[A-Za-z0-9\.\\-_]+\.[A-Za-z]+$/", $arrRegistryData['email'])) {
                    #   initialize the mail function

                    $sendDetails = array();
                    $sendDetails["emailReplyTo"] = DBS_EMAIL."@cqc.disclosures.co.uk";
                    $sendDetails["emailFrom"] = DBS_EMAIL."@cqc.disclosures.co.uk";
                    $this->sendRegistrationEmail($arrRegistryData, $sendDetails);
                    #   track the mail log for referance
                }
            }
        }
    return $result;
    }

    /*
     * Get the max Registered User Id
     */
    public function getMaxRegUser() {
        $sqlSelect = "SELECT max(id) AS id FROM user_registration";
        return $this->getDBRecords($sqlSelect);
    }

    /*
     * Send Activation Email to Registred Users.
     */
    public function sendRegistrationEmail($regUserDetails, $sendDetails) {
        
        $appName = $this->correctcase($regUserDetails['firstname']." ".$regUserDetails['lastname']);
        $msg="Thank you for your application for a CQC countersigned Disclosure and Barring Service (DBS) check.<br><br>
        You will receive an Email confirming your username and password which will allow you to access your online application.<br><br>
        Please be aware this may take up to 5 working days as this is not an automated service.
         <br><br><b>".
        strtoupper($regUserDetails['firstname'])."";
        if (!empty($regUserDetails['middlename'])) 
        {
            $msg.="<br>".
            strtoupper($regUserDetails['middlename'])."";
        }
        $msg.="<br>"
        .strtoupper($regUserDetails['lastname']). "</b><br><br>
        Kind regards<br>
        Care Quality Commission";   
        
        
        $msg2="Thank you for your application for a CQC countersigned Disclosure and Barring Service (DBS) check.
            
          You will receive an Email confirming your username and password which will allow you to access your online application.
        
          Please be aware this may take up to 5 working days as this is not an automated service."
                
        .strtoupper($regUserDetails['firstname'])." "
        .strtoupper($regUserDetails['middlename'])." "
        .strtoupper($regUserDetails['lastname']).  
                
        "Kind regards
        Care Quality Commission";        
                
        /*$msg = "Dear ".$appName."<br><br>
        We have issued an Incorporated to
        view the result visit your online account.
        <br><br><br>

        Kind regards<br><br>
        Care Quality Commission";


        $msg2 = "Dear ".$appName."
        We have issued an Incorporated to
        view the result visit your online account.
        Kind regards
        Care Quality Commission";*/

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        /* additional headers */
        $headers .= "From: Disclosures.co.uk\r\n";

        $mail = new htmlMimeMail();

        $text = $msg2;
        $html = $msg;

        $mail->setHtml($html, $text);
        $mail->setReturnPath($sendDetails["emailReplyTo"]);
        $mail->setFrom($sendDetails["emailFrom"]);
        $mail->setSubject('Submission Acknowledgement');
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
        $result = $mail->send(array($regUserDetails['email']), 'smtp');
    }

    /*
     * Get User Security Questions
     */
    public function getUserSecurityQuestion($userId){
        $sqlSelect = "SELECT username,id,securityQuestion FROM user_registration WHERE id ='$userId'";
        return $this->getDBRecords($sqlSelect);
    }
    
    /*
     * Get all master Security Questions
     */
    public function getSecurityQuestions($questionId=""){    
        if(!empty($questionId))
                $checkCondition = " WHERE id='$questionId'";
                
        $sqlSelect = "SELECT id,question FROM security_questions $checkCondition";
        return $this->getDBRecords($sqlSelect);
    }

    /*
     * Get all master organisation types
     */
    function getOrgTypes()
    {
        $sqlSelect = "SELECT id,description FROM organisation_type WHERE active = 'Y'";
        return $this->getDBRecords($sqlSelect);
    }

     /*
     * Get User Security Questions
     */
    public function getUserSecurityQuestionDetails($userId){
        $sqlSelect = "SELECT u.username,user_id as id,ur.securityQuestion FROM user_registration ur,users u WHERE ur.username = u.username and u.user_id ='$userId'";
        return $this->getDBRecords($sqlSelect);
    }
}

?>
