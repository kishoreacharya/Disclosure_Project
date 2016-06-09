<?php

$pdf->ezSetY(770);
//$image="images/access.jpg";
$image=$this->urlImgPath."background.jpg";
//$imagebottom="../images/flag.JPG";
$pdf->addJpegFromFile($image,0,130,600);
//$pdf->addJpegFromFile($imagebottom,0,120,100);

$pdf->setColor($fr,$fg,$fb);
$pdf->filledRectangle(0,800,600,45);
$pdf->setColor(0,0,0);

$msg80="<b>STRICTLY PRIVATE AND CONFIDENTIAL</b>\n";
$msg81="This document is representative of Disclosure information provided by the Criminal Records Bureau and issued by Atlantic Data Ltd";

$pdf->ezSetY(835);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg80,19,array('justification'=>'full','left'=>'120','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->ezSetY(770);

$pdf->ezSetY(813);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($whiter,$whiteg,$whiteb);
$pdf->ezText($msg81,8,array('justification'=>'full','left'=>'80','right'=>'50'));
$pdf->setColor(0,0,0);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->ezSetY(770);

//$pdf->selectFont(LIB_PATH.'/fonts/gothic.afm');

$pdf->setColor($discr,$discg,$discb);
$pdf->addText(355+$offsetH,723+$offsetV,30,"Disclosures",0,0);
$pdf->setColor($fr,$fg,$fb);
$pdf->addText(508+$offsetH,723+$offsetV,30,"CRB",0,0);
$pdf->setColor(0,0,0);

//$pdf->ezSetY(760);

$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($lr,$lg,$lb);
$pdf->addText(355+$offsetH,695+$offsetV,15,"Disclosure Level:",0,0);
$pdf->setColor(0,0,0);
$pdf->addText(470+$offsetH,695+$offsetV,15,$disclosuretype,0,0);
$pdf->addText(355+$offsetH,683+$offsetV,10,"Page 2 of 2",0,0);



$msg91="Enhanced Disclosures:";
$msg911="This ADL Disclosure Document is not a criminal conviction certificate issued by the Criminal Records Bureau (\"CRB\"). This ADL Disclosure Document is representative of an Enhanced Criminal Records Certificate within the meaning of sections 113B and 116 of the Police Act 1997. The information contained within this ADL Disclosure Document is issued directly by the CRB to Atlantic Data Limited (\"ADL\") electronically.";

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

$pound = html_entity_decode('&pound;');

$msg92="Use of Disclosure Information:";
$msg922="The information contained in this ADL Disclosure Document is confidential and all recipients have a duty to keep it secure and protect it from loss or unauthorised access. This ADL Disclosure Document must only be used in accordance with the CRB Code of Practice and any other guidance issued from time to time by the CRB. The CRB will monitor compliance of ADL with this Code of Practice and other guidance.\n\nThis ADL Disclosure Document is issued in accordance with Part V of the Police Act 1997. Part V creates a number of offences including forgery or alteration of a criminal conviction certificate, obtaining a criminal conviction certificate under false pretences, allowing a criminal conviction certificate to be used by another person in a way which suggests it relates to that other person and using a criminal conviction certificate issued to another person as if it was one's own. Any person who commits an offence under Part V is liable to 6 months imprisonment and/or a fine not exceeding level 5 on the standard scale; currently ". $pound."5,000.00.\n\nThis ADL Disclosure Document is not evidence of the identity of the bearer, nor does it establish a person's right to work in the United Kingdom.\n\nIf you are not authorised by the Applicant or the Employer to review the contents of this ADL Disclosure Document in line with the CRB's Code of Practice you should contact ADL on the details provided below.";

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

$msg93="Disclosure Content:";
$msg933="The personal details contained in this ADL Disclosure Document are those supplied by or on behalf of the individual to whom this ADL Disclosure Document relates, at the time the application was made.\n\nThe information contained in this ADL Disclosure Document is derived from police records and from records of those who are unsuitable to work with children and/or adults, where indicated. The police records are those held on the Police National Computer (PNC) that contains details of Convictions, Cautions, Reprimands and Warnings in England and Wales. Most of the relevant convictions in Scotland and Northern Ireland may be included. The CRB reserves the right to add new data sources from which it searches. For the most up to date list of data sources which are searched by the CRB please visit the CRB website";

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



$msg94="Disclosure Accuracy:";
$msg944="The CRB and ADL are not responsible for the accuracy of police records or records of those who are deemed unsuitable to work with children and/or adults.\n\nTo verify the content of this ADL Disclosure Document you can log on to your online account at ".$arrLangData['urlLink'].".\n\nADL do not issue documents, including certificates, containing information which relates to an individual's criminal record (\"Positive Information\"). Certificates containing Positive Information are only issued by the CRB.\n\nIf the individual to whom this ADL Disclosure Document relates is aware of any inaccuracy in the information contained in the ADL Disclosure Document, they should contact the ADL Countersignatory named on the first page of this ADL Disclosure Document immediately. This ADL Countersignatory will advise on how to dispute that information and, if requested, arrange for it to be referred to the CRB on their behalf. The information should be disputed within 3 months of the date of issue of this ADL Disclosure Document.\n\nIf the matter is referred to the CRB, the CRB will seek to resolve the matter with the source of the record and the individual to whom this ADL Disclosure Document relates. In some circumstances this may only be possible using fingerprints, for which consent of the individual to whom the ADL Disclosure Document relates will be required.\n\nIf the CRB upholds the dispute a new ADL Disclosure Document may be issued free-of-charge. Details of the CRB's disputes and complaints procedure can be found on the CRB's website.";

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


$msg95="Contact the CRB:";

$pdf->ezSetY(240);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg95,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);



