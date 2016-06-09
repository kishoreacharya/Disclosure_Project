<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class Cbarcodegen {
//put your code here
   function __construct() {
       
   }
 //Function to create initiating transcation barcode
    
    function initiating_transcation_barcode($iin,$serviceCode,$numberOfIdDocScans,$useByDateForLetter,$CustomerRefNum,$CRBCode,$SupplierFee)
    {
        $barcode = $iin.$serviceCode.$numberOfIdDocScans.$useByDateForLetter.$CustomerRefNum.$CRBCode.$SupplierFee;
        
        $luhnNo= $this->cal_luhnNo($barcode);
        $barcode .= $luhnNo;
        
        return $barcode;
        
        
    }
    ////Function to create initiating transcation barcode end
    
    //Function to calculate luhn no
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
    //Function to calculate luhn no end
    
    //Function to create Personal Details Barcode
    function personal_Details_Barcode($scanSeq,$paddingCharacter,$custPostCode,$custDOB,$custInitials,$doorNumber)
    {
        $barcode = $scanSeq.$paddingCharacter.$custPostCode.$custDOB.$custInitials.$doorNumber;
        
        return $barcode;
    }
    
      //Function to create Personal Details Barcode end
    
    //Function to create ID Verification Barcode
    function idVerification_Barcode($scanSeq,$paddingCharacter,$docType,$docRef,$docDate)
    {
        $barcode = $scanSeq.$paddingCharacter.$docType.$docRef.$docDate;
        return $barcode;
    }
    //Function to create ID Verification Barcode end
    
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
        return $cost;
    }
   //Function to append zeros after decimal point end
}
?>
