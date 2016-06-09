<?php

class PrintApplicant extends CommonLib 
{

    function __construct() {
        parent::__construct();
        return true;
    }
    
   /*
    * Function to update as printed
    * param 1  : appId  : Application id
    * return   : false
    */
    function setPrintedCorrectly($appId)
    {
        $arrPrintedCorrectly['printed_correctly'] = 'Y';
        $condition = "application_id = '$appId' limit 1";
        
        $this->Update('applications', $arrPrintedCorrectly, $condition);
        
    }
    /*
     * Function to update as printed ends
     */
    
    /*
     * Function to send mail to applicant with new username and password
     * param 1  : appId : Applicantion id
     */
    function sendMailToApplicant($appId)
    {
        $query = "SELECT u.username,u.password,concat(u.name,' ',u.surname) as name,ur.email,a.org_id FROM `users`  u inner join reqdocs r on u.user_id = r.user_id inner join applications a on r.app_id = a.application_id inner join user_registration ur on u.unique_key = ur.username WHERE a.application_id = '$appId'";
        $res = $this->getDBRecords($query);
        
         if (!empty($res[0]['email'])) {
                #   convert the email to lowercase
                $res[0]['email'] = stripslashes(strtolower($res[0]['email']));
                #   validation for excat email and initate the funtion
                if (preg_match("/^[_a-z0-9'-]+(\.[_a-z0-9'-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $res[0]['email'])) {
                    #   initialize the mail function

                    $orgId = $res[0]['org_id'];
                    $compId = $this->getCompId($orgId);
                    $sendDetails = array();
                    if($compId==3)
                    {
                    $sendDetails["emailReplyTo"] = DBS_EMAIL."hr"."@cqc.disclosures.co.uk";
                    $sendDetails["emailFrom"] = DBS_EMAIL."hr"."@cqc.disclosures.co.uk";
                    }
                    else
                    {
                    $sendDetails["emailReplyTo"] = DBS_EMAIL."@cqc.disclosures.co.uk"; //eschelpdesk@leics.gov.uk
                    $sendDetails["emailFrom"] = DBS_EMAIL."@cqc.disclosures.co.uk"; //eschelpdesk@leics.gov.uk
                    }
                    $this->sendEmail($res, $sendDetails);
                    #   track the mail log for referance

                   
                }
        }
     
    }
    
    /*
     * Function to send mail to applicant with new username and password
     */
    
    /**
     *
     * Send Login credintials Email to Users.
     * param1 : $regUserDetails : Applicant details
     * param2 : $sendDetails    : Sent from details
     *
     * */
    public function sendEmail($appDetails, $sendDetails) {
        $appName = $this->correctcase($appDetails[0]['name']);
        $msg = "Dear " . $appName.",<br/><br/>
        Thank you for submitting your Care Quality Commission (CQC) ".DBS." application.<br/><br/>

        Please take your applicant referral letter with your proof of identity used in the application and any fee to the Post Office.  You must attend the Post Office before the expiry date for the application which is quoted in the letter.<br/><br/>
        
        Below are your login details: <br/><br/>
        <b>URL: https://cqc.disclosures.co.uk<br/>
        USERNAME: ".$appDetails[0]['username'].
           "<br>PASSWORD: ".$appDetails[0]['password'].
                "</b><br><br>
        If you have lost or forgotten to print your letter please access the above link and you can reprint your referral letter.<br/><br/>   
        Please login regularly until you receive your Enhanced ".DBS_CERT." as there may be information on the communications dashboard.<br/><br/>
        Kind Regards<br/><br/>
        ".DBS." team<br/>
                 Care Quality Commission
                 <br><br>
                 The contents of this email and any attachments are confidential to intended recipient. They may not be disclosed to or used by or copied in any way by anyone other than the intended recipient or your representative who acts on your behalf.";


        $msg2 = "Dear " . $appName .",
         Thank you for submitting your Care Quality Commission (CQC) ".DBS." application.

        Please take your applicant referral letter with your proof of identity used in the application and any fee to the Post Office.  You must attend the Post Office before the expiry date for the application which is quoted in the letter.

        Below are your login details:
        URL: https://cqc.disclosures.co.uk
        USERNAME: ".$appDetails[0]['username'].
         "PASSWORD: ".$appDetails[0]['password'].
                
                "If you have lost or forgotten to print your letter please access the above link and you can reprint your referral letter.  
        Please login regularly until you receive your Enhanced ".DBS_CERT." as there may be information on the communications dashboard.
        
                Kind Regards
                
                ".DBS." team
                 Care Quality Commission
                 
                 The contents of this email and any attachments are confidential to intended recipient. They may not be disclosed to or used by or copied in any way by anyone other than the intended recipient or your representative who acts on your behalf.";

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
        $mail->setSubject('Submission of the application');
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
        $result = $mail->send(array($appDetails[0]['email']), 'smtp');
    }
    
    
  
}

?>