$pdf->addText(40+$offsetH,170+$offsetV,8,"Post:",0,0);
$pdf->setStrokeColor(0,0,0);
$pdf->addText(80+$offsetH,170+$offsetV,8,"Criminal Records Bureau, PO Box 165, Liverpool, L69 3JD",0,0);
$pdf->addText(40+$offsetH,150+$offsetV,8,"Telephone:",0,0);
$pdf->addText(40+$offsetH,140+$offsetV,8,"Disputes Line:",0,0);
$pdf->addText(130+$offsetH,140+$offsetV,8,"03000 200 190",0,0);
$pdf->addText(40+$offsetH,130+$offsetV,8,"Welsh Line:",0,0);
$pdf->addText(130+$offsetH,130+$offsetV,8,"03000 200 191",0,0);
$pdf->addText(40+$offsetH,120+$offsetV,8,"Minicom:",0,0);
$pdf->addText(130+$offsetH,120+$offsetV,8,"03000 200 192",0,0);
$pdf->addText(40+$offsetH,110+$offsetV,8,"General Information:",0,0);
$pdf->addText(130+$offsetH,110+$offsetV,8,"03000 200 190",0,0);


$msg95ADL = "Contact ADL:";

$pdf->ezSetY(240);
$pdf->selectFont(FONT_PATH.'arial.afm');
$pdf->setColor($otherr,$otherg,$otherb);
$pdf->ezText($msg95ADL,8,array('left'=>'full','left'=>'320','right'=>'20'));
$pdf->setColor(0,0,0);


$pdf->addText(320+$offsetH,170+$offsetV,8,"Post:",0,0);
$pdf->setStrokeColor(0,0,0);
$pdf->addText(345+$offsetH,170+$offsetV,8," Atlantic Data Ltd, PO BOX 6060, Milton Keynes, Bucks, MK1 9BW",0,0);
$pdf->addText(320+$offsetH,150+$offsetV,8,"Telephone:",0,0);
$pdf->addText(360+$offsetH,150+$offsetV,8," 03333 207 307",0,0);
$pdf->addText(320+$offsetH,140+$offsetV,8,"Fax:",0,0);
$pdf->addText(360+$offsetH,140+$offsetV,8,"03333 207 326",0,0);

$pdf->addText(320+$offsetH,120+$offsetV,8,"Web:",0,0);
$pdf->addText(360+$offsetH,120+$offsetV,8,"www.disclosurescrb.co.uk",0,0);
$pdf->addText(320+$offsetH,110+$offsetV,8,"Email:",0,0);
$pdf->addText(360+$offsetH,110+$offsetV,8,"info@disclosurescrb.co.uk",0,0);

/*$pdf->addText(40+$offsetH,150+$offsetV,8,"Web:",0,0);
$pdf->addText(80+$offsetH,150+$offsetV,8,"www.crb.gov.uk",0,0);
$pdf->addText(40+$offsetH,140+$offsetV,8,"Email:",0,0);
$pdf->addText(80+$offsetH,140+$offsetV,8,"info@crb.gsi.gov.uk",0,0);*/


/*$msg955="If you find this Disclosure and are not able to return it to the person to whom it relates, please return it to the CRB at the address above or hand it in at the nearest police station";

$pdf->ezSetY(90);
$pdf->selectFont(LIB_PATH.'/fonts/arial.afm');
$pdf->setColor(0,0,0);
$pdf->ezText($msg955,8,array('left'=>'full','left'=>'40','right'=>'20'));
$pdf->setColor(0,0,0);*/

#################################################################################################



?>