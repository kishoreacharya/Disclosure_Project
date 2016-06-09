<?php
require("class.generateBarcode.php");
require("class.pdfLetter.php");
class ReferralLetter extends CommonLib 
{
    
    public $PADDING_CHARACTER  = '';
    public $FONT               = 2;
    public $IMAGE_TYPE         = 'png';
    public $BARCODE_TYPE       = 'C128A';
    public $WIDTH              = 330; //305 changed to 330
    public $HEIGHT             = 60;
    public $XRES               = 1;
    public $ID_PATH            = 'barcode/ID_Barcode/';
    public $POD_PATH           = 'barcode/Personal_Barcode/';
    public $TOTAL_FEE          = 0;
    public $IB_BARCODE_TYPE    = 'C128C';
    public $IB_WIDTH           = 430;
    public $IB_HEIGHT          = 60;
    public $IB_XRES            = 2;
    public $IB_PATH            = 'barcode/Initiating_Barcode/';
    public $IIN                = '';
    public $USEDBYDATE         = '';
    public $MAXDAYS            = " +28 day";
    public $MAXMONTHS          = " +12 month";
    public $MINMONTHS          = " +3 month";
    public $DOORNO             = "";
    public $APPREFNUM          = "";
    public $DISCTYPE           = "";
    public $POL                = "";
    public $CLIENTFEE          = "";
    public $CLIENTFEECODE      = "";
    public $CLIENTID           = "";
    public $CRBFEE             = "";
    public $SERVICECODE        = "";
    public $SUBMIT_TIME        = "";
    public $DOC_EXPIRES        = "N";
    public $_compId        = 1;
     
    function __construct() 
    {
        parent::__construct();
        return true;
    }
    
    /*
     * Function to get process referal letter
     * param1   : appId  : Application id
     */
    
    function processRefLetter($appId,$remuneration,$CRN,$orgId,$actionType,$company_id)
    {
        $this->_compId = $company_id;
        $applicantDetails = $this->getApplicantDetails($appId);
        $applicantMidNameDetails = $this->getMiddleNames($appId);
        $documentSelected = $this->getSelectedDocuments($appId);
        $app_email        = $this->getEmail($appId);     
        $this->getClientDetails($appId);
        $documentCount    = count($documentSelected);
        if($documentCount == 4 || $documentCount > 5)
        {
            $newDocList = $this->removeDoc($documentSelected,$documentCount);
            $documentSelected =  array();
            $documentSelected = array_reverse($newDocList);
            $documentCount    = count($documentSelected);
        }
        $podBarcode       = $this->getPODBarcode($applicantDetails, $applicantMidNameDetails);
        $idDocBarcode     = $this->getIdBarcodes($documentSelected);
        $barcodeDetails   = $this->getBarcodeDetailsFromDB($appId);
        if(!empty($barcodeDetails))
        {
            if($barcodeDetails[0]['padding_character'] != '')
            {
                $this->PADDING_CHARACTER = $barcodeDetails[0]['padding_character'];
            }
            else
            {
                $this->getPaddingCharacter($podBarcode, $idDocBarcode);
            }
        }
        else
        {
            $this->getPaddingCharacter($podBarcode, $idDocBarcode);
        }
        
        $this->generateIdbarcode($idDocBarcode, $documentCount, $appId);
        $this->generatePODBarcode($podBarcode, $appId);
        
        $this->getUsedByDate($documentSelected);
        $initiatingBarcode = $this->generateInitiatingBarcode($appId, $documentCount,$remuneration,$CRN);

        $this->generateLetterPDF($applicantDetails, $applicantMidNameDetails, $documentSelected,$appId,$podBarcode,$idDocBarcode,$initiatingBarcode,$app_email,$orgId,$actionType);
        
        
       
        
    }
    
    
//    function processRefLetter_applogin($appId,$remuneration)
//    {
//        $applicantDetails = $this->getApplicantDetails($appId);
//        $applicantMidNameDetails = $this->getMiddleNames($appId);
//        $documentSelected = $this->getSelectedDocuments($appId);
//        $this->getClientDetails($appId);
//        $documentCount    = count($documentSelected);
//        $barcodeDetails   = $this->getBarcodeDetailsFromDB($appId);
//        $podBarcode       = $barcodeDetails[0]['personal_details_barcode'];
//        
//        $idDocBarcode     = $this->arrIdBarcodeDetails($documentCount,$barcodeDetails);
//       $this->USEDBYDATE = $barcodeDetails[0]['usedby_date'];
//       
//        $initiatingBarcode = $this->generateInitiatingBarcode($appId, $documentCount,$remuneration);
//
//        $this->generateLetterPDF($applicantDetails, $applicantMidNameDetails, $documentSelected,$appId,$podBarcode,$idDocBarcode,$initiatingBarcode);
//        
//        
//       
//        
//    }
    
    function getBarcodeDetailsFromDB($appId)
    {
        $query = "SELECT * FROM barcode_detail WHERE application_id = '$appId' order by barcode_detail_id DESC LIMIT 1";
        $res   = $this->getDBRecords($query);
        return $res;
    }
    
    
    
    function arrIdBarcodeDetails($docCount,$barcodeDetails)
    {
       $idDocBarcode[0] = $barcodeDetails['id_doc1_barcode'];
       $idDocBarcode[1] = $barcodeDetails['id_doc2_barcode'];
       $idDocBarcode[2] = $barcodeDetails['id_doc3_barcode'];
       
       if($docCount == '4')
       {
           $idDocBarcode[3] = $barcodeDetails['id_doc4_barcode'];
       }
       if($docCount == '5')
       {
           $idDocBarcode[3] = $barcodeDetails['id_doc4_barcode'];
           $idDocBarcode[4] = $barcodeDetails['id_doc5_barcode'];
       }
       return $idDocBarcode;
    }
    /*
     * Function to get applicant details end
     * 
     */
    
