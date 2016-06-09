<?php
#get TrustNane
$strTrustName=$this->getTrustDetails($company_id);

$pdf->ezSetY(770);
//$image="images/access.jpg";
$image=$this->urlImgPath."background.jpg";
//$imagebottom="../images/flag.JPG";
$pdf->addJpegFromFile($image,0,130,600);
//$pdf->addJpegFromFile($imagebottom,0,120,100);

$pdf->setColor($fr,$fg,$fb);
$pdf->filledRectangle(0,790,600,65);
$pdf->setColor(0,0,0);

$msg80="<b>STRICTLY PRIVATE AND CONFIDENTIAL</b>\n";
//$msg81="This document is representative of Disclosure information provided by the Criminal Records Bureau and issued by Atlantic Data Ltd";
$msg81="This is not a Certificate issued by the ".CERT_DBS.".\nIt is representative of the ".CERT_DISC." Information issued by the ".CERT_DISCLOSURES." and/to ".$strTrustName['name'].".";


$pdf->ezSetY(835);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg80,14,array('justification'=>'full','left'=>'120','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->ezSetY(770);

$pdf->ezSetY(813);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg81,8,array('justification'=>'center','left'=>'0','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->ezSetY(770);

//$pdf->selectFont(LIB_PATH.'/fonts/gothic.afm');

$pdf->setColor($discr,$discg,$discb);
$pdf->addText(355+$offsetH,723+$offsetV,30,"Disclosures",0,0);
$pdf->setColor($fr,$fg,$fb);
$pdf->addText(512+$offsetH,723+$offsetV,30,CERT_DBS,0,0);
$pdf->setColor(0,0,0);

//$pdf->ezSetY(760);

$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($lr,$lg,$lb);
//$pdf->addText(140+$offsetH,695+$offsetV,12,"Employers Copy",0,0);
$pdf->addText(355+$offsetH,695+$offsetV,15,CERT_DISC_LEVEL.":",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(470+$offsetH,695+$offsetV,15,$disclosuretype,0,0);
    $pdf->addText(355+$offsetH,683+$offsetV,10,"Page 2 of 2",0,0);



//$msg91=CERT_DISC_CHECK.":";
$msg911="This ADL ".CERT_DOCU." is not a criminal conviction certificate issued by the ".CERT_DISCLOSURES." (\"".CERT_DBS."\"). The information contained within this ADL ".CERT_DOCU." is issued directly by the ".CERT_DBS." to Atlantic Data Limited (\"ADL\") electronically.";

$pdf->ezSetY(785);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg91,8,array('justification'=>'full','left'=>'40','right'=>'30'));
$pdf->setColor(0,0,0);


$pdf->ezSetY(765);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg911,8,array('justification'=>'full','left'=>'40','right'=>'260'));
$pdf->setColor(0,0,0);


$msg92="Use of Information:";
$msg922="The information contained in this ADL ".CERT_DOCU." is confidential and all recipients have a duty to keep it secure and protect it from loss or unauthorised access. This ADL ".CERT_DOCU." must only be used in accordance with the ".CERT_DBS." Code of Practice and any other guidance issued from time to time by the ".CERT_DBS.". The ".CERT_DBS." will monitor compliance of ADL with this Code of Practice and other guidance.\n\nThis ADL ".CERT_DOCU." is issued in accordance with Part V of the Police Act 1997. Part V creates a number of offences including forgery or alteration of a criminal conviction certificate, obtaining a criminal conviction certificate under false pretences, allowing a criminal conviction certificate to be used by another person in a way which suggests it relates to that other person and using a criminal conviction certificate issued to another person as if it was one's own. Any person who commits an offence under Part V is liable to 6 months imprisonment and/or a fine not exceeding level 5 on the standard scale; currently ". $pound."5,000.00.\n\nThis ADL ".CERT_DOCU." is not evidence of the identity of the bearer, nor does it establish a person's right to work in the United Kingdom.\n\nIf you are not authorised by the Applicant or the Employer to review the contents of this ADL ".CERT_DOCU." in line with the ".CERT_DBS."' Code of Practice you should contact ADL on the details provided below.";

$pdf->ezSetY(700);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg92,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);


$pdf->ezSetY(680);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg922,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);

$msg93="Content:";
$msg933="The personal details contained in this ADL ".CERT_DOCU." are those supplied by or on behalf of the individual to whom this ADL ".CERT_DOCU." relates, at the time the application was made. This information was sent to the ".CERT_DBS." in order for them to carry out all appropriate checks.\n\nThe information contained in this ADL ".CERT_DOCU." is derived from police records and from records of those who are unsuitable to work with ".CERT_VG_C.", where indicated. The police records are those held on the Police National Computer (PNC) that contains details of Convictions, Cautions, Reprimands and Warnings in England and Wales. Most of the relevant convictions in Scotland and Northern Ireland may be included. The ".CERT_DBS." reserves the right to add new data sources from which it searches. For the most up to date list of data sources which are searched by the ".CERT_DBS." please visit the ".CERT_DBS." website";

$pdf->ezSetY(545);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg93,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);


$pdf->ezSetY(525);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg933,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);



