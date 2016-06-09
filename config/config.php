<?php
ini_set("short_open_tag","off");
ob_start();

/* Read settings from config.ini file. */
$iniFile = parse_ini_file( 'config.ini', true ); 

//Path Declaration
define("APP_PATH", $iniFile["PHP"]["APP_PATH"]);
define("SMARTY_PATH", $iniFile["PHP"]["SMARTY_PATH"]);
define("AUTO_COMPLETE", ($iniFile["PHP"]["AUTO_COMPLETE"] != 0)?'ON':'OFF');
DEFINE("SERVERSIDE_VALIDATION", isset($iniFile["PHP"]["SERVERSIDE_VALIDATION"])?$iniFile["PHP"]["SERVERSIDE_VALIDATION"]:0);
define("MODEL_PATH", APP_PATH."model/");
define("VIEW_PATH", APP_PATH."view/");
define("CONTROLLER_PATH", APP_PATH."controller/");
define("LANGUAGE_PATH", APP_PATH."languages/");
define("CLASS_PATH", MODEL_PATH."classes/");
define("IMAGE_PATH", "view/images/");
define("FONT_PATH", APP_PATH."fonts/");
define("IB_PATH", APP_PATH."barcode/Initiating_Barcode/");
define("IDB_PATH", APP_PATH."barcode/ID_Barcode/");
define("PB_PATH", APP_PATH."barcode/Personal_Barcode/");
define("JS_PATH", "view/js/");
define("CSS_PATH", "view/css/");
define("LIBS_PATH", APP_PATH."libs/");
define("COMMON_EDITOR_PATH", "view/");
define("WEB_URL", $iniFile["PHP"]["WEB_URL"]);

//  DB Declaration
define("DB_USER", $iniFile["DB"]["DB_USER"]);
define("DB_PASSWORD", $iniFile["DB"]["DB_PASSWORD"]);
define("DB_DBNAME", $iniFile["DB"]["DB_DBNAME"]);
define("DB_BRAND", $iniFile["DB"]["DB_BRAND"]);
define("DB_HOSTNAME", $iniFile["DB"]["DB_HOSTNAME"]);
define("DB_PORT",$iniFile["DB"]["DB_PORT"]);
define("DB_PROTOCOL",$iniFile["DB"]["DB_PROTOCOL"]);
//POSTOFFICE  DB Declaration
define("DB_POSTOFFICE_USER", $iniFile["POSTOFFICE"]["DB_POSTOFFICE_USER"]);
define("DB_POSTOFFICE_PASSWORD", $iniFile["POSTOFFICE"]["DB_POSTOFFICE_PASSWORD"]);
define("DB_POSTOFFICE_DBNAME", $iniFile["POSTOFFICE"]["DB_POSTOFFICE_DBNAME"]);
define("DB_POSTOFFICE_HOSTNAME", $iniFile["POSTOFFICE"]["DB_POSTOFFICE_HOSTNAME"]);

define("CENTRAL_DB_ID", 86);
//  mail declaration
define("MAIL_SERVER", $iniFile["MAIL"]["MAIL_SERVER"]);
define("MAIL_TYPE", $iniFile["MAIL"]["MAIL_TYPE"]);
define("MAIL_PORT", $iniFile["MAIL"]["MAIL_PORT"]);
define("MAIL_AUTH", $iniFile["MAIL"]["MAIL_AUTH"]);
define("MAIL_USER", $iniFile["MAIL"]["MAIL_USER"]);
define("MAIL_PASS", $iniFile["MAIL"]["MAIL_PASS"]);

define("ROUTE_TWO_AGREED", "N");
define("externalIdCheckBy", "A");
define("WORKS_WITH_OPTION", "Y");
define("VERIFY_CERTIFICATE", "B");
define("DBS_MISSING_INFO_OPTION", "Y");

//  title and basic settings
define('PAGE_TITLE','Care Quality Commission (CQC) - Disclosure Service');

include_once( MODEL_PATH."adodb/session/adodb-session2.php" );

ini_set("session.save_handler","user");

ADOdb_Session::config("mysql", DB_HOSTNAME, DB_USER, DB_PASSWORD, DB_DBNAME);
session_start();

?>
