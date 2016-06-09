<?php

class ProcessRefLetter extends CommonLib 
{

    
    function __construct() 
    {
        parent::__construct();
        return true;
    }
    
    /*
     * Function to get process referal letter
     * param1   : appId  : Application id
     */
    
    function processRefLetter($appId)
    {
        $applicantDetails = $this->getApplicantDetails($appId);
        $applicantMidNames = $this->getMiddleNames($appId);
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
        $appQuery = "SELECT  pnf.name as forname, pns.name as surname,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) as dob,addr .address1,addr .town_city,addr .postcode
                    FROM applications a
                   
                    INNER JOIN app_person_name apnf on a.application_id = apnf.application_id
                    INNER JOIN person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
                    INNER JOIN app_person_name apns on a.application_id = apns.application_id
                    INNER JOIN person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
                    INNER JOIN app_address apadd on a.application_id = apadd.application_id
                    INNER JOIN address addr on apadd.address_id = addr .address_id 
                    WHERE a.application_id = '$appId'";
        $resQuery = $this->getDBRecords($appQuery);
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
        $resreqDocsQuery=$this->getDBRecords(reqDocsQuery);
        $req=$docRes[0]['reqdocs'];
       $dummy = $this->processDocumentSelected($req, $resdocQuery);
        
        
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
    function processDocumentSelected($reqDocs,$docSelected)
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
            $documentList[$count]["Document Name"] = "Passport";
            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["passport_issue_date"],$format=3);
            $count++;
        }
        //Driving licence
        if($a2=="Y") {
            $documentList[$count]["Document Name"] = "Driving Licence";
            $documentList[$count]["Issue Date"] = $this->getFormatedDate($docSelected[0]["dl_valid_from"],$format=3);
            $count++;
            

        }
        // Birth certificate
        if($b1=="Y" || $a3=="Y" ) {
            $smarty->assign("birthCertDOB",getFormatedDate($appres[0]["bc_dob"],$format=3));
            if($appres[0]["bc_uk"]=="X")
                $birthCertUK="YES";
            else
                $birthCertUK="NO";
            $smarty->assign("birthCertUK",$birthCertUK);
            $smarty->assign("birthCertCountry",$appres[0]["bc_issue_country"].getCountryCode($appres[0]["bc_issue_country"]));
            $smarty->assign("birthCertIssueDt",getFormatedDate($appres[0]["bc_issue_date"],$format=3));


        }

        // Adoption certificate
        if($a6=="Y" )
        {
            $smarty->assign("adopt_cert_country",$appres[0]["adop_cert_country"]);
            $smarty->assign("adopt_cert",getFormatedDate($appres[0]["adop_cert_dt"],$format=3));
            $smarty->assign("adopt_cert_dob",getFormatedDate($appres[0]["adop_cert_dob"],$format=3));


        }


        //Foreign Birth Certificate
        if($b33=="Y" ) {
            $smarty->assign("birthCertDOB",getFormatedDate($appres[0]["foreign_bc_DOB"],$format=3));


            $smarty->assign("birthCertCountry",$appres[0]["foreign_bc_issue_country"].getCountryCode($appres[0]["foreign_bc_issue_country"]));
            $smarty->assign("birthCertIssueDt",getFormatedDate($appres[0]["foreign_bc_issue_date"],$format=3));


        }
        //P45
        if($b11=="Y" ) {
            $smarty->assign("pniNumber",$appres[0]["ni_no"]);
            $smarty->assign("pniNumberIssueDt",getFormatedDate($appres[0]["pniNumberIssueDt"],$format=3));


        }

        //P45
        if($b18=="Y" ) {
            $smarty->assign("p60ni_no",$appres[0]["p60ni_no"]);
            $smarty->assign("p60IssueDt",getFormatedDate($appres[0]["p60IssueDt"],$format=3));


        }
        //marriage certificate
        if($b2=="Y" ) {
            $smarty->assign("marriage_cert_issue_date",getFormatedDate($appres[0]["marriage_cert_issue_date"],$format=3));
        }

        //photo ID card

        if($a4=="Y" ) {
            $smarty->assign("photoIdCard",getFormatedDate($appres[0]["photo_id_card"],$format=3));
            $smarty->assign("photoIdCardIssueCountry",$appres[0]["photo_id_country"]);
            $smarty->assign("photoIdCardExpiryDate",getFormatedDate($appres[0]["photo_id_expiry"],$format=3));
        }

        //firearm
        if($a5=="Y" ) {
            $smarty->assign("fireArmsCert",$appres[0]["firearmscertno"]);
            $smarty->assign("fireArmsCertdob",getFormatedDate($appres[0]["firearms_dob"],$format=3));
            $smarty->assign("fireArmsCertIssuedt",getFormatedDate($appres[0]["firearms_cert"],$format=3));
            $smarty->assign("fireArmsCertvalidfrom",getFormatedDate($appres[0]["firearms_validfrom"],$format=3));
            $smarty->assign("fireArmsCertvalidto",getFormatedDate($appres[0]["firearms_validto"],$format=3));
            $smarty->assign("fireArmpostcode",$appres[0]["firearmspostcode"]);
        }
        // Certificate of british nationality
        if($b3=="Y" ) {

            $smarty->assign("british_nat_cert",getFormatedDate($appres[0]["british_nat_cert"],$format=3));

        }
        //Valid Vehicle Registration Document
        if($b4=="Y" ) {
            $smarty->assign("vehRegCert",getFormatedDate($appres[0]["veh_reg_doc"],$format=3));

        }
        //Valid NHS Card (UK)

        if($b5=="Y" ) {
            $smarty->assign("nhsCard",getFormatedDate($appres[0]["nhs_card"],$format=3));
        }

        //National Insurance Card (UK)
        if($b6=="Y" ) {
            $smarty->assign("natInsCard",$appres[0]["national_ins_card"]);
        }

        //Exam Certificate
        if($b7=="Y" ) {
            $smarty->assign("examCert",getFormatedDate($appres[0]["exam_cert"],$format=3));
        }

        //Connexions Card (UK)
        if($b8=="Y" ) {
            $smarty->assign("connexCardNo",$appres[0]["connex_card_no"]);
            $smarty->assign("connexissuedate",getFormatedDate($appres[0]["connex_care"],$format=3));
            $smarty->assign("connexCardExp",getFormatedDate($appres[0]["connex_card_exp"],$format=3));
        }

        //Valid TV Licence
        if($b15=="Y" ) {
            $smarty->assign("televisionLicense",getFormatedDate($appres[0]["television_license"],$format=3));
        }

        //Mortgage Statement
        if($b14=="Y" ) {
            $smarty->assign("mortageStmt",getFormatedDate($appres[0]["mortage_statement"],$format=3));
        }
        //Valid Insurance Certificate
        if($b16=="Y" ) {
            $smarty->assign("insCert",getFormatedDate($appres[0]["ins_cert"],$format=3));
        }

        //British Work Permit / VISA (UK)
        if($b17=="Y" ) {
            $smarty->assign("workPermit",getFormatedDate($appres[0]["work_permit"],$format=3));
        }
        //Financial Statement (pension, endowment, ISA etc)

        if($b9=="Y" ) {
            $smarty->assign("financialStmt",getFormatedDate($appres[0]["financial_statement"],$format=3));
        }
        //Mail Order Catalogue Statement

        if($b19=="Y" ) {
            $smarty->assign("mailOrdCat",getFormatedDate($appres[0]["mail_ord_catalog"],$format=3));
        }

        //Child Benefit book
        if($b12=="Y" ) {
            $smarty->assign("childBenefitBook",getFormatedDate($appres[0]["child_benifit_book"],$format=3));
        }
        //Bank/Building Society Statement

        if($b20=="Y" ) {
            $smarty->assign("bankStmt",getFormatedDate($appres[0]["bank_statement"],$format=3));
        }

        //Electricity


        if($b28=="Y" ) {
            $smarty->assign("elecBill",getFormatedDate($appres[0]["elec_bill"],$format=3));
        }

        //gas


        if($b29=="Y" ) {
            $smarty->assign("gasBill",getFormatedDate($appres[0]["gas_bill"],$format=3));
        }

        //Water bill
        if($b30=="Y" ) {
            $smarty->assign("waterBill",getFormatedDate($appres[0]["water_bill"],$format=3));
        }
        //Water bill
        if($b31=="Y" ) {
            $smarty->assign("telBill",getFormatedDate($appres[0]["telphone_bill"],$format=3));
        }

        //Mobile bill
        if($b32=="Y" ) {
            $smarty->assign("mobileBill",getFormatedDate($appres[0]["mobile_bill"],$format=3));
        }
        //credit card
        if($b21=="Y" ) {
            $smarty->assign("creditCareStmt",getFormatedDate($appres[0]["credit_care_statement"],$format=3));
        }
        //store card
        if($b22=="Y" ) {
            $smarty->assign("storeCardStmt",getFormatedDate($appres[0]["store_card_statement"],$format=3));
        }
        //
        if($b24=="Y" ) {
            $smarty->assign("corBenAgency",getFormatedDate($appres[0]["cor_benefit_agency"],$format=3));
        }

        //
        if($b26=="Y" ) {
            $smarty->assign("corInlandRev",getFormatedDate($appres[0]["cor_inland_rev"],$format=3));
        }
        //
        if($b27=="Y" ) {
            $smarty->assign("corLocAgency",getFormatedDate($appres[0]["cor_local_auth"],$format=3));
        }
        //
        if($b25=="Y" ) {
            $smarty->assign("corEmpServ",getFormatedDate($appres[0]["cor_emp_service"],$format=3));
        }
        //
        if($b10=="Y" ) {
            $smarty->assign("courtSummons",getFormatedDate($appres[0]["court_summons"],$format=3));
        }
        //
        if($b23=="Y" ) {
            $smarty->assign("paySlip",getFormatedDate($appres[0]["payslip"],$format=3));
        }
        if($b13=="Y" ) {
            $smarty->assign("councilTax",getFormatedDate($appres[0]["council_tax"],$format=3));
        }
        if($b37=="Y" ) {
            $smarty->assign("disclNumber",$appres[0]["disclosure_number"]);
            $smarty->assign("disclDate",getFormatedDate($appres[0]["disclosure_issue_date"],$format=3));
        }
        #Split the documents
    }
    /*
     * Function to process document selected end
     */

}

?>
