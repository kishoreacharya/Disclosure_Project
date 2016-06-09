<?php

#########################################################################
#                       Terminologies Defined
#                       Version : 1.1
#########################################################################

/*
 * CRB,ISA,VBS : DBS
 * Standard CRB check,Enhanced CRB check : Standard DBS check,Enhanced DBS check : Put DBS in place of CRB
 */
    $DBS="DBS";
    define("DBS", $DBS);
    $this->smarty->assign('DBS',$DBS);

/*
 * e-CRB,ECRB : e-DBS
 */
    $EDBS="e-".$DBS;
    define("EDBS", $EDBS);
    $this->smarty->assign('EDBS',$EDBS);

/*
 * Criminal Records Bureau,Independent Safeguarding Authority : Disclosure and Barring Service
 */
    $DISCLOSURES="Disclosure and Barring Service";
    define("DISCLOSURES", $DISCLOSURES);
    $this->smarty->assign('DISCLOSURES',$DISCLOSURES);

/*
 * Criminal records check : DBS check
 */
    $DBS_CHECK=$DBS." check";
    define("DBS_CHECK", $DBS_CHECK);
    $this->smarty->assign('DBS_CHECK',$DBS_CHECK);

/*
 * CRB Certificate,CRB Disclosure,CRB Disclosure Certificate : DBS Certificate
 */
    $DBS_CERT=$DBS." Certificate";
    define("DBS_CERT", $DBS_CERT);
    $this->smarty->assign('DBS_CERT',$DBS_CERT);

/*
 * ISA Adult first,ISA POVA first,ISA POVAfirst,POVA first : DBS Adult First
 */
    $DBS_ADULT=$DBS." Adult First";
    define("DBS_ADULT", $DBS_ADULT);
    $this->smarty->assign('DBS_ADULT',$DBS_ADULT);

/*
 * Enhanced CRB check with Barred List check : Enhanced check for regulated activity
 */
    $ECFRA="Enhanced check for regulated activity";
    define("ECFRA", $ECFRA);
    $this->smarty->assign('ECFRA',$ECFRA);

/*
 * Vulnerable adults :	Adults
 */
    $VG_FULL = "Adults";
    define("VG_FULL", $VG_FULL);
    $this->smarty->assign('VG_FULL',$VG_FULL);

/*
 * VA :	Adults
 */
    $VG_SHORT = "Adults";
    define("VG_SHORT", $VG_SHORT);
    $this->smarty->assign('VG_SHORT',$VG_SHORT);
    
/*
 * ISA Adult barred register,PoVA : DBS Adults Barred List
 */
    $DABL = $DBS." Adults Barred List";
    define("DABL", $DABL);
    $this->smarty->assign('DABL',$DABL);

/*
 * ISA Childrens Barred register,ISA List99,List99,List 99,ISA child first,ISA children first : DBS Children's Barred List
 */
    $DCBL = $DBS." Childrens Barred List";
    define("DCBL", $DCBL);
    $this->smarty->assign('DCBL',$DCBL);

/*
 * Disclosure Level : Level of Check
 */
    $DBS_LEVELCHECK="Level of Check";
    define("DBS_LEVELCHECK", $DBS_LEVELCHECK);
    $this->smarty->assign('DBS_LEVELCHECK',$DBS_LEVELCHECK);

/*
 * email variable change : dbs
 */
    $DBS_EMAIL="dbs";
    define("DBS_EMAIL", $DBS_EMAIL);
    $this->smarty->assign('DBS_EMAIL',$DBS_EMAIL);
    
    
       /*
 * DBS Code of Practice Link
 */
    $DBS_COP="https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/143662/cop.pdf";
    define("DBS_COP", $DBS_COP);
    $this->smarty->assign('DBS_COP',$DBS_COP);

/*
 * DBS Site url
 */
    $DBS_LINK="https://www.gov.uk/dbs";
    define("DBS_LINK", $DBS_LINK);
    $this->smarty->assign('DBS_LINK',$DBS_LINK);

/*
 * DBS ELIGIBILITY GUIDENCE LINK
 */
    $DBS_ELIGIBILITY_LINK="https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/143666/eligibility-guidance.pdf";
    define("DBS_ELIGIBILITY_LINK", $DBS_ELIGIBILITY_LINK);
    $this->smarty->assign('DBS_ELIGIBILITY_LINK',$DBS_ELIGIBILITY_LINK);

/*
 * DBS Protection of Freedoms Bill
 */
    $DBS_POFB="https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/119452/fact-sheet-part5.pdf";
    define("DBS_POFB", $DBS_POFB);
    $this->smarty->assign('DBS_POFB',$DBS_POFB);

/*
 * DBS Identity Checking Guidelines
 */
    $DBS_ICG="https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/143672/identity-checking-guidelines.pdf";
    define("DBS_ICG", $DBS_ICG);
    $this->smarty->assign('DBS_ICG',$DBS_ICG);
    
    
/*
 * DBS Recruiting Safely Link
 */
    $DBS_RSL="https://www.gov.uk/government/policies/helping-employers-make-safer-recruiting-decisions";
    define("DBS_RSL", $DBS_RSL);
    $this->smarty->assign('DBS_RSL',$DBS_RSL);

/*
 * DBS Privacy Policy link
 */
    $DBS_PPL="https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/118987/privacy-policy.pdf";
    define("DBS_PPL", $DBS_PPL);
    $this->smarty->assign('DBS_PPL',$DBS_PPL);

    /*
 * DBS Overseas applicants link
 */
    $DBS_OAL="https://www.gov.uk/dbs-check-requests-guidance-for-employers#overseas-applicants";
     define("DBS_OAL", $DBS_OAL);
    $this->smarty->assign('DBS_OAL',$DBS_OAL);