    /*
     * Function containing applicant 
     * param1 : appId : Applicantion id
     * return : true  : Applicant details
     */
    function getApplicantDetails($appId)
    {
        $appQuery = "SELECT  pnf.name as forname, pns.name as surname,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) as dob,addr .address1,addr .town_city,addr .postcode,s.discType,from_unixtime(submit_time,'%Y-%m-%d') submit_time
                    FROM applications a
                   
                    INNER JOIN app_person_name apnf on a.application_id = apnf.application_id
                    INNER JOIN person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
                    INNER JOIN app_person_name apns on a.application_id = apns.application_id
                    INNER JOIN person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
                    INNER JOIN app_address apadd on a.application_id = apadd.application_id
                    INNER JOIN address addr on apadd.address_id = addr .address_id 
                    INNER JOIN sectionx s on a.application_id = s.application_id
                    WHERE a.application_id = '$appId'";
        $resQuery = $this->getDBRecords($appQuery);
        $this->SUBMIT_TIME = $resQuery[0]['submit_time'];
        $discType = $resQuery[0]["discType"];
        if($discType == 'enhanced')
        {
            $this->DISCTYPE = 'E';
        }
        else if($discType == 'basic')
        {
            $this->DISCTYPE = 'B';
        }
        else if($discType == 'standard')
        {
            $this->DISCTYPE = 'S';
        }
        else
        {
            $this->DISCTYPE = 'A';
        }
        
        return $resQuery;
        
    }
    /*
     * Function containing applicant  end
     */
    
    
    /*
     * Function to get Applicant middle names
     * param1 : appId  : Application id
     * return : true   : Middle names
     */
    function getMiddleNames($appId)
    {
        $midNameQuery = "SELECT pn.name as middlename
                         FROM person_names pn
                         INNER JOIN app_person_name apn on pn.name_id = apn.name_id and pn.name_type_id in (9,10)
                         INNER JOIN applications a on apn.application_id = a.application_id
                         WHERE a.application_id = '$appId'";
        $resmidNameQuery = $this->getDBRecords($midNameQuery);
        return $resmidNameQuery;
    }
    /*
     * Function to get Applicant middle names end
     */
    
    
    /*
     * Function to get selected document details
     * param1   : appId  : Application id
     * return   : true   : Selected document details
     */
    function getSelectedDocuments($appId)
    {
        $docQuery ="select b.*,a.*,b.nationality ppnationality from applications a, sectionx b where a.application_id=b.application_id and a.application_id='$appId'";
        $resdocQuery=$this->getDBRecords($docQuery);
        
        $reqDocsQuery="select reqdocs from reqdocs where app_id ='$appId'";
        $resreqDocs=$this->getDBRecords($reqDocsQuery);
        $req=$resreqDocs[0]['reqdocs'];
        $documentSelected = $this->processDocumentSelected($req, $resdocQuery);
        return $documentSelected;
        
    }
    /*
     * Function to get selected document details end
     */

