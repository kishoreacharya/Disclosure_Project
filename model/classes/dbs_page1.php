<?php

#get TrustNane

$pdf->ezSetY(770);
//$image="images/access.jpg";
$image=$this->ImgPathPDF.'background.jpg';
# filename/left margin/height from bottom/picture width/picture height
$pdf->addJpegFromFile($image,0,130,595);

$pdf->setColor($fr,$fg,$fb);
$pdf->filledRectangle(0,790,600,65);
$pdf->setColor(0,0,0);

$msg80="<b>STRICTLY PRIVATE AND CONFIDENTIAL</b>";

$msg81="This is not a Certificate issued by the ".CERT_DBS.".\nIt is representative of the ".CERT_DISC." Information issued by the ".CERT_DISCLOSURES." and/to ".$this->correctcase($registeredbody).".";


$pdf->ezSetY(828);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg80,14,array('justification'=>'full','left'=>'130','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'/arial.afm');

$pdf->ezSetY(813);
$pdf->selectFont(FONT_PATH.'/arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg81,8,array('justification'=>'center','left'=>'0','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'/arial.afm');
$pdf->ezSetY(770);



if($uploadFile=='Y')
{
	if($pdfimage[0]["photo_image_accepted"]=='1') {
	  if (file_exists(CROP_FOLDER."/".$photoimage)) {
	      $image=ConvertToJpeg(CROP_FOLDER."/".$photoimage);
	      $pdf->addJpegFromFile($image,40,650,90);
	  }
	  else {
	     $noimage=$this->ImgPathPDF.'ebulk_noimage.jpg';
	     //$image=ConvertToJpeg(CROP_FOLDER."/".$noimage);
	     $pdf->addJpegFromFile($image,40,650,90);
	  }
	}
	else {
	 $noimage=$this->ImgPathPDF.'ebulk_noimage.jpg';
	//$image=ConvertToJpeg(CROP_FOLDER."/".$noimage);
	$pdf->addJpegFromFile($image,40,650,90);
	}
} else {
$noimage=$this->ImgPathPDF.'ebulk_noimage.jpg';
$pdf->addJpegFromFile($noimage,40,650,90);
}

$noimage=$this->ImgPathPDF.'ebulk_noimage.jpg';
$pdf->addJpegFromFile($noimage,40,650,90);

//$pdf->selectFont(LIB_PATH.'fonts/gothic.afm');

$pdf->setColor($discr,$discg,$discb);
$pdf->addText(355+$offsetH,715+$offsetV,30,"Disclosures",0,0);
$pdf->setColor($fr,$fg,$fb);
$pdf->addText(512+$offsetH,715+$offsetV,30,CERT_DBS,0,0);
$pdf->setColor(0,0,0);


//$pdf->ezSetY(760);

$pdf->selectFont(FONT_PATH.'arial.afm');

$pdf->setColor($lr,$lg,$lb);
$pdf->addText(140+$offsetH,690+$offsetV,12,"Employers Copy",0,0);
$pdf->addText(355+$offsetH,690+$offsetV,15,CERT_DISC_LEVEL.":",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(470+$offsetH,690+$offsetV,15,$disclosuretype,0,0);
$count_of_othernames=count($pdfresultname);
if($count_of_othernames>13) {
    $pdf->addText(355+$offsetH,675+$offsetV,10,"Page 1 of 3",0,0);
}else {
    $pdf->addText(355+$offsetH,675+$offsetV,10,"Page 1 of 2",0,0);
}


$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(140+$offsetH,660+$offsetV,9,CERT_DISC."Number:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(220+$offsetH,660+$offsetV,8,$disclosureno,0,0);

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(140+$offsetH,645+$offsetV,9,CERT_DISC."IssueDate:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(230+$offsetH,645+$offsetV,7,$disclosureIssueDate,0,0);

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(140+$offsetH,630+$offsetV,9,"Position applied for:",0,0);
$pdf->setColor(0,0,0);
//$pdf->addText(225+$offsetH,630+$offsetV,10,$positionapplied,0,0);
//$pdf->addText(222+$offsetH,630+$offsetV,6,strtoupper($positionapplied),0,0);
$stringLenth=strlen($positionapplied);
if($stringLenth > 30)
{

	$p_wordLimited_2 = explode(" ", $positionapplied,2);
	for($i = 0; $i < count($p_wordLimited_2); $i++)
	{
	$p_wordLimited_2[$i];
	}

	$p_wordLimited_3 = explode(" ", $positionapplied, 3);
	for($i = 0; $i < count($p_wordLimited_3); $i++)
	{
	$p_wordLimited_3[$i] ;
	}


    if(strlen($p_wordLimited_2[0]) > 28)
		{
		$pdf->addText(140+$offsetH,620+$offsetV,6,$p_wordLimited_2[0]." ".$p_wordLimited_2[1],0,0);
		//$pdf->addText(140+$offsetH,610+$offsetV,6,$p_wordLimited_2[1],0,0);
		}
	else
		{
				if(strlen($p_wordLimited_3[0]) <= 9 && strlen($p_wordLimited_3[1]) <= 9)
				{
				$pdf->addText(225+$offsetH,630+$offsetV,6,$p_wordLimited_3[0]." ".$p_wordLimited_3[1],0,0);
				$pdf->addText(140+$offsetH,615+$offsetV,6,$p_wordLimited_3[2],0,0);
				}
				else  {
				 $pdf->addText(224+$offsetH,630+$offsetV,6,$p_wordLimited_2[0],0,0);
				 $pdf->addText(140+$offsetH,615+$offsetV,6,$p_wordLimited_2[1],0,0);
				}
		}


}else{

$pdf->addText(222+$offsetH,630+$offsetV,6,$positionapplied,0,0);
}


$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(355+$offsetH,660+$offsetV,9,"Registered Body:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(428+$offsetH,660+$offsetV,7,strtoupper($registeredbody),0,0);
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(355+$offsetH,645+$offsetV,9,"Countersignatory:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(429+$offsetH,645+$offsetV,6,strtoupper($countersignatory),0,0);

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(355+$offsetH,630+$offsetV,9,"Name of employer:",0,0);
$pdf->setColor(0,0,0);
//$pdf->addText(450+$offsetH,630+$offsetV,10,$nameofemployer,0,0);
//$pdf->addText(434+$offsetH,630+$offsetV,6,strtoupper($nameofemployer),0,0);
$stringLenth_e=strlen($nameofemployer);


if($stringLenth_e > 30)
{

	$wordLimited_2 = explode(" ", $nameofemployer,2);
	for($i = 0; $i < count($wordLimited_2); $i++)
	{
	$wordLimited_2[$i];
	}

	$wordLimited_3 = explode(" ", $nameofemployer, 3);
	for($i = 0; $i < count($wordLimited_3); $i++)
	{
	$wordLimited_3[$i] ;
	}


    if(strlen($wordLimited_2[0]) > 30)
		{
		$pdf->addText(355+$offsetH,620+$offsetV,6,$wordLimited_2[0],0,0);
		$pdf->addText(355+$offsetH,610+$offsetV,6,$wordLimited_2[1],0,0);
		}
	else
		{
				if(strlen($wordLimited_3[0]) <= 10 && strlen($wordLimited_3[1]) <= 10)
				{
				 $pdf->addText(434+$offsetH,630+$offsetV,6,$wordLimited_3[0]." ".$wordLimited_3[1],0,0);
				 $pdf->addText(355+$offsetH,615+$offsetV,6,$wordLimited_3[2],0,0);
				}
				else  {
				$pdf->addText(434+$offsetH,630+$offsetV,6,$wordLimited_2[0],0,0);
				$pdf->addText(355+$offsetH,615+$offsetV,6,$wordLimited_2[1],0,0);
				}
		}


}
else {
$pdf->addText(434+$offsetH,630+$offsetV,6,$nameofemployer,0,0);
}


$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,638,570,638);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);


#################################################################################################



$pdf->addText(40+$offsetH,590+$offsetV,10,"Applicant Personal Details",0,0);
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,570+$offsetV,8,"Surname:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(120+$offsetH,570+$offsetV,6,strtoupper($surname),0,0);

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,550+$offsetV,8,"Forename(s):",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(120+$offsetH,550+$offsetV,6,strtoupper($forename),0,0);

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,530+$offsetV,8,"Other Names:",0,0);
//$pdf->setColor(0,0,0);
//$pdf->addText(120+$offsetH,530+$offsetV,6,strtoupper($othernames),0,0);

$line_count=530;
$total_othernames=count($pdfresultname);

$pdf->setColor(0,0,0);
$k=0;
while($total_othernames!=0 && $k<13) {   ### Display atleast 13 othernames
    $othernames=$this->correctcase($pdfresultname[$k]["result_name"]);
    $pdf->addText(120+$offsetH,$line_count+$offsetV,6,strtoupper($othernames),0,0);

    $k++;
    $total_othernames--;
    $line_count=$line_count-10;
}
$rem_othernames=$total_othernames; ### set remaining othernames

$line_count=$line_count-20;
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,$line_count+$offsetV,8,"Date of Birth:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(120+$offsetH,$line_count+$offsetV,6,$dob,0,0);

$line_count=$line_count-20;
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,$line_count+$offsetV,8,"Place of Birth:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(120+$offsetH,$line_count+$offsetV,6,strtoupper($placeofbirth),0,0);

$line_count=$line_count-20;

$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(40+$offsetH,$line_count+$offsetV,8,"Gender:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(120+$offsetH,$line_count+$offsetV,6,strtoupper($gender),0,0);

############## Current Address ##############################
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->addText(265+$offsetH,570+$offsetV,8,"Current Address:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(350+$offsetH,570+$offsetV,6,strtoupper($address1),0,0);

//echo $offsetV;

if(!empty($address2)) { $offsetV=38; } else { $offsetV=52;}
$pdf->addText(350+$offsetH,545+$offsetV,6,strtoupper($address2),0,0);

$offsetV=38;
$pdf->addText(350+$offsetH,530+$offsetV,6,strtoupper($town),0,0);
$pdf->addText(350+$offsetH,515+$offsetV,6,strtoupper($country),0,0);
$pdf->addText(350+$offsetH,500+$offsetV,6,strtoupper($postcode),0,0);
$pdf->addText(350+$offsetH,485+$offsetV,6,$countrycode,0,0);

##############################################################
$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,$line_count,570,$line_count);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);

$line_count=$line_count-5;
$msg1="<b>Police Records of Convictions, Cautions, Reprimands & Final Warnings</b>";
$msg11="<b>".$policerecordsofconvictions."</b>";

$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg1,10,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-15;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg11,8,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-20;
$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,$line_count,570,$line_count);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);

$msg2="<b>Information from the list held under Section 142 of the Education Act 2002</b>";
$msg22="<b>".$eduactlist."</b>";

$line_count=$line_count-5;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg2,10,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-15;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg22,8,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-20;
$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,$line_count,570,$line_count);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);