#########################################################################
#                       Javascript Versioning
#                       Version : 1
#########################################################################

/*
 * Javascript Versioning variable : Starts from 1.0 (floating digit)
 */

    $JS_VER=1.0;
    define("JS_VER", $JS_VER);
    $this->smarty->assign('JS_VER',$JS_VER);

    
    /*
 * BMS Welsh Language
 */
    $BMS_WELSH="Please note that this application needs to be completed in ENGLISH. If WELSH is your preferred language please contact our DBS team.";
    define("BMS_WELSH", $BMS_WELSH);
    $this->smarty->assign('BMS_WELSH',$BMS_WELSH);
    
    /*
 * BMS Clear Result Text
 */
    $BMS_CLEAR_RESULT_TEXT="Contains no information";
   define("BMS_CLEAR_RESULT_TEXT", $BMS_CLEAR_RESULT_TEXT);
    $this->smarty->assign('BMS_CLEAR_RESULT_TEXT',$BMS_CLEAR_RESULT_TEXT);

/*
 * BMS Positive Result Text
 */
    $BMS_POSITIVE_RESULT_TEXT="CONTAINS INFORMATION";
    define("BMS_POSITIVE_RESULT_TEXT", $BMS_POSITIVE_RESULT_TEXT);
    $this->smarty->assign('BMS_POSITIVE_RESULT_TEXT',$BMS_POSITIVE_RESULT_TEXT);
#########################################################################
#           Certificate Terminologies Defined
#                       Version : 1.1
#  Below listed Globals are purely defined for Certificate PDF changes
#########################################################################

/*
 * CRB : DBS
 * DisclosuresCRB
 */
    $CERT_DBS=$DBS;
    define("CERT_DBS", $CERT_DBS);
    $this->smarty->assign('CERT_DBS',$CERT_DBS);

/*
 * Disclosure,Criminal Records Certificate  : Certificate
 */
    $CERT_DISC='Certificate';
    define("CERT_DISC", $CERT_DISC);
    $this->smarty->assign('CERT_DISC',$CERT_DISC);

/*
 * Criminal Records Bureau,Independent Safeguarding Authority : Disclosure and Barring Service
 */
    $CERT_DISCLOSURES=$DISCLOSURES;
    define("CERT_DISCLOSURES", $CERT_DISCLOSURES);
    $this->smarty->assign('CERT_DISCLOSURES',$CERT_DISCLOSURES);

/*
 * Disclosure Level : Level of Check
 */
    $CERT_DISC_LEVEL='Level of Check';
    define("CERT_DISC_LEVEL", $CERT_DISC_LEVEL);
    $this->smarty->assign('CERT_DISC_LEVEL',$CERT_DISC_LEVEL);

/*
 * Protection of Vulnerable Adults : DBS Adults’ Barred List 
 */
    $CERT_DABL = $DBS." Adults Barred List";
    define("CERT_DABL", $CERT_DABL);
    $this->smarty->assign('CERT_DABL',$CERT_DABL);

/*
 * Protection of Children Act List : DBS Children’s Barred List 
 */
    $CERT_DCBL = $DBS." Childrens Barred List";
    define("CERT_DCBL", $CERT_DCBL);
    $this->smarty->assign('CERT_DCBL',$CERT_DCBL);

/*
 * Disclosure Document : Document
 */
    $CERT_DOCU = "Document";
    define("CERT_DOCU", $CERT_DOCU);
    $this->smarty->assign('CERT_DOCU',$CERT_DOCU);

/*
 * Enhanced Disclosures : Certificates
 */
    $CERT_DISC_CHECK = "Certificates";
    define("CERT_DISC_CHECK", $CERT_DISC_CHECK);
    $this->smarty->assign('CERT_DISC_CHECK',$CERT_DISC_CHECK); ;

/*
 * children and/or vulnerable adults : adults, including children
 */
    $CERT_VG_C = "adults, including children";
    define("CERT_VG_C", $CERT_VG_C);
    $this->smarty->assign('CERT_VG_C',$CERT_VG_C);

/*
 * Certificate change date from 1st Dec 2012
 * 1354320000 = 1st Dec 2012
 */
    $CERT_DATE = 1354320000;
    define("CERT_DATE", $CERT_DATE);
    $this->smarty->assign('CERT_DATE',$CERT_DATE);

/* BMS
 *  View PDF result date before 17th June 2013
 *  1371423599 = 16th June 2013 - 23:59:59
 */   
  $PDFRESULT_DATE = 1371423599;
  define("PDFRESULT_DATE", $PDFRESULT_DATE);
  $this->smarty->assign('PDFRESULT_DATE',$PDFRESULT_DATE);
    
/* BMS
 *  Info requested by adl status
 */   
  $INFO_REQUESTED_STATUS = "Application requires more information";
  define("INFO_REQUESTED_STATUS", $INFO_REQUESTED_STATUS);
  $this->smarty->assign('INFO_REQUESTED_STATUS',$INFO_REQUESTED_STATUS);    

/* BMS
 *  Info requested by DBS status
 */   
  $INFO_REQUESTED_STATUS_DBS = "Information requested by DBS";
  define("INFO_REQUESTED_STATUS_DBS", $INFO_REQUESTED_STATUS_DBS);
  $this->smarty->assign('INFO_REQUESTED_STATUS_DBS',$INFO_REQUESTED_STATUS_DBS);  
?>
