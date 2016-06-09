<?php
ob_start();
require("class.ezpdf.php");
class LetterPDF extends CommonLib
{
    public $urlImgPath;

    function __construct($urlImgPath) 
    {
        $this->urlImgPath = $urlImgPath;
        parent::__construct();
        return true;
    }
    
    /*
     * Function to generate pdf letter
     */
    function generateLetter($applicantDetails,$appMiddleName,$documentDetails,$totFees,$usedByDate,$appId,$appDetails,$personalDetBarcode,$idDocBarcodes,$initiatingBarcode,$cntDoc,$app_email,$orgId,$actionType)
    {
        
        $docCount = $cntDoc;
        $date = $usedByDate;
        
        $pdf = new Cezpdf('a4','portrait');
        $lineCount = 830;
        $pdf->ezSetY($lineCount);
        $lineCount = $lineCount - 20;
        $pdf->ezSetY($lineCount);
             
       
        $pdf->setPreferences('HideWindoUI','true');
        $pdf->setPreferences('FitWindow','true');


        $pdf->ezSetMargins(50,50,0,0);
                //$pdf->rectangle(10,10,575,820);#### Border of the page


        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        //$pdf->addText(20+$offsetH,810+$offsetV,30,"@",0,0);
        $pdf->addText(30,800,22,"<b>ID VERIFICATION</b>",0,0);
        $pdf->addText(30,780,22,"<b>SERVICE</b>",0,0);
 
        $ref = $this->urlImgPath;
        
//        echo $ref;
//        die();
        
      
      
        $pdf->addJpegFromFile(IMAGE_PATH."cqc_logo.jpg",400,780,168,44);
        //$pdf->addPngFromFile(IMAGE_PATH."heading_logo_cqc.png",400,780,168,44);
        $pdf->addText(490,765,10,"<i>CONFIDENTIAL</i>",0,0);
        $pdf->setStrokeColor(0.6,0.6,0.6);
        $pdf->line(30, 760, 570, 760);


        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->setColor(0.2,0.2,0.2);
        $pdf->addText(30,740,10,"<b>First Name:</b>",0,0);
       
        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
//        $pdf->addText(125,740,10,$applicantDetails[0]['forname'],0,0);
//        $pdf->setColor(0.2,0.2,0.2);
        $forename1 = $applicantDetails[0]['forname'];
        $setForenameLineOne = '';
        if(strlen(str_replace('  ',' ',$forename1)) > 30)
        {
            $forenameFlag = true;
            $forenameAdd = explode(' ', str_replace('  ',' ',$forename1));
            for($i = 0;$i < count($forenameAdd);$i++)
            {
                if(empty($strforename1))
                {
                    if(strlen($strforename1) < 25){
                    $strforename1 .= $forenameAdd[$i].' ';
                    }
                }
                 else if(strlen($strforename1.$forenameAdd[$i]) < 25){
                $strforename1 .= $forenameAdd[$i].' ';
                }
                else{
                    $strforename2 .= $forenameAdd[$i].' ';
                }
            }
            $pdf->addText(125,740,10,$strforename1,0,0);

            if(!empty($strforename2))
            {

                $setForenameLineOne = "true";
                $pdf->addText(125,730,10,$strforename2,0,0);
            }


        }
        else
        {
             $pdf->addText(125,740,10,$applicantDetails[0]['forname'],0,0);
        }
        //$pdf->addText(410.7,740,10,$applicantDetails[0]['address1'],0,0);
        $pdf->setColor(0.2,0.2,0.2);

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(307.5,740,10,"<b>Address Line 1:</b>",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        $address1 = $applicantDetails[0]['address1'];
        $setAddresLineOne = '';
        if(strlen(str_replace('  ',' ',$address1)) > 30)
        {
            $addFlag = true;
            $arrAdd = explode(' ', str_replace('  ',' ',$address1));
            for($i = 0;$i < count($arrAdd);$i++)
            {
                if(strlen($strAdd1) < 25){
                $strAdd1 .= $arrAdd[$i].' ';
                }
                else{
                    $strAdd2 .= $arrAdd[$i].' ';
                }
            }
            $pdf->addText(410.7,740,10,$strAdd1,0,0);
            
            if(!empty($strAdd2))
            {

                $setAddresLineOne = "true";
                $pdf->addText(410.7,720,10,$strAdd2,0,0);                
            }
            
            
        }
        else
        {
             $pdf->addText(410.7,740,10,$applicantDetails[0]['address1'],0,0);
        }
        //$pdf->addText(410.7,740,10,$applicantDetails[0]['address1'],0,0);
        $pdf->setColor(0.2,0.2,0.2);

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(30,720,10,"<b>Middle Name 1:</b>",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($appMiddleName[0]['middlename'] != null ||  $appMiddleName[0]['middlename'] != '')
        {
            $pdf->addText(125,720,10,$appMiddleName[0]['middlename'],0,0);
        }
        $pdf->setColor(0.2,0.2,0.2);

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
         if($setAddresLineOne != 'true')
        {
            $pdf->addText(307.5,720,10,"<b>Town:</b>",0,0);
        }
        else
        {
             $pdf->addText(307.5,700,10,"<b>Town:</b>",0,0);
        }

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($setAddresLineOne != 'true')
        {
            $pdf->addText(410.7,720,10,$applicantDetails[0]['town_city'],0,0);
        }
        else
        {
            $pdf->addText(410.7,700,10,$applicantDetails[0]['town_city'],0,0);
        }
        $pdf->setColor(0.2,0.2,0.2);


        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(30,700,10,"<b>Middle Name 2:</b>",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($appMiddleName[1]['middlename'] != null ||  $appMiddleName[1]['middlename'] != '')
        {
            $pdf->addText(125,700,10,$appMiddleName[1]['middlename'],0,0);
        }
        
        
        $pdf->setColor(0.2,0.2,0.2);


        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        if($setAddresLineOne != 'true')
        {
            $pdf->addText(307.5,700,10,"<b>Postcode:</b>",0,0);
        }
        else
        {
             $pdf->addText(307.5,680,10,"<b>Postcode:</b>",0,0);
        }

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($setAddresLineOne != 'true')
        {
            $pdf->addText(410.7,700,10,$applicantDetails[0]['postcode'],0,0);
        }
        else
        {
            $pdf->addText(410.7,680,10,$applicantDetails[0]['postcode'],0,0);
        }
        $pdf->setColor(0.2,0.2,0.2);

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(30,680,10,"<b>Middle Name 3:</b>",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($appMiddleName[2]['middlename'] != null ||  $appMiddleName[2]['middlename'] != '')
        {
            $pdf->addText(125,680,10,$appMiddleName[2]['middlename'],0,0);
        }
        $pdf->setColor(0.2,0.2,0.2);

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        if($setAddresLineOne != 'true')
        {
            $pdf->addText(307.5,680,10,"<b>Date Of Birth:</b>",0,0);
        }
        else
        {
             $pdf->addText(307.5,660,10,"<b>Date Of Birth:</b>",0,0);
        }
        

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
        if($setAddresLineOne != 'true')
        {
            $pdf->addText(410.7,680,10,$applicantDetails[0]['dob'],0,0);
        }
        else
        {
             $pdf->addText(410.7,660,10,$applicantDetails[0]['dob'],0,0);
        }
        $pdf->setColor(0.2,0.2,0.2);


        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(30,660,10,"<b>Surname:</b>",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor(0.4,0.4,0.4);
//        $pdf->addText(125,660,10,$applicantDetails[0]['surname'],0,0);
//        $pdf->setColor(0,0,0);

        $surname1 = $applicantDetails[0]['surname'];
        $setSurnameLineOne = '';
        if(strlen(str_replace('  ',' ',$surname1)) > 30)
        {
            $surnameFlag = true;
            $surnameAdd = explode(' ', str_replace('  ',' ',$surname1));
            for($i = 0;$i < count($surnameAdd);$i++)
            {
                if(empty($strsurname1))
                {
                    if(strlen($strsurname1) < 25){
                    $strsurname1 .= $surnameAdd[$i].' ';
                    }
                }
                else if(strlen($strsurname1.$surnameAdd[$i]) < 25){
                $strsurname1 .= $surnameAdd[$i].' ';
                }
                else{
                    $strsurname2 .= $surnameAdd[$i].' ';
                }
            }
            $pdf->addText(125,660,10,$strsurname1,0,0);

            if(!empty($strsurname2))
            {

                $setSurnameLineOne = "true";
                $pdf->addText(125,650,10,$strsurname2,0,0);
            }


        }
        else
        {
             $pdf->addText(125,660,10,$applicantDetails[0]['surname'],0,0);
        }


        $pdf->line(30, 640, 570, 640);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor($whiter,$whiteg,$whiteb);
        $pdf->addText(30,630,10,"Dear ". $applicantDetails[0]['forname'].",",0,0);


        $reg = html_entity_decode('&reg;');
        $content1 = "Please take this form and your selected documents from the list below to a participating Post Office$reg branch on or before\n                    to have your identity verified.";

        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(30,594,14,"<b>$date</b>",0,0);

        $pdf->ezSetY(620);
       
        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor($whiter,$whiteg,$whiteb);
        $pdf->ezText($content1,10,array('justification'=>'full','left'=>'30','right'=>'25'));


        $content2 = "To find your nearest participating Post Office$reg branch please visit www.postoffice.co.uk/branch-finder, select CQC CRB ID Verification Service and search on your Postcode/town.";


        $pdf->ezSetY(590);
       
        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor($whiter,$whiteg,$whiteb);
        $pdf->ezText($content2,10,array('justification'=>'left','left'=>'30','right'=>'25')); 
        $content3 = "Your identity documents must be original and not photocopies, and must not be downloaded via the internet. One document must confirm your date of birth and one must confirm your current address.";

        $pdf->ezSetY(560);
       
        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');

        $pdf->setColor($whiter,$whiteg,$whiteb);
        $pdf->ezText($content3,10,array('justification'=>'left','left'=>'30','right'=>'25'));

        $pound = html_entity_decode('&pound;');

        $content4 = "You can pay the <b>$pound $totFees</b> fee by cash, or debit/credit card at the Post Office branch. If the Post Office branch cannot complete the verification process please contact Care Quality Commission helpdesk on 03000 616161 or email them at cqc.electroniccrbs@cqc.org.uk.";



        $pdf->ezSetY(530);
        //$pdf->selectFont('../fonts/Times-Roman.afm');
        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->setColor($whiter,$whiteg,$whiteb);
        $pdf->ezText($content4,10,array('justification'=>'left','left'=>'30','right'=>'25'));

        $splitCost = explode('.', $totFees);
        if(substr($splitCost[0], 0, 1) == '0')$totFees = substr($totFees, 1);
        $pdf->setColor(1,1,1);
        $pdf->filledRectangle(99,516,33,10);

        $pdf->setColor(0,0,0);
        $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
        $pdf->addText(99,518,10,"<b>$pound $totFees</b>",0,0);


        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
        $pdf->addText(30,470,10,"Regards",0,0);

        $pdf->addText(30,460,10,"Atlantic Data Online Team",0,0);

        $pdf->setLineStyle(1, '', '', array(5));
        $pdf->line(30, 450, 570, 450);

        $pdf->setLineStyle(1, '', '', array(5,0));

      
        $pdf->selectFont(FONT_PATH.'ChevinBoldItalic.afm');
        $pdf->addText(30,420,10,"For Post Office Official Use -",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinMediumItalic.afm');
        $pdf->addText(30,410,10,"Scan barcode to Serve Customer ",0,0);

        $pdf->selectFont(FONT_PATH.'ChevinLight.afm');

        $pdf->addJpegFromFile(IMAGE_PATH."1.jpg",307.5,380,40,60);
        //$pdf->addPngFromFile(IMAGE_PATH.'barcode_1.png',307.5,380,40,60);

        $pdf->addPngFromFile(IB_PATH.'app_'.$appId.'_IB.png',347.5,390,230,50);
        $pdf->addText(370,387,8.7,$initiatingBarcode,0,0);
        
        $pdf->setColor(0.6,0.6,0.6);
        $pdf->filledRectangle(30,279.3,17.5,91.5);

        $pdf->setColor(1,1,1);
        $pdf->setColor(0.6,0.6,0.6);
        $pdf->setStrokeColor(0.6,0.6,0.6);
        $pdf->rectangle(202.5,352,17.5,18);


        $pdf->filledRectangle(202,338,17.5,14);

        $pdf->setColor(0.8,0.8,0.8);
        $pdf->filledRectangle(202,323.5,17.5,14);

        $pdf->setColor(0.6,0.6,0.6);
        $pdf->filledRectangle(202,309.6,17.5,14.2);

        $pdf->setColor(0.8,0.8,0.8);
        $pdf->filledRectangle(202,295.7,17.5,14);

        $pdf->setColor(0.6,0.6,0.6);
        $pdf->filledRectangle(202,280.5,17.5,14.8);

        $pdf->setColor(1, 1, 1);
        $pdf->addText(209,342,8,"<b>B</b>",0,0);
        $pdf->setColor(1, 1, 1);
        $pdf->addText(209,327,8,"<b>C</b>",0,0);
        $pdf->addText(209,313.5,8,"<b>D</b>",0,0);
        $pdf->addText(209,300,8,"<b>E</b>",0,0);
        $pdf->addText(209,286,8,"<b>F</b>",0,0);
        $pdf->setColor(0,0,0);

        $pdf->setColor(1, 1, 1);
        $pdf->addText(35,320,12,"<b>A</b>",0,0);


        $pdf->setColor(0,0,0);

        $pdf->ezSetY(370);



            //$data = array('type'=>'A');
            //$pdf->line(30, 365, 570, 365);
            //$pdf->ezTable($data,array('type'=>'','name'=>''),'',array('showHeadings'=>0,'shaded'=>0,'xPos'=>'left','xOrientation'=>'left','width'=>100));



            $cols = array('customerid'=>"<b>CHECK CUSTOMER ID</b>",'customerDetails'=>'<b>CUSTOMER DETAILS</b>');
            $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
            $pdf->ezTable($appDetails,$cols
            ,'',array('showHeadings'=>1,'shaded'=>0,'showLines'=>2,'xPos'=>126,'width'=>160,'fontSize' => 8.7,'lineCol' =>array(0.6,0.6,0.6),'textCol'=>array(0.3,0.3,0.3),'cols'=>array('customerid'=>array('justification'=>'left','width'=>65),'customerDetails'=>array('justification'=>'left','width'=>80)),'titleFontSize'=>5));


               $pdf->ezSetY(370); 
              

            $cols = array('Document Name'=>"<b>DOCUMENTS PRESENT                         </b>",'Short Name'=>'<b>MATCH SHORT NAME    </b>','Ref'=>'<b>MATCH REF</b>','formatedDate'=>'<b>MATCH DATE</b>');

            $pdf->setColor(0.8,0.8,0.8);
            $pdf->ezTable($documentDetails,$cols
            ,'',array('showHeadings'=>1,'shaded'=>0,'showLines'=>2,'xPos'=>400,'fontSize' => 8.05,'lineCol' =>array(0.6,0.6,0.6),'textCol'=>array(0.3,0.3,0.3),'cols'=>array('docName'=>array('justification'=>'left','width'=>140),'shortName'=>array('justification'=>'left','width'=>90),'ref'=>array('justification'=>'left','width'=>60),'date'=>array('justification'=>'left','width'=>60))));
            //


                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(53,346,57,20);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(54,357.4,8.7,"CHECK",0,0);
                $pdf->addText(54,346.6,8.7,"CUSTOMER ID",0,0);
            //    
                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(118,346,57,20);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(119,357.4,8.7,"CUSTOMER",0,0);
                $pdf->addText(119,346.6,8.7,"DETAILS",0,0);

                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(223.8,356.8,100,11.5);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(223.8,357.5,8.7,"DOCUMENTS PRESENT",0,0);


                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(372,356.8,80,11.5);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(372.7,357.5,8.7,"MATCH SHORT NAME",0,0);

                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(467,356.8,45,11.5);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(467.3,357.5,8.7,"MATCH REF",0,0);

                $pdf->setColor(1,1,1);
                $pdf->filledRectangle(518,356.8,50,11.5);

                $pdf->setColor(0,0,0);
                $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');
                $pdf->addText(518.4,357.5,8.7,"MATCH DATE",0,0);



            //    
            $pdf->setColor(0,0,0);
            $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
            $pdf->addJpegFromFile(IMAGE_PATH."A.jpg",30,200,40,60);
            //$pdf->addPngFromFile(IMAGE_PATH.'a.png',30,200,40,60);
            $pdf->addPngFromFile(PB_PATH.'app_'.$appId.'_1_PODB.png',70,210,230,50);
            $pdf->addText(117,207,8.7,$personalDetBarcode,0,0);          

            $pdf->addJpegFromFile(IMAGE_PATH."B.jpg",30,130,40,60);
            //$pdf->addPngFromFile(IMAGE_PATH.'b.png',30,130,40,60);
            $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_2_IDB.png',70,140,230,50);
            $pdf->addText(117,137,8.7,$idDocBarcodes[0],0,0);     

            $pdf->addJpegFromFile(IMAGE_PATH."C.jpg",30,60,40,60);
            //$pdf->addPngFromFile(IMAGE_PATH.'c.png',30,60,40,60);
            $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_3_IDB.png',70,70,230,50);
            $pdf->addText(117,67,8.7,$idDocBarcodes[1],0,0);

            $pdf->addJpegFromFile(IMAGE_PATH."D.jpg",307.5,200,40,60);
            //$pdf->addPngFromFile(IMAGE_PATH.'d.png',307.5,200,40,60);
            $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_4_IDB.png',347.5,210,230,50);
            $pdf->addText(392.5,207,8.7,$idDocBarcodes[2],0,0);

            if($docCount > 3)
            {
                if($docCount == 4)
                {
                     $pdf->addJpegFromFile(IMAGE_PATH."E.jpg",307.5,130,40,60);
                     //$pdf->addPngFromFile(IMAGE_PATH.'e.png',307.5,130,40,60);
                     $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_5_IDB.png',347.5,140,230,50);
                     $pdf->addText(392.5,137,8.7,$idDocBarcodes[3],0,0);
                     
                     
                     $pdf->addJpegFromFile(IMAGE_PATH."F.jpg",307.5,60,40,60);
                     //$pdf->addPngFromFile(IMAGE_PATH.'f.png',307.5,60,40,60);
                     $pdf->addText(392,80,22,"<b>Not Applicable</b>",0,0);
                }
                elseif($docCount == 5)
                {
                    $pdf->addJpegFromFile(IMAGE_PATH."E.jpg",307.5,130,40,60);
                    //$pdf->addPngFromFile(IMAGE_PATH.'e.png',307.5,130,40,60);
                    $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_5_IDB.png',347.5,140,230,50);
                    $pdf->addText(392.5,137,8.7,$idDocBarcodes[3],0,0);
                    
                    $pdf->addJpegFromFile(IMAGE_PATH."F.jpg",307.5,60,40,60);
                   // $pdf->addPngFromFile(IMAGE_PATH.'f.png',307.5,60,40,60);
                    $pdf->addPngFromFile(IDB_PATH.'app_'.$appId.'_6_IDB.png',347.5,70,230,50);
                    $pdf->addText(392.5,67,8.7,$idDocBarcodes[4],0,0);
                }
                
                
            }
            else
            {
                 $pdf->addJpegFromFile(IMAGE_PATH."E.jpg",307.5,130,40,60);
                 //$pdf->addPngFromFile(IMAGE_PATH.'e.png',307.5,130,40,60);
                 $pdf->addText(392,150,22,"<b>Not Applicable</b>",0,0);

                 $pdf->addJpegFromFile(IMAGE_PATH."F.jpg",307.5,60,40,60);
                 //$pdf->addPngFromFile(IMAGE_PATH.'f.png',307.5,60,40,60);
                 $pdf->addText(392,80,22,"<b>Not Applicable</b>",0,0);
            }


            $pdf->selectFont(FONT_PATH.'_CHEVDB__.afm');

            $pdf->addText(30,40,11,"<b>PLEASE RETURN THIS LETTER AND ALL RELEVANT DOCUMENTS TO CUSTOMER AT THE END OF TRANSACTION</b>",0,0);
            $pdf->line(30, 35, 570, 35);
            //$pdf->setColor(238,130,238);
            $pdf->setColor(0.6,0.3,0.6);

            $pdf->selectFont(FONT_PATH.'ChevinLight.afm');
            //$pdf->setStrokeColor(0.6,0.3,0.6);
            //$pdf->addText(92,25,7,"disclosuresCRB.co.uk is a trading name of Atlantic Data Ltd. VAT no: 755878078. Company registration no: 4085856. Registered in England and Wales.",0,0);

           

       
    $pdfcode = $pdf->output();
   
   if($actionType == 'sendmail')
   {
      
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        /* additional headers */
        $headers .= "From: Disclosures.co.uk\r\n";

        $mail = new htmlMimeMail();

        $text = $this->mailContentText($applicantDetails[0]['forname']. ' ' . $applicantDetails[0]['surname']);
        $html = $this->mailContenthtml($applicantDetails[0]['forname']. ' ' . $applicantDetails[0]['surname']);

        $mail->setHtml($html, $text);
        $compId = $this->getCompId($orgId);
        if($compId==3)
        {
            $returnpath = "dbshr@cqc.disclosures.co.uk";
            $frompath = "dbshr@cqc.disclosures.co.uk";
        }
      else
       {
          $returnpath = "dbs@cqc.disclosures.co.uk";
          $frompath = "dbs@cqc.disclosures.co.uk";

        }
        $mail->setReturnPath($returnpath);
        $mail->setFrom($frompath);
        $mail->setSubject('Your Referral Letter');
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
         $mail->addAttachment($pdfcode, $applicantDetails[0]['forname'].'_referralLetter.pdf', 'application/pdf');
        $result = $mail->send(array($app_email), 'smtp');
         $pdf->stream();
   }
   elseif($actionType == 'savepdf')
   {
           header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header( "Content-Type: application/pdf" );
            header("Content-Disposition: attachment; filename=\"".$applicantDetails[0]['forname']."_referralLetter.pdf\";");
            header("Content-Transfer-Encoding:Â­ binary");
            header("Content-Length: ".strlen($pdfcode));
            echo $pdfcode;
      
   }
   else
   {
       $pdf->stream();
   }
     
        
    }
    /*
     * Function to generate pdf letter ends
     */

  function mailContenthtml($appName)
  {
        $msg = "Dear " . $appName.",<br/><br/>
        Thank you for submitting your Care Quality Commission (CQC) ".DBS." application.<br/><br/>

        Please take your applicant referral letter with your proof of identity used in the application and any fee to the Post Office.  You must attend the Post Office before the expiry date for the application which is quoted in the letter.<br/><br/>
        Kind Regards<br/><br/>
        ".DBS." team<br/>
                 Care Quality Commission
                 <br><br>
                 The contents of this email and any attachments are confidential to intended recipient. They may not be disclosed to or used by or copied in any way by anyone other than the intended recipient or your representative who acts on your behalf.";
        
        return $msg;
  }
  
    function mailContenttext($appName)
  {
       $msg = "Dear " . $appName .",
         Thank you for submitting your Care Quality Commission (CQC) ".DBS." application.

        Please take your applicant referral letter with your proof of identity used in the application and any fee to the Post Office.  You must attend the Post Office before the expiry date for the application which is quoted in the letter.        
                Kind Regards
                
                ".DBS." team
                 Care Quality Commission
                 
                 The contents of this email and any attachments are confidential to intended recipient. They may not be disclosed to or used by or copied in any way by anyone other than the intended recipient or your representative who acts on your behalf.";
       
       return $msg;
  }
}
?>