$line_count=$line_count-5;
$msg3="<b>$CERT_DCBL Information</b>";
$msg33="<b>".$protectionchild."</b>";

$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg3,10,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-15;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg33,8,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-20;
$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,$line_count,570,$line_count);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);

$line_count=$line_count-5;
$msg4="<b>$CERT_DABL Information</b>";
$msg44="<b>".$vulnerableadult."</b>";

$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg4,10,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-15;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg44,8,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-20;
$pdf->setStrokeColor($lr,$lg,$lb);
$pdf->setLineStyle(2,'','',array(0.1));
$pdf->line(40,$line_count,570,$line_count);
$pdf->setLineStyle(1,'','',array(0.1));
$pdf->setStrokeColor(0,0,0);

$line_count=$line_count-5;
$msg5="<b>Other relevant information disclosed at the Chief Police Officer(s) discretion</b>";
$msg55="<b>".$otherrelaventinfo."</b>";

$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg5,10,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);

$line_count=$line_count-15;
$pdf->ezSetY($line_count);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg55,8,array('justification'=>'full','left'=>'40','right'=>'50'));
$pdf->setColor(0,0,0);



#################################################################################################
function ConvertToJpeg($image, $back_color=false) {
    // the purpose of this function is to convert any input image to a jpeg image
    // and return the file location of the converted image
    // this function will only convert png, gif files to jpeg images
    // jpeg files will be left untouched and return the original file location
    // this function will return false if it was unable to perform the conversion
    // if supplied then $back_color must be an array each element an intiger from 0 to 255
    //      [red] = red
    //      [green] = green
    //      [blue] = blue

    if ($back_color === false) {
      $back_color = array('red' => 255, 'green' => 255, 'blue' => 255);
    } else {
      // check that all values are from 0 to 255
      foreach ($back_color as $key => $value) {
        $value = intval($value);
        if ($value < 0) {
          $value = 0;
        }
        if ($value > 255) {
          $value = 255;
        }
        $back_color[$key] = $value;
      }
    }

    $location = false;

    // first get the mime type of the image file
    if (file_exists($image)) {
      $size = @getimagesize($image);
      if ($size !== false) {
        // we have an image
        $width = $size[0];
        $height = $size[1];
        $mime = $size['mime'];
        if ($mime != '') {
          if ($mime == 'image/jpeg' || $mime == 'image/png' || $mime == 'image/gif') {
            if ($mime == 'image/jpeg') {
              $location = $image;
            } else {
              // gif or png
              $new_image = imagecreatetruecolor($width, $height);
              $background = imagecolorallocate($new_image, $back_color['red'], $back_color['green'], $back_color['blue']);
              imagefill($new_image, 0, 0, $background);
              switch ($mime) {
                case 'image/gif':
                  $overlay = imagecreatefromgif($image);
                  break;
                case 'image/png':
                  $overlay = imagecreatefrompng($image);
                  break;
              }
              imagecopy($new_image, $overlay, 0, 0, 0, 0, $width, $height);
              // create file name for new image
              $path = substr($image, 0, strrpos($image, '/')+1);
			  $imagename_5=explode("/",$image);
			  $imagename=explode(".",$imagename_5[1]);
			 //echo $imagename[0];

              $date = date('YmdHis');
              $attempt = 0;
              $location = $path.$imagename[0].'.jpg';
             /* while(file_exists($location)) {
                $attempt++;
                if ($attempt > 999) {
                  die('infinit loop detected '.__FILE__.' line '.__LINE__);
                }
                $location = $path.$date.'_'.str_pad(strval($attempt), 3, '000', STR_PAD_LEFT).'.jpg';
              }
			  */
              imagejpeg($new_image, $location);
            }
          } else {
            // wrong image type
            die('Unable to convert image of mime type <strong>"'.$mime.'"</strong>');
          }
        } else {
          die('Unable to determine mime type of <strong>"'.$image.'"</strong>');
        }
      } else {
        die('the specified file <strong>'.$image.'</strong>');
      }
    } else {
       die('<div align="center"><strong><br><br>Unable to download the PDF.<br>Applicants Photo not available.</strong></div>');
    }

    return $location;
  } // end function wt3_covertToJpeg



?>
