<?php
define (__TRACE_ENABLED__, false);
define (__DEBUG_ENABLED__, false);

require("class.barcode.php");
require("class.i25object.php");
require("class.c39object.php");
require("class.c128aobject.php");
require("class.c128bobject.php");
require("class.c128cobject.php");
ini_set("memory_limit","500M");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author Chitha Harsha K
 */
class Cgenbarcode {
//put your code here
  
     
    function genBarcode($barcode,$outputFormate,$type,$width,$height,$xres,$font,$barcodeName)
    {
        $output   = $outputFormate;
        $confid="100";
        $timedet=time();
        
        //print $barcode."<br>";

        $code=$barcode;
        $type     = $type;
        $width    = $width;
        $height   = $height;
        $xres     = $xres;
        $font     = $font;


        if (isset($barcode) && strlen($barcode)>0) {
          $style  = BCS_ALIGN_CENTER;
          $style |= ($output  == "png") ? BCS_IMAGE_PNG  : 0;
          $style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0;
          $style |= ($border  == "on") ? BCS_BORDER 	  : 0;
          $style |= ($drawtext <> "on") ? BCS_DRAW_TEXT  : 0;
          $style |= ($stretchtext=="on") ? BCS_STRETCH_TEXT  : 0;
          $style |= ($negative== "on") ? BCS_REVERSE_COLOR  : 0;

          switch ($type)
          {
            case "I25":
                                  $obj = new I25Object($width, $height, $style, $barcode);
                                  break;
            case "C39":
                                  $obj = new C39Object($width, $height, $style, $barcode);
                                  break;
            case "C128A":
                                  $obj = new C128AObject($width, $height, $style, $barcode);
                                  break;
            case "C128B":
                                  $obj = new C128BObject($width, $height, $style, $barcode);
                                  break;
            case "C128C":
                                  $obj = new C128CObject($width, $height, $style, $barcode);
                                  break;
                default:
                                  $obj = false;
          }

        $barcodesrc="../pdf/barcode/$barcodeName";
        $obj->SetPath($barcodesrc);
        $obj->SetFont($font);
        $obj->DrawObject($xres);
        $obj->FlushObject();
        $obj->DestroyObject();
        }
    }
}
?>