    /*
     * Function to process documnet selected
     * param1   :  reqDocs     : Required docs
     * param2   :  docSelected : Document selected
     * return   : true         : Processed documents
     */
    function processDocumentSelected($req,$docSelected)
    {
         if(!empty($req)) {
            $appVars = explode("||",$req);
            #get an array of parameters posted.
            for($i=0;$i<count($appVars);$i++) {
                $keyVal = explode("~",$appVars[$i]);
                $$keyVal[0]=$keyVal[1];
            }
        }
        
        $count = 0;        //Passport
        if($a1=="Y") {
            $documentList = $this->getDocList("Passport", "PASSPORT", $this->getFormatedDate($docSelected[0]["passport_issue_date"],$format=3), substr($docSelected[0]["passport_no"], 0,4), $this->getFormatedDate($docSelected[0]["passport_expiry_date"],$format=3),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Passport";
//            $documentList[$count]["Short Name"] = "PASSPORT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["passport_issue_date"],$format=3);
//            $documentList[$count]["Ref"] = substr($docSelected[0]["passport_no"], 0,4);
//            $documentList[$count]["Expiry Date"] = $this->getFormatedDate($docSelected[0]["passport_expiry_date"],$format=3);
            $count++;
        }
        //Driving licence
        if($a2=="Y" || $b55 =="Y" || $b56 == "Y") {
            $documentList = $this->getDocList("Driving Licence", "DRIVING LIC", $this->getFormatedDate($docSelected[0]["dl_valid_from"],$format=3), substr($docSelected[0]["driving_liscence_no"], 0,4), $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Driving Licence";
//            $documentList[$count]["Short Name"] = "DRIVING LIC";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["dl_valid_from"],$format=3);
//            $documentList[$count]["Ref"] = substr($docSelected[0]["driving_liscence_no"], 0,4);
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
            

        }
        // Birth certificate
        if($a3=="Y" ) 
            {
             if($docSelected[0]["bc_issue_date"] != '')$birthcerissuedate = $docSelected[0]["bc_issue_date"];
            elseif($docSelected[0]["foreign_bc_issue_date"] != '')$birthcerissuedate = $docSelected[0]["foreign_bc_issue_date"];
            $documentList = $this->getDocList("Birth Certificate <12 months from DOB", "BIRTH CERT1", $this->getFormatedDate($birthcerissuedate,$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Birth Certificate <12 months from DOB";
//            $documentList[$count]["Short Name"] = "BIRTH CERT1";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["bc_issue_date"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;

        }
        
            if($a11=="Y") {
            $bioExpiryDate = '';
            if(!empty($docSelected[0]["biometric_expirydate"])) $bioExpiryDate =  $this->getFormatedDate($docSelected[0]["biometric_expirydate"],$format=3);
            else $bioExpiryDate = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $documentList = $this->getDocList("Biometric", "BIOMETRIC", $this->getFormatedDate($docSelected[0]["biometric_issuedate"],$format=3), substr($docSelected[0]["biometric_no"], 0,4), $bioExpiryDate,$count,$documentList);
            $count++;
        }
        
        if($b1=="Y")
        {
             if($docSelected[0]["bc_issue_date"] != '')$birthcerissuedate = $docSelected[0]["bc_issue_date"];
            elseif($docSelected[0]["foreign_bc_issue_date"] != '')$birthcerissuedate = $docSelected[0]["foreign_bc_issue_date"];
            $documentList = $this->getDocList("Birth Certificate >12 months from DOB", "BIRTH CERT2", $this->getFormatedDate($birthcerissuedate,$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Birth Certificate >12 months from DOB";
//            $documentList[$count]["Short Name"] = "BIRTH CERT2";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["bc_issue_date"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
        }
        // Adoption certificate
        if($b57=="Y" )
        {
            $documentList = $this->getDocList("Adoption Certificate", "ADOPT CERT", $this->getFormatedDate($docSelected[0]["adop_cert_dt"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Adoption Certificate";
//            $documentList[$count]["Short Name"] = "ADOPT CERT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["adop_cert_dt"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
            


        }


//        //Foreign Birth Certificate
//        if($b33=="Y" ) {
//            $smarty->assign("birthCertDOB",getFormatedDate($appres[0]["foreign_bc_DOB"],$format=3));
//
//
//            $smarty->assign("birthCertCountry",$appres[0]["foreign_bc_issue_country"].getCountryCode($appres[0]["foreign_bc_issue_country"]));
//            $smarty->assign("birthCertIssueDt",getFormatedDate($appres[0]["foreign_bc_issue_date"],$format=3));
//
//
//        }
        //P45
        if($b11=="Y" ) {
            $documentList = $this->getDocList("P45/P60", "P45 P60", $this->getFormatedDate($docSelected[0]["pniNumberIssueDt"],$format=3), substr($docSelected[0]["ni_no"], 0,4),  $this->getExpirationDate($this->getFormatedDate($docSelected[0]["pniNumberIssueDt"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "P45/P60";
//            $documentList[$count]["Short Name"] = "P45 P60";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["pniNumberIssueDt"],$format=3);
//            $documentList[$count]["Ref"] = substr($docSelected[0]["ni_no"], 0,4);
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate($this->getFormatedDate($docSelected[0]["pniNumberIssueDt"],$format=3), ' +12 months');
            $count++;
        
        }

        //P45
        if($b18=="Y" ) {
            $documentList = $this->getDocList("P45/P60", "P45 P60", $this->getFormatedDate($docSelected[0]["p60IssueDt"],$format=3), substr($docSelected[0]["p60ni_no"], 0,4), $this->getExpirationDate($this->getFormatedDate($docSelected[0]["p60IssueDt"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "P45/P60";
//            $documentList[$count]["Short Name"] = "P45 P60";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["p60IssueDt"],$format=3);
//            $documentList[$count]["Ref"] = substr($docSelected[0]["p60ni_no"], 0,4);
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate($this->getFormatedDate($docSelected[0]["p60IssueDt"],$format=3), ' +12 months');
            $count++;
        

        }
        //marriage certificate
        if($b2=="Y" ) {
            $documentList = $this->getDocList("Marriage Certificate", "MARR CERT", $this->getFormatedDate($docSelected[0]["marriage_cert_issue_date"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Marriage Certificate";
//            $documentList[$count]["Short Name"] = "MARR CERT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["marriage_cert_issue_date"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
            
        }

        //photo ID card

        if($b52=="Y" ) {     
             $euExpiryDate = '';
            if(!empty($docSelected[0]["photo_id_expiry"])) $euExpiryDate =  $this->getFormatedDate($docSelected[0]["photo_id_expiry"],$format=3);
            else $euExpiryDate = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $documentList = $this->getDocList("EU National ID Card", "EU ID CARD", $this->getFormatedDate($docSelected[0]["photo_id_issue"],$format=3), '    ', $euExpiryDate,$count,$documentList);

            $count++;
           
        }
        
         //photo ID card

        if($b58=="Y" ) {           
            $documentList = $this->getDocList("HM Forces ID Card", "HM FORCES", $this->getFormatedDate($docSelected[0]["hm_forces_idcard_expiry_date"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);

            $count++;
           
        }
        
         //Letter from Head Teacher or College Principal 

        if($b54=="Y" ) {
            $documentList = $this->getDocList("Head Teacher Letter", "HEAD TEACHER", $this->getFormatedDate($docSelected[0]["headteacherletter_issuedt"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList,'b54');
            $count++;
           
        }

        //firearm
        if($b59=="Y" ) {
            $documentList = $this->getDocList("Firearms Licence", "FIREARMS LIC",$this->getFormatedDate($docSelected[0]["firearms_cert"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Firearms Licence";
//            $documentList[$count]["Short Name"] = "FIREARMS LIC";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["firearms_cert"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
           
        }
        // Certificate of british nationality
        if($b3=="Y" ) {
            $documentList = $this->getDocList("Certificate of British Nationality", "BRIT CERT",$this->getFormatedDate($docSelected[0]["british_nat_cert"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Certificate of British Nationality";
//            $documentList[$count]["Short Name"] = "BRIT CERT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["british_nat_cert"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
           

          

        }
        //Valid Vehicle Registration Document
        if($b4=="Y" ) {
            $documentList = $this->getDocList("Vehicle Registration Document", "V5 VEHICLE", $this->getFormatedDate($docSelected[0]["veh_reg_doc"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Vehicle Registration Document";
//            $documentList[$count]["Short Name"] = "V5 VEHICLE";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["veh_reg_doc"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;

        }
        //Valid NHS Card (UK)

        if($b5=="Y" ) {
            $documentList = $this->getDocList("NHS Card", "NHS CARD", $this->getFormatedDate($docSelected[0]["nhs_card"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "NHS Card";
//            $documentList[$count]["Short Name"] = "NHS CARD";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["nhs_card"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
            
        }

        //National Insurance Card (UK)
        if($b6=="Y" ) {
            $documentList = $this->getDocList("NI Card", "NI CARD", '', substr($docSelected[0]["national_ins_card"], 0,4), $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "NI Card";
//            $documentList[$count]["Short Name"] = "NI CARD";
//            $documentList[$count]["Issue Date"] = '';
//            $documentList[$count]["Ref"] = substr($docSelected[0]["national_ins_card"], 0,4);
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            $count++;
            
        }

        //Exam Certificate
        if($b7=="Y" ) {
            $documentList = $this->getDocList("Exam Certificate", "EXAM CERT", $this->getFormatedDate($docSelected[0]["exam_cert"],$format=3), '    ', $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Exam Certificate";
//            $documentList[$count]["Short Name"] = "EXAM CERT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["exam_cert"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
//            $documentList[$count]["Expiry Date"] = $this->getExpirationDate(date('Y-m-d'), $this->MAXDAYS);
            
            $count++;
            
        }

        //Connexions Card (UK)
        if($b8=="Y" ) {
            $documentList = $this->getDocList("Connexions Card", "CONNEX CARD", $this->getFormatedDate($docSelected[0]["connex_care"],$format=3), '    ', $this->getFormatedDate($docSelected[0]["connex_card_exp"],$format=3),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Connexions Card";
//            $documentList[$count]["Short Name"] = "CONNEX CARD";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["connex_care"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }

        //Valid TV Licence
        if($b15=="Y" ) {
            $documentList = $this->getDocList("TV Licence", "TV LICENCE", $this->getFormatedDate($docSelected[0]["television_license"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["television_license"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "TV Licence";
//            $documentList[$count]["Short Name"] = "TV LICENCE";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["television_license"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
          
        }

        //Mortgage Statement
        if($b14=="Y" ) {
            $documentList = $this->getDocList("Mortgage Statement", "MORT STAT", $this->getFormatedDate($docSelected[0]["mortage_statement"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["mortage_statement"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Mortgage Statement";
//            $documentList[$count]["Short Name"] = "MORT STAT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["mortage_statement"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
           
        }
        //Valid Insurance Certificate
        if($b16=="Y" ) {
            $documentList = $this->getDocList("Insurance Certificate", "INS CERT",$this->getFormatedDate($docSelected[0]["ins_cert"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["ins_cert"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Insurance Certificate";
//            $documentList[$count]["Short Name"] = "INS CERT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["ins_cert"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }

        //British Work Permit / VISA (UK)
        if($b17=="Y" ) {
            $documentList = $this->getDocList("Work Permit/Visa", "WORK PERMIT", $this->getFormatedDate($docSelected[0]["work_permit"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["work_permit"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Work Permit/Visa";
//            $documentList[$count]["Short Name"] = "WORK PERMIT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["work_permit"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
          
        }
        //Financial Statement (pension, endowment, ISA etc)

        if($b9=="Y" ) {
            $documentList = $this->getDocList("Financial Statement", "FINAN STATE", $this->getFormatedDate($docSelected[0]["financial_statement"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["financial_statement"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Financial Statement";
//            $documentList[$count]["Short Name"] = "FINAN STATE";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["financial_statement"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
        //Mail Order Catalogue Statement

        if($b19=="Y" ) {
            $documentList = $this->getDocList("Mail Order Catalogue Statement", "MAIL ORDER", $this->getFormatedDate($docSelected[0]["mail_ord_catalog"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["mail_ord_catalog"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Mail Order Catalogue Statement";
//            $documentList[$count]["Short Name"] = "MAIL ORDER";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["mail_ord_catalog"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
         
        }

//        //Child Benefit book
//        if($b12=="Y" ) {
//            $documentList[$count]["Document Name"] = "Firearms Licence";
//            $documentList[$count]["Short Name"] = "BIRTH CERT2";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["firearms_cert"],$format=3);
//            $documentList[$count]["Ref"] = '';
//            $count++;
//            $smarty->assign("childBenefitBook",getFormatedDate($appres[0]["child_benifit_book"],$format=3));
//        }
        //Bank/Building Society Statement

        if($b20=="Y" ) {
            $documentList = $this->getDocList("Bank or Building Society Statement", "BANK STATE", $this->getFormatedDate($docSelected[0]["bank_statement"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["bank_statement"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Bank or Building Society Statement";
//            $documentList[$count]["Short Name"] = "BANK STATE";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["bank_statement"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
           
        }

        //Electricity


        if($b28=="Y" ) {
            $documentList = $this->getDocList("Electricity Bill", "UTILITY BILL", $this->getFormatedDate($docSelected[0]["elec_bill"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["elec_bill"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "UTILITY BILL";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["elec_bill"],$format=3);
//            $documentList[$count]["Ref"] = '';
            $count++;
            
        }

        //gas


        if($b29=="Y" ) {
            $documentList = $this->getDocList("Gas Bill", "UTILITY BILL", $this->getFormatedDate($docSelected[0]["gas_bill"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["gas_bill"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "UTILITY BILL";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["gas_bill"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }

        //Water bill
        if($b30=="Y" ) {
            $documentList = $this->getDocList("Water Bill", "UTILITY BILL", $this->getFormatedDate($docSelected[0]["water_bill"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["water_bill"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "UTILITY BILL";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["water_bill"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
        //Water bill
        if($b31=="Y" ) {
            $documentList = $this->getDocList("Telephone Bill", "UTILITY BILL", $this->getFormatedDate($docSelected[0]["telphone_bill"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["telphone_bill"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "UTILITY BILL";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["telphone_bill"],$format=3);
//            $documentList[$count]["Ref"] = '';
            $count++;
            
        }

        //Mobile bill
        if($b32=="Y" ) {
            $documentList = $this->getDocList("Utility Bill", "UTILITY BILL", $this->getFormatedDate($docSelected[0]["mobile_bill"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["mobile_bill"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "UTILITY BILL";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["mobile_bill"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
           
        }
        //credit card
        if($b21=="Y" ) {
            $documentList = $this->getDocList("Credit Card Statement", "CR CARD STAT", $this->getFormatedDate($docSelected[0]["credit_care_statement"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["credit_care_statement"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Credit Card Statement";
//            $documentList[$count]["Short Name"] = "CR CARD STAT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["credit_care_statement"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
        //store card
        if($b22=="Y" ) {
            $documentList = $this->getDocList("Store Card Statement", "ST CARD STAT", $this->getFormatedDate($docSelected[0]["store_card_statement"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["store_card_statement"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Store Card Statement";
//            $documentList[$count]["Short Name"] = "ST CARD STAT";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["store_card_statement"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
//        //
  //Benefit Statement - e.g. Child Allowance, Pension
        if($b24=="Y" ) {
            $documentList = $this->getDocList("Benefit Statement", "BENEFIT STAT", $this->getFormatedDate($docSelected[0]["cor_benefit_agency"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["cor_benefit_agency"],$format=3), $this->MINMONTHS),$count,$documentList);
            $count++;
            
        }

        //A document from Central/ Local Government/ Government Agency/ Local Authority giving entitlement (UK & Channel Islands) - e.g. from the Department for Work and Pensions, the Employment Service , Customs & Revenue, Job Centre, Job Centre Plus, Social Security.
        if($b27=="Y" ) {
            $documentList = $this->getDocList("Entitlement Document", "GOVNT DOC", $this->getFormatedDate($docSelected[0]["entitlement_doc_issue_dt"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["entitlement_doc_issue_dt"],$format=3), $this->MINMONTHS),$count,$documentList,'b27');
            $count++;
            
        }
        
        if($b13=="Y" ) {
            $documentList = $this->getDocList("Council Tax Statement", "COUNCIL TAX", $this->getFormatedDate($docSelected[0]["council_tax"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["council_tax"],$format=3), $this->MAXMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Council Tax Statement";
//            $documentList[$count]["Short Name"] = "COUNCIL TAX";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["council_tax"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
        if($b37=="Y" ) {
            $documentList = $this->getDocList("Utility Bill", "Utility Bill", $this->getFormatedDate($docSelected[0]["disclosure_issue_date"],$format=3), '    ', $this->getExpirationDate($this->getFormatedDate($docSelected[0]["disclosure_issue_date"],$format=3), $this->MINMONTHS),$count,$documentList);
//            $documentList[$count]["Document Name"] = "Utility Bill";
//            $documentList[$count]["Short Name"] = "Utility Bill";
//            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["disclosure_issue_date"],$format=3);
//            $documentList[$count]["Ref"] = '    ';
            $count++;
            
        }
        #Split the documents
        
        return $documentList;
    }
    /*
     * Function to process document selected end
     */

    /*
     * Function to generate id doc barcodes
     * param 1  : documentSelected : List of document selected
     * param 2  : documentCount    : Number of document selected
     * param 3  : appId            : Applicant id
     */
    function generateIdbarcode($idDocBarcode,$documentCount,$appId)
    {
        for($i = 0;$i < $documentCount;$i++)
        {
            $scanSeq = $i + 2;
            $barcode    = str_replace(' ',$this->PADDING_CHARACTER, $idDocBarcode[$i]);
            
            $objGenerateBarcode = new Cgenbarcode();
            $objGenerateBarcode->genBarcode(trim($barcode),$this->IMAGE_TYPE, $this->BARCODE_TYPE, $this->WIDTH,$this->HEIGHT,$this->XRES,$this->FONT, $this->ID_PATH.'app_'.$appId.'_'.$scanSeq.'_IDB.png');
        }
    }
    
    /*
     * Function to generate id doc barcodes ends
     */
    
    /*
     * Function to generate personal details barcodes
     */
    function generatePODBarcode($podBarcode,$appId)
    {
        $barcode    = str_replace(' ',$this->PADDING_CHARACTER, $podBarcode);

        $objGenerateBarcode = new Cgenbarcode();
        $objGenerateBarcode->genBarcode(trim($barcode),$this->IMAGE_TYPE, $this->BARCODE_TYPE, $this->WIDTH,$this->HEIGHT,$this->XRES,$this->FONT, $this->POD_PATH.'app_'.$appId.'_1'.'_PODB.png');
    }
    /*
     * Function to generate personal details barcodes ends
     */
    
    /*
     * Function to get formate document short name
     * param 1  :  short_name : Short name of the document
     * return   :  true       : Returns formated short name
     */
    function formateShortName($short_name)
    {
        for($i = 0;$i < 12 - strlen(trim($short_name));$i++)
        {
            $short_name .= ' '; 
        }
        return $short_name;
    }
    /*
     * Function to get formate document short name ends
     */
    
    /*
     * Function to get padding character
     * param  :  applicant_details  : Details of applicant
     * param  :  document_deatils   : Details of document selected
     */
    function getPaddingCharacter($podBarcode,$idDocBarcode)
    {
        $strBarcode = $podBarcode;
        
        for($i =0; $i< count($idDocBarcode);$i++)
        {
            $strBarcode .= $idDocBarcode[$i];
        }
        $paddingChar = $this->generateRandomPaddingChar($strBarcode);
        
        $this->PADDING_CHARACTER = $paddingChar;
    }
    /*
     * Function to get padding character ends
     */
   
    
    /*
     * Function to get barcodes
     * param 1  : documentSelected     : List of document selected
     * return   : true                 : ID Barcodes
     */
    function getIdBarcodes($documentSelected)
    {

        $documentCount  = count($documentSelected);
         $docBarcode;
         for($i = 0;$i < $documentCount;$i++)
         {
            $scanSeq = $i+2;
            $short_name = $this->formateShortName($documentSelected[$i]["Short Name"]);
            $doc_ref    = $documentSelected[$i]["Ref"];
            if(!empty ($documentSelected[$i]["Issue Date"]))
            {
                //$doc_date = str_replace('/','', $documentSelected[$i]["Issue Date"]);
                $doc_date = $this->formateDOB($documentSelected[$i]["Issue Date"], false);
            }
            else $doc_date   =  '      ';
            
            $barcode    = $scanSeq.' '.$short_name.$doc_ref.$doc_date;
           
            $docBarcode[$i] = strtoupper($barcode);
            
           
        }
        
        return $docBarcode;
         
         
    }
    /*
     * Function to get barcodes ends
     */
    
    /*
     * Function to get initials
     * param 1  : firstname    : Applicants firstname
     * param 1  : surname      : Applicants surname
     * param 1  : middlename1  : Applicants middlename1
     * param 1  : middlename2  : Applicants middlename2
     * param 1  : middlename3  : Applicants middlename3
     */
    function getNameInitials($firstname,$surname,$middlename1,$middlename2)
    {
        $initials  = substr($firstname, 0,1);
        if(!empty($middlename1))$initials .= substr($middlename1, 0,1);
        if(!empty($middlename2))$initials .= substr($middlename2, 0,1);
        $initials .= substr($surname, 0,1);
        
        for($i = 0;i < 4 - strlen($initials);$i++)
        {
            $initials .= ' ';
        }
       
        return $initials;
            
    }
    /*
     * Function to get initials ends
     */
    
   /* 
    * Function to get personal deatils barcode
    * param 2  : applicant_details    : Details of applicant
    * param 3  : applicant_middlename : Applicant middle names
    */
    function getPODBarcode($applicant_details,$applicant_middlename)
    {
         $firstname   = $applicant_details[0]['forname'];
         $surname     = $applicant_details[0]['surname'];
         $middlename1 = $applicant_middlename[0]['middlename'];
         $middlename2 = $applicant_middlename[1]['middlename'];
         $middlename3 = $applicant_middlename[2]['middlename'];
         $initials    = $this->getNameInitials($firstname, $surname, $middlename1, $middlename2, $middlename3);
         //$door_number = $applicant_details[0]['door_no'];
         if(is_numeric(substr($applicant_details[0]["address1"], 0,1)))
         {
             $drNo = explode(' ', $applicant_details[0]["address1"]);
             $drNUM = '';
             $drNumber = '';
              for($i = 0;$i < strlen($drNo[0]);$i++)
                         {
                            $drNUM = substr($drNo[0], $i,1);
                   /*   if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $drNUM))*/
                            if(ctype_alnum($drNUM)) 
                                {
                                    $drNumber .=  $drNUM;
                            }
                            else
                            {
                                break;
                            }

                         }
             
                    if(strlen($drNumber) <= 4)
                     {
                         $door_number = $drNumber;
                         for($i = 0;$i < 4 - strlen($drNumber);$i++)
                         {
                             $door_number .= ' ';

                         }

                         $this->DOORNO = $door_number;
                     }
                     else
                     {
                         $door_number  = '    ';
                         $this->DOORNO = "N/A";
                         $applicant_details[0]['door_no'] = "N/A";
                     }
                
               
             
         }
         else
         {
             $door_number = '    ';
             $this->DOORNO = "N/A";
             $applicant_details[0]['door_no'] = "N/A";
         }
         
         
         $postcode    = trim($applicant_details[0]['postcode']);
         $postcode    = $this->formatePostcode($postcode);
         $dob         = $this->formateDOB(trim($applicant_details[0]['dob']),false);
         
         $podBarcode = '1 '.$postcode.$dob.$initials.$door_number;
         
         return strtoupper($podBarcode);
    }
    /*
     * Function to get personal deatils barcode ends
     */
    /*
     * Function to format postcode
     * param 1 : postcode : Post code of the applicant
     * return  : true     : Formated post code
     */
    function formatePostcode($postCode)
    {
        $len = strlen($postCode);
        $splitPostCode = explode(' ', $postCode);
        if(count($splitPostCode) > 1)
            $postCode = $splitPostCode[0].' ';

        if($len < 8)
        {
            for($i = 0;$i < 8 - $len;$i++)
            {
                $postCode .= ' ';
            }
        }
        
        $postCode .= $splitPostCode[1];
        return $postCode;
    }
    /*
     * Function to formate postcode ends
     */
    
    /*
     * Function to formate dob
     * param 1 : dob  : DOB of the applicant
     * return  : true : Fromated DOB
     */
    function formateDOB($dob,$withStrips)
    {
        $splitDOB = explode('/', $dob);
        if($withStrips == false)
        {
            $dob = $splitDOB[0].$splitDOB[1].substr($splitDOB[2],2);
        }
        else
        {
            $dob = $splitDOB[0].'/'.$splitDOB[1].'/'.substr($splitDOB[2],2);
        }
        return $dob;
    }
    /*
     * Function to formate dob
     */
    /*
     * Function to generate Initiating barcode
     * 
     */
    function generateInitiatingBarcode($appId,$docCount,$remuneration,$CRN,$orgId)
    {
        $compName = $this->getCompName($this->_compId);
        $serviceCode = $this->SERVICECODE;
        if($remuneration == 'Y')$volunteer         = 'N';
        else if($remuneration == 'N')$volunteer    = 'Y';
        
        $queryCRBCode  = "SELECT crb_code FROM crb_code WHERE volunteer = '$volunteer' AND disc_level ='$this->DISCTYPE'";
        $resCRBCode    = $this->getDBRecords($queryCRBCode);
        
        $crbCode       = $resCRBCode['0']['crb_code'];
       
        /************Make CRB code 06 in case of HR Department*********************/
        if($compName == 'HR' && $crbCode == '03') $crbCode = '06';
        /************Make CRB code 06 in case of HR Department*********************/
        $crbPriceQuery = "SELECT amount FROM svc_crb WHERE service_code = '$serviceCode' AND crb_code = '$crbCode'";
        $resCRBPrice   = $this->getDBRecords($crbPriceQuery);
        $crbFee        = $resCRBPrice[0]['amount'];
        $this->CRBFEE  = $crbFee;
        
        $polFee        = $this->POL;
        
        //$this->USEDBYDATE = '200312';
        $clientId      = $this->CLIENTID;
        if($CRN == '') $CRN  = $this->getCustRefNum($appId, $clientId);
        $this->APPREFNUM = $CRN;
        
//        if($serviceCode == '01')
//        {
//            $clientFee        = 5;
//            $clientFeeNo      = '0500';
//        }
//        else
//        {
//            $clientFee        = 0;
//            $clientFeeNo      = '0000';
//        }
        
        $clientFee        = $this->CLIENTFEE;
        $clientFeeNo      = $this->CLIENTFEECODE;
            
        $this->TOTAL_FEE =  $crbFee + $polFee + $clientFee;
        
        $initiatingBarcode = $this->IIN.$serviceCode.$docCount.$this->USEDBYDATE.$CRN.$crbCode.$clientFeeNo;
        $initiatingBarcode .= $this->cal_luhnNo($initiatingBarcode);
        
         $objGenerateBarcode = new Cgenbarcode();
       
         $objGenerateBarcode->genBarcode(trim($initiatingBarcode), $this->IMAGE_TYPE,  $this->IB_BARCODE_TYPE,$this->IB_WIDTH,$this->IB_HEIGHT,$this->IB_XRES, $this->FONT, $this->IB_PATH.'app_'.$appId.'_IB.png');
         
        return $initiatingBarcode;
        
        
        
    }
    /*
     * Function to generate Initiating barcode  ends
     */
    
    
    /*
     * Function to calculate luhn's no
     * param 1  : $barcode : Initiating barcode
     * return   : true     : Luhn's No
     */
    
    function cal_luhnNo($barcode)
    {
        $alternateNo = true;
        $calLuhnNo = 0;
        $luhnNo = 0;
        $barcodeCount = strlen($barcode);
        for($i=0;$i< strlen($barcode);$i++)
        {
            $barcodeCount--;
            if($alternateNo == true)
            {
                $calLuhnNo = substr($barcode, $barcodeCount,1) * 2;
                
                if(strlen($calLuhnNo) > 1)
                {
                    $calProduct = substr($calLuhnNo, 0,1) + substr($calLuhnNo, 1,1);
                    $luhnNo += $calProduct;
                }
                else
                {
                    $luhnNo += $calLuhnNo;
                }
                $alternateNo = false;
            }
            else
            {
                $luhnNo += substr($barcode, $barcodeCount,1);
                $alternateNo = true;
            }
            
        }
        
        $num = substr($luhnNo, -1);
        if($num == 0)
        {
            $luhnNo = 0;
        }
        else
        {
            $luhnNo = 10 - $num;
        }
        return $luhnNo;
    }
    
    /*
     * Function to calculate luhn's no ends
     */
    
    /*
     * Function to generate pdf
     */
    function generateLetterPDF($applicationDetails,$appMiddleName,$documentDetails,$appId,$personalDetBarcode,$idDocBarcodes,$initiatingBarcode,$app_email,$orgId,$actionType)
    {
        $cntDoc = count($documentDetails);
        for($i = 0;$i < count($applicationDetails);$i++)
        {
            $applicationDetails[$i]['formatedDate'] = $this->formateDOB($applicationDetails[$i]['dob'],true);
            $applicationDetails[$i]['forname']      = ucfirst($applicationDetails[$i]['forname']);
            $applicationDetails[$i]['surname']      = ucfirst($applicationDetails[$i]['surname']);
            $applicationDetails[$i]['address1']     = ucfirst($applicationDetails[$i]['address1']);
            $applicationDetails[$i]['postcode']     = strtoupper($applicationDetails[$i]['postcode']);
            $applicationDetails[$i]['town_city']     = ucfirst($applicationDetails[$i]['town_city']);
            $applicationDetails[$i]['door_no']      = strtoupper($this->DOORNO);
////            if($applicationDetails[$i]['door_no'] == '')
////            {
//                $applicationDetails[$i]['door_no'] == 'N/A';
////            }
       }
        
        
        for($i = 0;$i < count($appMiddleName);$i++)
        {
            $appMiddleName[$i]['middlename'] = ucfirst($appMiddleName[$i]['middlename']);
        }
        for($i = 0;$i < count($documentDetails);$i++)
        {
            $documentDetails[$i]['formatedDate'] = $this->formateDOB($documentDetails[$i]['Issue Date'],true);
            
            if(trim($documentDetails[$i]['Issue Date']) == '')
            {
                $documentDetails[$i]['Issue Date'] = 'N/A';
                 $documentDetails[$i]['formatedDate']  = 'N/A';
            }
            
            if(trim($documentDetails[$i]['Ref']) == '')
            {
                $documentDetails[$i]['Ref'] = 'N/A';
            }
            
            $idDocBarcodes[$i]      = $this->sepBarcode($idDocBarcodes[$i]);
        }
        
        
        if(count($documentDetails) < 5)
        {
            for($j = count($documentDetails);$j < 5;$j++)
                {
                    $documentDetails[$j]['Document Name'] = "Not Applicable";
                    $documentDetails[$j]['Short Name']    = "N/A";
                    $documentDetails[$j]['Issue Date"']   = "N/A";
                    $documentDetails[$j]['Ref']           = "N/A";
                    $documentDetails[$j]['formatedDate']  = "N/A";
                }
        }
        $usedByDate   = $this->formateUsedBydate($this->USEDBYDATE);
        $appDetails   = $this->getFormatedAppDetails($applicationDetails, $appMiddleName);
        $this->append_Zero($this->TOTAL_FEE);
        $personalDetBarcode = $this->sepBarcode($personalDetBarcode);
        
        $initiatingBarcode  = $this->sepBarcode($initiatingBarcode);
        
        //$this->saveAppBarcodeInfo($appId,$initiatingBarcode,$personalDetBarcode,$idDocBarcodes,$usedByDate);
        $this->saveBarcodeDetails($appId,$initiatingBarcode,$personalDetBarcode,$idDocBarcodes,$usedByDate);
        
//       if($this->DOC_EXPIRES <> 'Y')
//        {
                    // Used By Date reduced by one while displaying
                $usedByDateArry = explode("/", $usedByDate);
                $usedByDateTS = mktime(0, 0, 0, $usedByDateArry[1], $usedByDateArry[0], $usedByDateArry[2]);
                $usedByDate    = date("d/m/y",  strtotime("-1 day",$usedByDateTS));
//        }
        $objLetterPDF = new LetterPDF(IMAGE_PATH);
        $objLetterPDF->generateLetter($applicationDetails, $appMiddleName, $documentDetails,$this->TOTAL_FEE,$usedByDate,$appId,$appDetails,$personalDetBarcode,$idDocBarcodes,$initiatingBarcode,$cntDoc,$app_email,$orgId,$actionType);
    }
    /*
     * Function to generate pdf
     */
    
//        /*
//     * Function to generate pdf
//     */
//    function generateLetterPDF_Reprint($applicationDetails,$appMiddleName,$documentDetails,$appId,$personalDetBarcode,$idDocBarcodes,$initiatingBarcode)
//    {
//        $cntDoc = count($documentDetails);
//        for($i = 0;$i < count($applicationDetails);$i++)
//        {
//            $applicationDetails[$i]['formatedDate'] = $this->formateDOB($applicationDetails[$i]['dob'],true);
//            $applicationDetails[$i]['forname']      = ucfirst($applicationDetails[$i]['forname']);
//            $applicationDetails[$i]['surname']      = ucfirst($applicationDetails[$i]['surname']);
//            $applicationDetails[$i]['address1']     = ucfirst($applicationDetails[$i]['address1']);
//            $applicationDetails[$i]['postcode']     = strtoupper($applicationDetails[$i]['postcode']);
//            $applicationDetails[$i]['town_city']     = ucfirst($applicationDetails[$i]['town_city']);
//            $applicationDetails[$i]['door_no']      = strtoupper($this->DOORNO);
//////            if($applicationDetails[$i]['door_no'] == '')
//////            {
////                $applicationDetails[$i]['door_no'] == 'N/A';
//////            }
//       }
//        
//        
//        for($i = 0;$i < count($appMiddleName);$i++)
//        {
//            $appMiddleName[$i]['middlename'] = ucfirst($appMiddleName[$i]['middlename']);
//        }
//        for($i = 0;$i < count($documentDetails);$i++)
//        {
//            $documentDetails[$i]['formatedDate'] = $this->formateDOB($documentDetails[$i]['Issue Date'],true);
//            
//            if(trim($documentDetails[$i]['Issue Date']) == '')
//            {
//                $documentDetails[$i]['Issue Date'] = 'N/A';
//                 $documentDetails[$i]['formatedDate']  = 'N/A';
//            }
//            
//            if(trim($documentDetails[$i]['Ref']) == '')
//            {
//                $documentDetails[$i]['Ref'] = 'N/A';
//            }
//            
//            $idDocBarcodes[$i]      = $this->sepBarcode($idDocBarcodes[$i]);
//        }
//        
//        
//        if(count($documentDetails) < 5)
//        {
//            for($j = count($documentDetails);$j < 5;$j++)
//                {
//                    $documentDetails[$j]['Document Name'] = "Not Applicable";
//                    $documentDetails[$j]['Short Name']    = "N/A";
//                    $documentDetails[$j]['Issue Date"']   = "N/A";
//                    $documentDetails[$j]['Ref']           = "N/A";
//                    $documentDetails[$j]['formatedDate']  = "N/A";
//                }
//        }
//        $usedByDate   = $this->formateUsedBydate($this->USEDBYDATE);
//        $appDetails   = $this->getFormatedAppDetails($applicationDetails, $appMiddleName);
//        $this->append_Zero($this->TOTAL_FEE);
//        $personalDetBarcode = $this->sepBarcode($personalDetBarcode);
//        
//        $initiatingBarcode  = $this->sepBarcode($initiatingBarcode);
//        
//        //$this->saveAppBarcodeInfo($appId,$initiatingBarcode,$personalDetBarcode,$idDocBarcodes,$usedByDate);
//        $this->saveBarcodeDetails($appId,$initiatingBarcode,$personalDetBarcode,$idDocBarcodes,$usedByDate);
//        $objLetterPDF = new LetterPDF(IMAGE_PATH);
//        $objLetterPDF->generateLetter($applicationDetails, $appMiddleName, $documentDetails,$this->TOTAL_FEE,$usedByDate,$appId,$appDetails,$personalDetBarcode,$idDocBarcodes,$initiatingBarcode,$cntDoc);
//    }
//    /*
//     * Function to generate pdf
//     */
//    
    
    /*
     * Function to formate usedbydate
     */
    function formateUsedBydate($date)
    {
        $fdate = substr($date, 0,2).'/'.substr($date, 2,2).'/'.substr($date, 4,2);
        return $fdate;
    }
    /*
     * Function to formate usedbydate ends
     */
    
    /*
     * Function to get formated app deatils
     */
    function getFormatedAppDetails($applicationDetails,$appMidName)
    {
        $appDetails[0]['customerid'] = 'Initials:';
        $appDetails[1]['customerid'] = 'Date of Birth:';
        $appDetails[2]['customerid'] = 'Door Number:';
        $appDetails[3]['customerid'] = 'Postcode:';

        $appDetails[0]['customerDetails'] = strtoupper($this->getNameInitials($applicationDetails[0]['forname'], $applicationDetails[0]['surname'], $appMidName[0]['middlename'], $appMidName[1]['middlename']));
        $appDetails[1]['customerDetails'] = $applicationDetails[0]['formatedDate'];
        //$appDetails[2]['customerDetails'] = $applicationDetails[0]['door_no'];
        $appDetails[2]['customerDetails'] = $applicationDetails[0]['door_no'];
        $appDetails[3]['customerDetails'] = strtoupper($applicationDetails[0]['postcode']);
        
        
        return $appDetails;
        
    }
    /*
     * Function to get formated app deatils ends
     */
    
    
    //Function to append zeros after decimal point
    function append_Zero($cost)
    {
        $splitCost = explode('.', $cost);
        
        
        if(count($splitCost) > 1)
        {
            if(strlen($splitCost[1]) == 1)
            {
                $cost = $cost.'0';
            }
        }
        else
        {
            $cost = $cost.'.00';
        }
        
        if(strlen($splitCost[0]) == 1)
        {
            $cost = '0'.$cost;
        }
        $this->TOTAL_FEE = $cost;
    }
   //Function to append zeros after decimal point end
    
    /*
     * Function to separate Barcodes
     */
     function sepBarcode($barcode)
    {
        $formatedBarcode = '';
        $j = 0;
        $barcode = str_replace(' ', $this->PADDING_CHARACTER,$barcode);
        for($i = 0;$i < strlen($barcode)/4;$i++)
        {
            $formatedBarcode .= substr($barcode, $j,4).' ';
            $j += 4;
        }
        
        return $formatedBarcode;
    }
    /*
     * Function to separate Barcodes ends
     */
    
    /*
     * Function to get used by date
     * param 1  : documentSelected : List of document selected
     */
    function getUsedByDate($documentSelected)
    {
        for($i = 0;$i<count($documentSelected);$i++)
        {
            $usedBydate[$i] = strtotime(str_replace('/', '-', $documentSelected[$i]['Expiry Date']));
        }
        
        $minExpiryDate = min($usedBydate);
        if($this->SUBMIT_TIME == '')
        {
            $date = date("Y-m-d");// current date
        }
        else
        {
            $date = $this->SUBMIT_TIME;
        }


        $minExipryDateFromToday = date("d/m/Y",strtotime(date("Y-m-d", strtotime($date)) . " +28 day"));
  
        $expiryDate = strtotime(str_replace('/', '-',$minExipryDateFromToday));
        if($minExpiryDate < $expiryDate)
        {
          
           $expiryDate = $minExpiryDate;           
           $this->DOC_EXPIRES = 'Y';
        }       
        $expiryDate = $this->getExpirationDate(date('Y-m-d',  $expiryDate), ' +1 day'); 
//        $expiryDate = $this->getExpirationDate(date('Y-m-d',  strtotime(str_replace('/', '-', $expiryDate))), ' -1 day');
        
        $this->USEDBYDATE = $this->formateDOB($expiryDate, false);
    }
    /*
     * Function to get used by date end
     */
    
    /*
     * Function to get expiration date
     * param 1   : issueDate   : Issue date
     * param 2   : monthsToAdd : Months to add
     */
    function getExpirationDate($issueDate,$monthsToAdd)
    {
        $issueDate = date('Y-m-d',  strtotime(str_replace('/', '-', $issueDate)));
        $dt =  strtotime(date("Y-m-d", strtotime($issueDate)) . $monthsToAdd);
        return date("d/m/Y",$dt);
    }
    
    /*
     * Function to get document array
     * 
     */
    function getDocList($docName,$docShortName,$docIssueDate,$docRef,$docExipryDate,$count,$documentList)
    {
         $documentList[$count]["Document Name"]  = $docName;
         $documentList[$count]["Short Name"]     = $docShortName;
         $documentList[$count]["Issue Date"]     = $docIssueDate;
         $documentList[$count]["Ref"]            = strtoupper($docRef);
         $documentList[$count]["Expiry Date"]    = $docExipryDate;
         return $documentList;
    }
    
    
    /*
     * Function to get customer reference number
     * param1  : appId : Application Id
     * return  : true  : Customer ref number
     */
    function getCustRefNum($appId,$clientId)
    {
            $CRN = $appId;
            $i   = strlen($appId);
            do {
                $CRN = '0'.$CRN;
                $i++;
            } while ($i < 6);
            $CRN = $clientId.$CRN;
       
        return $CRN;
    }
    /*
     * Function to get customer ref number ends
     */
    
    /*
     * Function to save barcode info
     * param 1 : appId             : application id
     * param 2 : initiatingBarcode : Initiating barcode
     * param 3 : personalDetBarcode: Personal details barcode
     * param 4 : idDocBarcodes     : Id document barcode
     * param 5 : usedbydate        : Expiration date 
     */
    function saveAppBarcodeInfo($appId,$initiatingBarcode,$personalDetBarcode,$idDocBarcodes,$usedbydate)
    {
        $fieldArray['application_id']      = $appId;
        $fieldArray['initiating_barcode']  = str_replace(' ', '', $initiatingBarcode);
        $fieldArray['personal_barcode']    = str_replace(' ', '', $personalDetBarcode);
        $fieldArray['id_docB_barcode']     = str_replace(' ', '', $idDocBarcodes[0]);
        $fieldArray['id_docC_barcode']     = str_replace(' ', '', $idDocBarcodes[1]);
        $fieldArray['id_docD_barcode']     = str_replace(' ', '', $idDocBarcodes[2]);
        
        if(count($idDocBarcodes) == 4)$idDocBarcodes[4]='';
        if(count($idDocBarcodes) == 3)
        {
            $idDocBarcodes[3]='';
            $idDocBarcodes[4]='';
        }
        $fieldArray['id_docE_barcode']     = str_replace(' ', '', $idDocBarcodes[3]);
        $fieldArray['id_docF_barcode']     = str_replace(' ', '', $idDocBarcodes[4]);
        $fieldArray['used_by_date']        = $usedbydate;
        $fieldArray['fees']                = $this->TOTAL_FEE;
        $tableName                         = 'application_barcode_info';
        
        $this->Insert($tableName, $fieldArray);
        
    }
    
    /*
     * Function to save bacode details in database
     */
    function saveBarcodeDetails($appId,$initiatingBarocde,$personalBarcode,$idBarcode,$usedByDate)
    {
        $fieldArray['application_id']                = $appId;
        $fieldArray['initiating_barcode_name']       = 'app_'.$appId.'_IB.png';
        $fieldArray['initiating_barcode']            = str_replace(' ', '',$initiatingBarocde);
        $fieldArray['personal_details_barcode_name'] = 'app_'.$appId.'_1_PODB.png';
        $fieldArray['personal_details_barcode']      = str_replace(' ', '',$personalBarcode);
        
        for($i = 1;$i <= count($idBarcode);$i++)
        {
            $fieldName                       = 'id_doc'.$i.'_barcode';
            $id                              = $i+1;
            $fieldArray[$fieldName.'_name']  = 'app_'.$appId.'_'.$id.'_PODB.png';
            $fieldArray[$fieldName]          = str_replace(' ', '',$idBarcode[$i - 1]);
        }
       
        $fieldArray['application_ref_number']            = $this->APPREFNUM;
        $fieldArray['usedby_date']                       = $usedByDate;
        $fieldArray['POL_Fees']                          = $this->POL;
        $fieldArray['CRB_Fees']                          = $this->CRBFEE;
        $fieldArray['Client_Fees']                       = $this->CLIENTFEE;
        $fieldArray['fee']                               = $this->TOTAL_FEE;
        $fieldArray['padding_character']                 = $this->PADDING_CHARACTER;
        $fieldArray['created_date']                      = time();
        
        $tableName                                        = 'barcode_detail';
        
        $this->Insert($tableName, $fieldArray);
        
        
    }
    
    function getClientDetails($appId)
    {
        //gets client details based on service code
        //for cqc service is fullpayment
        $query = "SELECT service_code,POL,client_fee,client_fee_code,client_id,IIN from service_code order by id limit 1 ";
        $res   = $this->getDBRecords($query);
        
        $this->SERVICECODE  = $res[0]['service_code'];
        $this->POL           = $res[0]['POL'];
        $this->CLIENTFEE     = $res[0]['client_fee'];
        $this->CLIENTFEECODE = $res[0]['client_fee_code'];
        $this->CLIENTID      = $res[0]['client_id'];
        $this->IIN           = $res[0]['IIN'];
        
        
    }
    
    
    /*
     * Function to remove documents is count is not 3 or 5
     */
    function removeDoc($documentList,$count)
    {
        $newDocumentList = array_reverse($documentList);
        $isAddressCount = 0;
         for($i = 0;$i < $count;$i++)
            {
                $docCount = count($newDocumentList);
                if($docCount != 3 && $docCount != 5)
                {
                    
                    $isAddress = $this->getDocAddressDetail($newDocumentList[$i]['Document Name']);
                    
                    if($isAddress == 'N' || $isAddressCount > 0)
                    {
                        unset ($newDocumentList[$i]);
                    }
                    if($isAddress == 'Y')
                    {
                        $isAddressCount++;
                    }
                }
                else
                {
                        break;
                }
                
            }
        
        
        return $newDocumentList;
    }
    /*
     * Function to remove documents is count is not 3 or 5 ends
     */
    
    /*
     * Function to get documents address details
     */
    function getDocAddressDetail($docName)
    {
        $query = "SELECT address FROM id_document WHERE ref_doc_name = '$docName'";
        $result = $this->getDBRecords($query);
        
        return $result[0]['address'];
    }
    /*
     * Function to get documents address details ends
     */
    
    /*
     * Function to get applicant email
     */
    function getEmail($appId)
    {
        $query = "SELECT ur.email FROM `users`  u inner join reqdocs r on u.user_id = r.user_id inner join applications a on r.app_id = a.application_id inner join user_registration ur on u.unique_key = ur.username WHERE a.application_id = '$appId'";
        $res = $this->getDBRecords($query);
        return $res[0]['email'];
    }
    
    
    
    
    
    
    
    
    
    
    /*
     * Function to save barcode info ends
     */
//    /*
//     * Function to process Expiration date
//     * param 1  : docType  : Document type
//     * param 2  : issuedate: Issue date
//     */
//    function processExpirationDate($docType,$issueDate)
//    {
//        switch ($docType)
//        {
//            case 'general':
//                $expirydate = $this->getExpirationDate(date('d/m/Y'), $this->MAXDAYS);
//                break;
//            case '12months':
//                $expirydate = $this->getExpirationDate($issueDate, ' +12 months');
//                break;
//            case '3months_correspondence':
//                $expirydate = $this->getExpirationDate($issueDate, ' +3 months');
//                break;
//            case '3months_general':
//                $expirydate = $this->getExpirationDate($issueDate, ' +3 months');
//                break;
//            case '3months_utility':
//                $expirydate = $this->getExpirationDate($issueDate, ' +3 months');
//                break;
//            default:
//                $expirydate = $this->getExpirationDate(date('d/m/Y'), $this->MAXDAYS);
//                break;
//                
//        }
//        
//        return $expirydate;
//        
//    }
}

?>