$msg94="Accuracy:";
$msg944="The ".CERT_DBS." and ADL are not responsible for the accuracy of police records or records of those who are deemed unsuitable to work with ".CERT_VG_C.".\n\nTo verify the content of this ADL ".CERT_DOCU." you can log on to your online account at ".WEB_URL.".\n\nADL do not issue documents, including certificates, containing information which relates to an individual's criminal record (\"Positive Information\"). Certificates containing Positive Information are only issued by the ".CERT_DBS.".\n\nIf the individual to whom this ADL ".CERT_DOCU." relates is aware of any inaccuracy in the information contained in the ADL ".CERT_DOCU.", they should contact the ADL Countersignatory named on the first page of this ADL ".CERT_DOCU." immediately. This ADL Countersignatory will advise on how to dispute that information and, if requested, arrange for it to be referred to the ".CERT_DBS." on their behalf. The information should be disputed within 3 months of the date of issue of this ADL ".CERT_DOCU.".\n\nIf the matter is referred to the ".CERT_DBS.", the ".CERT_DBS." will seek to resolve the matter with the source of the record and the individual to whom this ADL ".CERT_DOCU." relates. In some circumstances this may only be possible using fingerprints, for which consent of the individual to whom the ADL ".CERT_DOCU." relates will be required.\n\nIf the ".CERT_DBS." upholds the dispute a new ADL ".CERT_DOCU." may be issued free-of-charge. Details of the ".CERT_DBS."' disputes and complaints procedure can be found on the ".CERT_DBS."' website.";
$pdf->ezSetY(440);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg94,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);


$pdf->ezSetY(420);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg944,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);


$msg95="Contact the ".CERT_DBS.":";

$pdf->ezSetY(240);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg95,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);



$pdf->addText(40+$offsetH,180+$offsetV,8,"Post:",0,0);
$pdf->setStrokeColor(0,0,0);
$pdf->addText(80+$offsetH,180+$offsetV,8,CERT_DISCLOSURES.", PO Box 165, Liverpool, L69 3JD",0,0);
$pdf->addText(40+$offsetH,165+$offsetV,8,"Telephone:",0,0);
$pdf->addText(40+$offsetH,155+$offsetV,8,"Disputes Line:",0,0);
$pdf->addText(130+$offsetH,155+$offsetV,8,"03000 200 190",0,0);
$pdf->addText(40+$offsetH,145+$offsetV,8,"Welsh Line:",0,0);
$pdf->addText(130+$offsetH,145+$offsetV,8,"03000 200 191",0,0);
$pdf->addText(40+$offsetH,135+$offsetV,8,"Minicom:",0,0);
$pdf->addText(130+$offsetH,135+$offsetV,8,"03000 200 192",0,0);
$pdf->addText(40+$offsetH,125+$offsetV,8,"General Information:",0,0);
$pdf->addText(130+$offsetH,125+$offsetV,8,"03000 200 190",0,0);


$msg95ADL = "Contact ".$lang['disclosureServiceAddressone'].":";

$pdf->ezSetY(240);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg95ADL,8,array('left'=>'full','left'=>'320','right'=>'20'));
$pdf->setColor(0,0,0);

$rb_address=$lang['disclosureServiceAddressone'].", ".$lang["disclosureServiceAddresstwo"];
if(!empty($lang["disclosureServiceAddressthree"])){
    $rb_address.=", ".$lang["disclosureServiceAddressthree"].", ".$lang["disclosureServiceAddressfour"];
}else{
    $rb_address.=", ".$lang["disclosureServiceAddressfour"];
}

$pdf->addText(320+$offsetH,180+$offsetV,8,"Post:",0,0);
$pdf->setStrokeColor(0,0,0);
$pdf->ezSetY(227);
$pdf->ezText($rb_address,8,array('left'=>'full','left'=>'340','right'=>'20'));
$pdf->addText(320+$offsetH,145+$offsetV,8,"Telephone:",0,0);
$pdf->addText(360+$offsetH,145+$offsetV,8," ".$lang["phoneNo"],0,0);
$pdf->addText(320+$offsetH,135+$offsetV,8,"Fax:",0,0);
$pdf->addText(360+$offsetH,135+$offsetV,8," ".$lang["fax"],0,0);


//$pdf->addText(320+$offsetH,135+$offsetV,8,"Web:",0,0);
//$pdf->addText(360+$offsetH,135+$offsetV,8,"www.disclosuresdbs.co.uk",0,0);
//$pdf->addText(320+$offsetH,125+$offsetV,8,"Email:",0,0);
//$pdf->addText(360+$offsetH,125+$offsetV,8,"info@disclosuresdbs.co.uk ",0,0);

//$msg955="If you find this Disclosure and are not able to return it to the person to whom it relates, please return it to the CRB at the address above or hand it in at the nearest police station";
//
//$pdf->ezSetY(160);
//$pdf->selectFont(LIB_PATH.'/fonts/arial.afm');
//$pdf->setColor(0,0,0);
//$pdf->ezText($msg955,8,array('left'=>'full','left'=>'40','right'=>'20'));
//$pdf->setColor(0,0,0);

#################################################################################################



?>
