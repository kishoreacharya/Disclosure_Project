<?php

ob_start();
require("class.ezpdf.php");
require("class.generateBarcode.php");
require("class.application.php");

class ViewPdfLetter extends CommonLib {

    public $ImgPathPDF = 'view/images/';
    public $PADDING_CHARACTER = '';
    public $FONT = 2;
    public $IMAGE_TYPE = 'jpeg';
    public $BARCODE_TYPE = 'C128B';
    public $WIDTH = 250;
    public $HEIGHT = 75;
    public $XRES = 1;
    public $RESULT_PATH = 'barcode/APP_Form_Result/';
    public $POD_PATH = 'barcode/Personal_Barcode/';
    public $TOTAL_FEE = 0;
    public $IB_BARCODE_TYPE = 'C128C';
    public $IB_WIDTH = 430;
    public $IB_HEIGHT = 60;
    public $IB_XRES = 2;
    public $IB_PATH = 'barcode/Initiating_Barcode/';
    public $IIN = '98261754';
    public $USEDBYDATE = '';
    public $MAXDAYS = " +28 day";
    public $MAXMONTHS = " +12 month";
    public $MINMONTHS = " +3 month";


    function __construct($urlImgPath) {
        $this->urlImgPath = $urlImgPath;
        parent::__construct();
        return true;
    }

    /*
     * Function to generate pdf letter
     */

