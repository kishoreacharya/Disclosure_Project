<?
function  getmynum()
{
//	$filename = "/usr/local/carddetails.txt";
	$filename = "/home/sites/disclosures.co.uk/private/carddetails.txt";
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	return $contents;
}
?>