    function generateLetterPDF($confid, $lang) {

        $pound = html_entity_decode('&pound;');
        /* Copy Symbol */
        $copySymbol = html_entity_decode('&copy;');


        ##########  TEXT COLOR  ###########

        $r = .40;
        $g = .19;
        $b = .57;
        #################
        ##########  White TEXT COLOR  ###########
        $whiter = 1;
        $whiteg = 1;
        $whiteb = 1;

        #################
        ##########  Disclosures TEXT COLOR  ###########
        $discr = .58;
        $discg = .58;
        $discb = .60;

        #################
        ##########  Disclosures TEXT COLOR  ###########
        $otherr = .18;
        $otherg = .21;
        $otherb = .57;

        #################
        ##########  Filled Rectangle (BackGround color) #########

        $fr = .40;
        $fg = .14;
        $fb = .40;

        #######################
        ##########  Line Color #########

        $lr = .40;
        $lg = .19;
        $lb = .57;

        #######################

        $docCount = count($documentDetails);
        $date = $usedByDate;

        $pdf = & new Cezpdf('a4', 'portrait');
//$pdf->setPreferences('HideToolbar','false');
//$pdf->setPreferences('HideMenuBar','true');
        $pdf->setPreferences('HideWindoUI', 'true');
        $pdf->setPreferences('FitWindow', 'true');

        $pdf->ezSetMargins(50, 50, 0, 0);
        $rights = array('print');

        ###################################################################################################################################
        if (!empty($confid)) {

            $objApplication = new Application();

            #get Application Reference Number
            $resAppRefNo = $objApplication->getAppRefNo($confid);
            $appRefNo = $resAppRefNo[0]['app_ref_no'];

            #get Applicant PDF Information
            $pdfres = $objApplication->getAppPDFInfo($appRefNo);


            #get country name from ebulk_iso_country_code
            $country1name = $this->getCountyEbulkISO($pdfres[0]['app_addr_country_code']);

            #RB Refrence Number
            $rbAppRefNo = $pdfres[0]['rb_app_ref_no'];

            #get result name from ebulk_result_batch_person_names
            $pdfresultname = $this->getEbulkResultNames($rbAppRefNo);
            $othernames = $this->correctcase($pdfresultname[0]["result_name"]);

            #get photoimage,photo_image_accepted from Section X
            $pdfimage = $this->getAppPhotoInfo($confid);
            $photoimage = $pdfimage[0]["photoimage"];

            if ($pdfimage[0]["photo_image_accepted"] == '1' || $pdfimage[0]["photo_image_accepted"] == '0') {
                $disclosuretype = $this->correctcase($pdfres[0]["disclosure_type"]);
                $disclosureno = $pdfres[0]["disclosure_number"];
                $disclosureIssueDate = $pdfres[0]["disclosure_issue_date"];
                $positionapplied = $pdfres[0]["emp_position_applied_for"];
                $registeredbody = $pdfres[0]["cntsig_rb_name"];
                $countersignatory = $pdfres[0]["cntsig_full_name"];
                $nameofemployer = $pdfres[0]["emp_org_name"];
                $surname = $pdfres[0]["app_surname"];
                $forename = $pdfres[0]["app_forname"];
                $dob = $pdfres[0]["app_dob"];
                $placeofbirth = $pdfres[0]["app_place_of_birth"];
                $gender = $this->correctcase($pdfres[0]["app_gender"]);
                $address1 = $pdfres[0]["app_addr_line1"];
                $address2 = $pdfres[0]["app_addr_line2"];
                $town = $pdfres[0]["app_addr_town"];
                $country = $pdfres[0]["app_addr_country"];
                $postcode = $pdfres[0]["app_addr_postcode"];
                $countrycode = $country1name;
                $policerecordsofconvictions = strtoupper($pdfres[0]["disc_police_records_of_convictions"]);
                $eduactlist = strtoupper($pdfres[0]["disc_edu_act_list"]);
                $protectionchild = strtoupper($pdfres[0]["disc_isa_child_barred_list"]);
                $vulnerableadult = strtoupper($pdfres[0]["disc_isa_vulnerable_adult_barred_list"]);
                $otherrelaventinfo = strtoupper($pdfres[0]["ecert_other_relevant_infn"]);

                if (empty($eduactlist) && strtoupper($disclosuretype) == "STANDARD")
                    $eduactlist = "NOT REQUESTED";

                if (empty($protectionchild) && strtoupper($disclosuretype) == "STANDARD")
                    $protectionchild = "NOT REQUESTED";

                if (empty($vulnerableadult) && strtoupper($disclosuretype) == "STANDARD")
                    $vulnerableadult = "NOT REQUESTED";


                $isaflag = $pdfres[0]["isaflag"];



                //$pdf->addText(50+$offsetH,670+$offsetV,10,$firstname,0,0);

                $today = date("d") . " " . date("M") . " " . date("Y");

                ###################################################################################################################################
                //echo "Hori::::::::".$offsetH."<br>";
                //echo "Ver:::::::::".$offsetV."<br>";

                $offsetV = 30;


                ############################PAGE1

                require_once(CLASS_PATH . "page1.php");

                $pdf->setColor($fr, $fg, $fb);
                $pdf->filledRectangle(0, 0, 600, 130);
                $pdf->setColor(0, 0, 0);

                $messageEmployee = "THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY & IS REPRESENTATIVE OF DISCLOSURE INFORMATION ISSUED BY THE CRB";

                $pdf->ezSetY(110);

                $pdf->ezSetY(110);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->ezText($messageEmployee, 7, array('justification' => 'full', 'left' => '40', 'right' => '285'));
                $pdf->setColor(0, 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->ezSetY(770);


                if(empty($setAddresLineTwo) && empty($setAddresLineOne))
                {
                    $offsetV = 38;
                }
                if(empty($setAddresLineTwo) && !empty($setAddresLineOne)){
                    $offsetV=38;
                }


                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->addText(40 + $offsetH, 75 + $offsetV, 15, "Employers Copy", 0, 0);
                /* $pdf->addText(40+$offsetH,60+$offsetV,9,"THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY & IS\nREPRESENTATIVE OF DISCLOSURE INFORMATION ISSUED\nBY THE CRB",0,0); */



                $pdf->selectFont(FONT_PATH . 'Times-Roman.afm');
//$pdf->addText(35+$offsetH,38+$offsetV,26,"@",0,0);
                $pdf->addText(40 + $offsetH, 42 + $offsetV, 15, $lang['atlanticDataLtd'], 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->addText(40 + $offsetH, 29 + $offsetV, 9, $lang["kindRegards"], 0, 0);
                $pdf->addText(40 + $offsetH, 19 + $offsetV, 9, $lang["disclosureServiceAddressone"], 0, 0);
                $pdf->addText(40 + $offsetH, 9 + $offsetV, 9, $lang["disclosureServiceAddresstwo"], 0, 0);
                $pdf->addText(40 + $offsetH, -2 + $offsetV, 9, $lang["disclosureServiceAddressthree"], 0, 0);
                $pdf->addText(40 + $offsetH, -12 + $offsetV, 9, $lang["disclosureService"], 0, 0);
                $pdf->addText(40 + $offsetH, -22 + $offsetV, 9, $lang["disclosureServiceAddressfour"], 0, 0);
                $pdf->setColor(0, 0, 0);



                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->filledRectangle(320, 50, 250, 70);

                $timedet = time();
                $barcode = $confid . $timedet;

                if (isset($barcode) && strlen($barcode) > 0) {
                    $barcodesrc = $this->generateResultbarcode($barcode, $appRefNo);
                }

                //$lineCount=$lineCount-20;
//$pdf->addJpegFromFile($barcodesrc,330,703,$width,$height);
//-------------------- Barcode Ends here ---------------------
//$image="../images/barcode.jpg";
                $pdf->addJpegFromFile($barcodesrc, 350, 55, 200);


                $pdf->addText(520 + $offsetH, 1 + $offsetV, 10, "Page 1 of 2", 0, 0);

                $pdf->addText(250 + $offsetH, -15 + $offsetV, 6, $copySymbol . " This document is copyright to @lantic Data Ltd", 0, 0);
                $pdf->setColor(0, 0, 0);
##endfooter
##newpage
                $pdf->ezNewPage();
                $pdf->selectFont(FONT_PATH . 'gilfont.afm');
                $pdf->ezSetY(800);

#########################END OF PAGE1
############################PAGE2

                include(CLASS_PATH . "page2.php");

##footer

                $pdf->setColor($fr, $fg, $fb);
                $pdf->filledRectangle(0, 0, 600, 130);
                $pdf->setColor(0, 0, 0);

                $messageEmployee = "THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY & IS REPRESENTATIVE OF DISCLOSURE INFORMATION ISSUED BY THE CRB";

                $pdf->ezSetY(110);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->ezText($messageEmployee, 7, array('justification' => 'full', 'left' => '40', 'right' => '285'));
                $pdf->setColor(0, 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->ezSetY(770);



                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->addText(40 + $offsetH, 75 + $offsetV, 15, "Employers Copy", 0, 0);
//$pdf->addText(40+$offsetH,60+$offsetV,9,"THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY",0,0);
                $pdf->selectFont(FONT_PATH . 'Times-Roman.afm');
//$pdf->addText(35+$offsetH,38+$offsetV,26,"@",0,0);
                $pdf->addText(40 + $offsetH, 42 + $offsetV, 15, $lang['atlanticDataLtd'], 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->addText(40 + $offsetH, 29 + $offsetV, 9, $lang["kindRegards"], 0, 0);
                $pdf->addText(40 + $offsetH, 19 + $offsetV, 9, $lang["disclosureServiceAddressone"], 0, 0);
                $pdf->addText(40 + $offsetH, 9 + $offsetV, 9, $lang["disclosureServiceAddresstwo"], 0, 0);
                $pdf->addText(40 + $offsetH, -2 + $offsetV, 9, $lang["disclosureServiceAddressthree"], 0, 0);
                $pdf->addText(40 + $offsetH, -12 + $offsetV, 9, $lang["disclosureService"], 0, 0);
                $pdf->addText(40 + $offsetH, -22 + $offsetV, 9, $lang["disclosureServiceAddressfour"], 0, 0);
                $pdf->setColor(0, 0, 0);

                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->filledRectangle(320, 50, 250, 70);


//$image="images/barcode.jpg";
//$pdf->addJpegFromFile($image,350,55,200);

                $pdf->addJpegFromFile($barcodesrc, 350, 55, 200);
                $pdf->addText(520 + $offsetH, 1 + $offsetV, 10, "Page 2 of 2", 0, 0);
                $pdf->addText(250 + $offsetH, -15 + $offsetV, 6, $copySymbol . ' This document is copyright to @lantic Data Ltd', 0, 0);

//$pdf->addText(250+$offsetH,-15+$offsetV,6,"© This document is copyright to @lantic Data Ltd",0,0);
                $pdf->setColor(0, 0, 0);
            } else {
                $pdf->addText(200 + $offsetH, 700 + $offsetV, 30, "Access Denied", 0, 0);
            }
        } else {

            $pdf->addText(200 + $offsetH, 700 + $offsetV, 30, "Access Denied", 0, 0);
        }

        if (!empty($d) && $d) {
            $pdfcode = $pdf->output(1);
            //$end_time = getmicrotime();
            $pdfcode = str_replace("\n", "\n<br>", htmlspecialchars($pdfcode));
            echo '<html><body>';
            echo trim($pdfcode);
            echo '</body></html>';
        } else {
            $pdfcode = $pdf->output();
            $pdf->stream();
        }
//       
    }

    /*
     * Function to generate pdf letter ends
     */

    public function getCountyEbulkISO($app_addr_country_code) {

        $qryres = "select country_name from ebulk_iso_country_code where country_code='" . $app_addr_country_code . "'";
        $countryres = $this->getDBRecords($qryres);
        $countryname = $countryres[0]["country_name"];
        return $countryname;
    }

    public function getEbulkResultNames($_rb_app_ref_no) {
        $pdfqueryresultname = "select result_name from ebulk_result_batch_person_names where rb_ref_no = '$_rb_app_ref_no'";
        $pdfresultname = $this->getDBRecords($pdfqueryresultname);
        return $pdfresultname;
    }

    public function getAppPhotoInfo($confid) {
        $pdfqueryimage = "select photoimage,photo_image_accepted from sectionx where application_id = '$confid'";
        $pdfimage = $this->getDBRecords($pdfqueryimage);
        return $pdfimage;
    }

    public function generateResultbarcode($barcode, $appRefNo) {
        $objGenerateBarcode = new Cgenbarcode();
        $objGenerateBarcode->genBarcode(trim($barcode), $this->IMAGE_TYPE, $this->BARCODE_TYPE, $this->WIDTH, $this->HEIGHT, $this->XRES, $this->FONT, $this->RESULT_PATH . 'appRefNo_' . $appRefNo . '.jpeg');
        return $this->RESULT_PATH . 'appRefNo_' . $appRefNo . '.jpeg';
    }
    
    
     /*
     * Function to generate pdf letter with terminology dbs
     */

   public function generateLetterPDFDBS($confid, $lang) {

        $pound = html_entity_decode('&pound;');
        /* Copy Symbol */
        $copySymbol = html_entity_decode('&copy;');


        ##########  TEXT COLOR  ###########

        $r = .40;
        $g = .19;
        $b = .57;
        #################
        ##########  White TEXT COLOR  ###########
        $whiter = 1;
        $whiteg = 1;
        $whiteb = 1;

        #################
        ##########  Disclosures TEXT COLOR  ###########
        $discr = .58;
        $discg = .58;
        $discb = .60;

        #################
        ##########  Disclosures TEXT COLOR  ###########
        $otherr = .18;
        $otherg = .21;
        $otherb = .57;

        #################
        ##########  Filled Rectangle (BackGround color) #########

        $fr = .40;
        $fg = .14;
        $fb = .40;

        #######################
        ##########  Line Color #########

        $lr = .40;
        $lg = .19;
        $lb = .57;

        #######################

        $docCount = count($documentDetails);
        $date = $usedByDate;

        $pdf = & new Cezpdf('a4', 'portrait');
//$pdf->setPreferences('HideToolbar','false');
//$pdf->setPreferences('HideMenuBar','true');
        $pdf->setPreferences('HideWindoUI', 'true');
        $pdf->setPreferences('FitWindow', 'true');

        $pdf->ezSetMargins(50, 50, 0, 0);
        $rights = array('print');

        ###################################################################################################################################
        if (!empty($confid)) {

            $objApplication = new Application();

            #get Application Reference Number
            $resAppRefNo = $objApplication->getAppRefNo($confid);
            $appRefNo = $resAppRefNo[0]['app_ref_no'];

            #get Applicant PDF Information
            $pdfres = $objApplication->getAppPDFInfo($appRefNo);


            #get country name from ebulk_iso_country_code
            $country1name = $this->getCountyEbulkISO($pdfres[0]['app_addr_country_code']);

            #RB Refrence Number
            $rbAppRefNo = $pdfres[0]['rb_app_ref_no'];

            #get result name from ebulk_result_batch_person_names
            $pdfresultname = $this->getEbulkResultNames($rbAppRefNo);
            $othernames = $this->correctcase($pdfresultname[0]["result_name"]);

            #get photoimage,photo_image_accepted from Section X
            $pdfimage = $this->getAppPhotoInfo($confid);
            $photoimage = $pdfimage[0]["photoimage"];

            if ($pdfimage[0]["photo_image_accepted"] == '1' || $pdfimage[0]["photo_image_accepted"] == '0') {
                $disclosuretype = $this->correctcase($pdfres[0]["disclosure_type"]);
                $disclosureno = $pdfres[0]["disclosure_number"];
                $disclosureIssueDate = $pdfres[0]["disclosure_issue_date"];
                $positionapplied = $pdfres[0]["emp_position_applied_for"];
                $registeredbody = $pdfres[0]["cntsig_rb_name"];
                $countersignatory = $pdfres[0]["cntsig_full_name"];
                $nameofemployer = $pdfres[0]["emp_org_name"];
                $surname = $pdfres[0]["app_surname"];
                $forename = $pdfres[0]["app_forname"];
                $dob = $pdfres[0]["app_dob"];
                $placeofbirth = $pdfres[0]["app_place_of_birth"];
                $gender = $this->correctcase($pdfres[0]["app_gender"]);
                $address1 = $pdfres[0]["app_addr_line1"];
                $address2 = $pdfres[0]["app_addr_line2"];
                $town = $pdfres[0]["app_addr_town"];
                $country = $pdfres[0]["app_addr_country"];
                $postcode = $pdfres[0]["app_addr_postcode"];
                $countrycode = $country1name;
                $policerecordsofconvictions = strtoupper($pdfres[0]["disc_police_records_of_convictions"]);
                $eduactlist = strtoupper($pdfres[0]["disc_edu_act_list"]);
                $protectionchild = strtoupper($pdfres[0]["disc_isa_child_barred_list"]);
                $vulnerableadult = strtoupper($pdfres[0]["disc_isa_vulnerable_adult_barred_list"]);
                $otherrelaventinfo = strtoupper($pdfres[0]["ecert_other_relevant_infn"]);

                if (empty($eduactlist) && strtoupper($disclosuretype) == "STANDARD")
                    $eduactlist = "NOT REQUESTED";

                if (empty($protectionchild) && strtoupper($disclosuretype) == "STANDARD")
                    $protectionchild = "NOT REQUESTED";

                if (empty($vulnerableadult) && strtoupper($disclosuretype) == "STANDARD")
                    $vulnerableadult = "NOT REQUESTED";


                $isaflag = $pdfres[0]["isaflag"];



                //$pdf->addText(50+$offsetH,670+$offsetV,10,$firstname,0,0);

                $today = date("d") . " " . date("M") . " " . date("Y");

                ###################################################################################################################################
                //echo "Hori::::::::".$offsetH."<br>";
                //echo "Ver:::::::::".$offsetV."<br>";

                $offsetV = 30;


                ############################PAGE1

                require_once(CLASS_PATH . "dbs_page1.php");

                $pdf->setColor($fr, $fg, $fb);
                $pdf->filledRectangle(0, 0, 600, 130);
                $pdf->setColor(0, 0, 0);

                $messageEmployee = "THIS ".strtoupper(CERT_DISC)." IS NOT EVIDENCE OF IDENTITY & IS REPRESENTATIVE OF ".strtoupper(CERT_DISC)." INFORMATION ISSUED BY THE ".DBS;

                $pdf->ezSetY(110);

                $pdf->ezSetY(110);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->ezText($messageEmployee, 7, array('justification' => 'full', 'left' => '40', 'right' => '285'));
                $pdf->setColor(0, 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->ezSetY(770);


                if(empty($setAddresLineTwo) && empty($setAddresLineOne))
                {
                    $offsetV = 38;
                }
                if(empty($setAddresLineTwo) && !empty($setAddresLineOne)){
                    $offsetV=38;
                }


                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->addText(40 + $offsetH, 75 + $offsetV, 15, "Employers Copy", 0, 0);
                /* $pdf->addText(40+$offsetH,60+$offsetV,9,"THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY & IS\nREPRESENTATIVE OF DISCLOSURE INFORMATION ISSUED\nBY THE CRB",0,0); */



                $pdf->selectFont(FONT_PATH . 'Times-Roman.afm');
//$pdf->addText(35+$offsetH,38+$offsetV,26,"@",0,0);
                //$pdf->addText(40 + $offsetH, 42 + $offsetV, 15, $lang['atlanticDataLtd'], 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                //$pdf->addText(40 + $offsetH, 29 + $offsetV, 9, $lang["kindRegards"], 0, 0);
                $pdf->addText(40 + $offsetH, 29 + $offsetV, 15, $lang['disclosureServiceAddressone'], 0, 0);
                $pdf->addText(40 + $offsetH, 19 + $offsetV, 9, $lang["disclosureServiceAddresstwo"], 0, 0);
                $pdf->addText(40 + $offsetH, 9 + $offsetV, 9, $lang["disclosureServiceAddressthree"], 0, 0);
                $pdf->addText(40 + $offsetH, -2 + $offsetV, 9, $lang["disclosureServiceAddressfour"], 0, 0);
               
               
                $pdf->setColor(0, 0, 0);



                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->filledRectangle(320, 50, 250, 70);

                $timedet = time();
                $barcode = $confid . $timedet;

                if (isset($barcode) && strlen($barcode) > 0) {
                    $barcodesrc = $this->generateResultbarcode($barcode, $appRefNo);
                }

                //$lineCount=$lineCount-20;
//$pdf->addJpegFromFile($barcodesrc,330,703,$width,$height);
//-------------------- Barcode Ends here ---------------------
//$image="../images/barcode.jpg";
                $pdf->addJpegFromFile($barcodesrc, 350, 55, 200);


                $pdf->addText(520 + $offsetH, 1 + $offsetV, 10, "Page 1 of 2", 0, 0);

                $pdf->addText(250 + $offsetH, -15 + $offsetV, 6, $copySymbol . " This document is copyright to @lantic Data Ltd", 0, 0);
                $pdf->setColor(0, 0, 0);
##endfooter
##newpage
                $pdf->ezNewPage();
                $pdf->selectFont(FONT_PATH . 'gilfont.afm');
                $pdf->ezSetY(800);

#########################END OF PAGE1
############################PAGE2

                include(CLASS_PATH . "dbs_page2.php");

##footer

                $pdf->setColor($fr, $fg, $fb);
                $pdf->filledRectangle(0, 0, 600, 130);
                $pdf->setColor(0, 0, 0);

                $messageEmployee = "THIS ".strtoupper(CERT_DISC)." IS NOT EVIDENCE OF IDENTITY & IS REPRESENTATIVE OF ".strtoupper(CERT_DISC)." INFORMATION ISSUED BY THE ".DBS;

                $pdf->ezSetY(110);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->ezText($messageEmployee, 7, array('justification' => 'full', 'left' => '40', 'right' => '285'));
                $pdf->setColor(0, 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                $pdf->ezSetY(770);



                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->addText(40 + $offsetH, 75 + $offsetV, 15, "Employers Copy", 0, 0);
//$pdf->addText(40+$offsetH,60+$offsetV,9,"THIS DISCLOSURE IS NOT EVIDENCE OF IDENTITY",0,0);
             $pdf->selectFont(FONT_PATH . 'Times-Roman.afm');
//$pdf->addText(35+$offsetH,38+$offsetV,26,"@",0,0);
                //$pdf->addText(40 + $offsetH, 42 + $offsetV, 15, $lang['atlanticDataLtd'], 0, 0);
                $pdf->selectFont(FONT_PATH . 'arial.afm');
                //$pdf->addText(40 + $offsetH, 29 + $offsetV, 9, $lang["kindRegards"], 0, 0);
                $pdf->addText(40 + $offsetH, 29 + $offsetV, 15, $lang['disclosureServiceAddressone'], 0, 0);
                $pdf->addText(40 + $offsetH, 19 + $offsetV, 9, $lang["disclosureServiceAddresstwo"], 0, 0);
                $pdf->addText(40 + $offsetH, 9 + $offsetV, 9, $lang["disclosureServiceAddressthree"], 0, 0);
                $pdf->addText(40 + $offsetH, -2 + $offsetV, 9, $lang["disclosureServiceAddressfour"], 0, 0);
               
               
                $pdf->setColor(0, 0, 0);


                $pdf->setColor($whiter, $whiteg, $whiteb);
                $pdf->filledRectangle(320, 50, 250, 70);


//$image="images/barcode.jpg";
//$pdf->addJpegFromFile($image,350,55,200);

                $pdf->addJpegFromFile($barcodesrc, 350, 55, 200);
                $pdf->addText(520 + $offsetH, 1 + $offsetV, 10, "Page 2 of 2", 0, 0);
                $pdf->addText(250 + $offsetH, -15 + $offsetV, 6, $copySymbol . ' This document is copyright to @lantic Data Ltd', 0, 0);

//$pdf->addText(250+$offsetH,-15+$offsetV,6,"© This document is copyright to @lantic Data Ltd",0,0);
                $pdf->setColor(0, 0, 0);
            } else {
                $pdf->addText(200 + $offsetH, 700 + $offsetV, 30, "Access Denied", 0, 0);
            }
        } else {

            $pdf->addText(200 + $offsetH, 700 + $offsetV, 30, "Access Denied", 0, 0);
        }

        if (!empty($d) && $d) {
            $pdfcode = $pdf->output(1);
            //$end_time = getmicrotime();
            $pdfcode = str_replace("\n", "\n<br>", htmlspecialchars($pdfcode));
            echo '<html><body>';
            echo trim($pdfcode);
            echo '</body></html>';
        } else {
            $pdfcode = $pdf->output();
            $pdf->stream();
        }
//       
    }

    /*
     * Function to generate pdf letter ends
     */

}

?>
