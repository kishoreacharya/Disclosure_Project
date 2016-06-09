<?php

define('OBJECT', 'OBJECT', true);
define('ARRAY_A', 'ARRAY_A', true);
define('ARRAY_N', 'ARRAY_N', true);

class Oracle {

    protected $trace = false;  // same as $debug_all
    protected $debug_all = false;  // same as $trace
    protected $debug_called = false;
    protected $protecteddump_called = false;
    protected $show_errors = true;
    protected $num_queries = 0;
    protected $last_query = null;
    protected $last_error = null;
    protected $col_info = null;
    protected $captured_errors = array();
    protected $dbuser = false;
    protected $dbpassword = false;
    protected $dbname = false;
    protected $dbhost = false;
    protected $cache_dir = false;
    protected $cache_queries = false;
    protected $cache_inserts = false;
    protected $use_disk_cache = false;
    protected $cache_timeout = 24;   // hours

    /**
     * Constructor - allow the user to perform a qucik connect at the
     * same time as initialising the class
     *
     * @param string $dbuser
     * @param string $dbpassword
     * @param string $dbname
     * @param string $dbhost
     * @return DB
     */
     function __construct(){
         $this->constructDB();
     }   

    function constructDB($dbuser = DB_USER, $dbpassword = DB_PASSWORD, $dbname = DB_DBNAME, $dbhost = DB_HOSTNAME) {
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->QuickConnect($dbuser, $dbpassword, $dbname, $dbhost);
        if (isset($_POST['x'])) {
            unset($_POST['x']);
        }
        if (isset($_POST['y'])) {
            unset($_POST['y']);
        }
    }

    function postoffice_db() {
        $this->dbuser = DB_POSTOFFICE_USER;
        $this->dbpassword = DB_POSTOFFICE_PASSWORD;
        $this->dbname = DB_POSTOFFICE_DBNAME;
        $this->dbhost = DB_POSTOFFICE_HOSTNAME;
        $this->QuickConnect_postoffice($this->dbuser, $this->dbpassword, $this->dbname, $this->dbhost);
    }

    /**
     * Short hand way to connect to mySQL database server
     * and select a mySQL database at the same time
     *
     * @param string $dbuser
     * @param string $dbpassword
     * @param string $dbname
     * @param string $dbhost
     * @return bool
     */
    function QuickConnect($dbuser = '', $dbpassword = '', $dbname = '', $dbhost = 'localhost') {
        $return_val = false;
        if (!$this->Connect($dbuser, $dbpassword, $dbhost, true))
            ;
        else if (!$this->Select($dbname))
            ;
        else {
            $return_val = true;
        }
        return $return_val;
    }
    
    function QuickConnect_postoffice($dbuser = '', $dbpassword = '', $dbname = '', $dbhost = 'localhost') {
        $return_val = false;
        if (!$this->Connect_postoffice($dbuser, $dbpassword, $dbhost, true))
            ;
        else if (!$this->Select($dbname))
            ;
        else {
            $return_val = true;
        }
        return $return_val;
    }

    /**
     * Try to connect to mySQL database server
     *
     * @param string $dbuser
     * @param string $dbpassword
     * @param string $dbhost
     * @return bool
     */
    function Connect($dbuser = '', $dbpassword = '', $dbhost = 'localhost') {
        $return_val = false;


        if (defined("DB_RESOURCE")) {
            $this->dbuser = DB_USER;
            $this->dbpassword = DB_PASSWORD;
            $this->dbname = DB_DATABASE;
            $this->dbhost = DB_HOST;
            $this->dbh = DB_RESOURCE;
            return true;
        }

        //echo "Database Connection <br>";
        // Must have a user and a password
        if (!$dbuser) {
            $this->RegisterError($this->GetError(1) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(1), E_USER_WARNING) : null;
        }
        // Try to establish the server database handle
        else if (!$this->dbh = mysql_connect($dbhost, $dbuser, $dbpassword, true)) {
            $this->RegisterError($this->GetError(2) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(2), E_USER_WARNING) : null;
        } else {
            $this->dbuser = $dbuser;
            $this->dbpassword = $dbpassword;
            $this->dbhost = $dbhost;
            $return_val = true;
            define("DB_RESOURCE", $this->dbh);
        }

        return $return_val;
    }

     function Connect_postoffice($dbuser = '', $dbpassword = '', $dbhost = 'localhost') {
        $return_val = false;


        if (defined("DB_RESOURCE_POSTOFFICE")) {
                $this->dbuser = DB_POSTOFFICE_USER;
                $this->dbpassword = DB_POSTOFFICE_PASSWORD;
                $this->dbname = DB_POSTOFFICE_DBNAME;
                $this->dbhost = DB_POSTOFFICE_HOSTNAME;
                $this->dbh = DB_RESOURCE_POSTOFFICE;
            return true;
        }

        //echo "Database Connection <br>";
        // Must have a user and a password
        if (!$dbuser) {
            $this->RegisterError($this->GetError(1) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(1), E_USER_WARNING) : null;
        }
        // Try to establish the server database handle
        else if (!$this->dbh = mysql_connect($dbhost, $dbuser, $dbpassword, true)) {
            $this->RegisterError($this->GetError(2) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(2), E_USER_WARNING) : null;
        } else {
            $this->dbuser = $dbuser;
            $this->dbpassword = $dbpassword;
            $this->dbhost = $dbhost;
            $return_val = true;
            define("DB_RESOURCE_POSTOFFICE", $this->dbh);
        }

        return $return_val;
    }

    /**
     * Try to select a mySQL database
     *
     * @param string $dbname
     * @return bool
     */
    function Select($dbname = '') {
        $return_val = false;

        // Must have a database name
        if (!$dbname) {
            $this->RegisterError($this->GetError(3) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(3), E_USER_WARNING) : null;
        }

        // Must have an active database connection
        else if (!$this->dbh) {
            $this->RegisterError($this->GetError(4) . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->GetError(4), E_USER_WARNING) : null;
        }

        // Try to connect to the database
        else if (!mysql_select_db($dbname, $this->dbh)) {
            // Try to get error supplied by mysql if not use our own
            if (!$str = mysql_error($this->dbh))
                $str = $this->GetError(5);

            $this->RegisterError($str . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($str, E_USER_WARNING) : null;
        }
        else {
            $this->dbname = $dbname;
            $return_val = true;
        }

        return $return_val;
    }

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no mater if magic quotes are on or not)
     *
     * @param string $str
     * @return string
     */
    function Escape($str) {
        //return $str;
        //return mysql_escape_string(stripslashes($str));
        //echo $str."<br>";
        //return stripslashes((stripslashes($str)));
        return $str;
    }

    /**
     * Return mySQL specific system date syntax
     *
     * @return string
     */
    function Sysdate() {
        return 'NOW()';
    }

    /**
     * Perform mySQL query and try to detirmin result value
     *
     * @param string $query
     * @return mixed
     */
    function Query($query) {
        // Initialise return
        $return_val = 0;

        // Flush cached values..
        $this->Flush();

        //Format a mySQL string correctly for safe mySQL insert
        $query = $this->Escape($query);

        // For reg expressions
        $query = trim($query);
        // Log how the function was called
        $this->func_call = "\$db->Query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Count how many queries there have been
        $this->num_queries++;

        // The would be cache file for this query
        $cache_file = $this->cache_dir . '/' . md5($query);

        // Try to get previously cached version
        if ($this->use_disk_cache && file_exists($cache_file)) {
            // Only use this cache file if less than 'cache_timeout' (hours)

            if ((time() - filemtime($cache_file)) > ($this->cache_timeout * 3600)) {
                unlink($cache_file);
            } else {
                $result_cache = unserialize(file_get_contents($cache_file));

                $this->col_info = $result_cache['col_info'];
                $this->last_result = $result_cache['last_result'];
                $this->num_rows = $result_cache['num_rows'];

                // If debug ALL queries
                $this->trace || $this->debug_all ? $this->Debug() : null;

                return $result_cache['return_value'];
            }
        }
        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->Connect($this->dbuser, $this->dbpassword, $this->dbhost);
            $this->Select($this->dbname);
        }

        // Perform the query via std mysql_query function..

        try {
            $this->result = mysql_query($query, $this->dbh);
            if (!$this->result) {
                throw new Exception('Invalid Query');
            }
        } catch (Exception $e) {
            /*             * ******************************************* */
            $filename = "log.txt";
            if (is_writable($filename)) {
                if ($handle = fopen($filename, 'a')) {
                    $data = $_SERVER['HTTP_REFERER'] . "\n";
                    $data .= $query . "\n";
                    if (fwrite($handle, $data) === FALSE) {
                        
                    }
                    fclose($handle);
                }
            }
            /*             * *********************************************** */
            //header("Location:".$this->queryErrorRedirectionPath);
            //echo $e->getMessage();
        }
        //echo "query: ".$query."<br>";
        // If there is an error then take note of it..
        if ($str = mysql_error($this->dbh)) {
            $is_insert = true;
            $this->RegisterError($str);
            $this->show_errors ? trigger_error($str . '<br><b>Query</b>: ' . $query . '<br>', E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        $is_insert = false;
        if (preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            /**/
            /* $this->result = mysql_query($query,$this->dbh);
              echo "Query: ".$query."<br>";
              echo "Rows Affected: ".mysql_affected_rows()."<br>"; */
            /**/
            $this->rows_affected = @mysql_affected_rows();

            // Take note of the insert_id
            if (preg_match("/^(insert|replace)\s+/i", $query)) {
                $this->insert_id = @mysql_insert_id($this->dbh);
            }

            // Return number fo rows affected
            $return_val = $this->rows_affected;
        }
        // Query was a select
        else {

            // Take note of column info
            $i = 0;
            while ($i < @mysql_num_fields($this->result)) {
                $this->col_info[$i] = @mysql_fetch_field($this->result);
                $i++;
            }

            // Store Query Results
            $num_rows = 0;
            while ($row = @mysql_fetch_object($this->result)) {
                // Store relults as an objects within main array
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @mysql_free_result($this->result);

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            //echo "num_rows: ".$num_rows."<br>";
            //  exit;
            $return_val = $this->num_rows;
        }

        // disk caching of queries
        if ($this->use_disk_cache && ( $this->cache_queries && !$is_insert ) || ( $this->cache_inserts && $is_insert )) {
            if (!is_dir($this->cache_dir)) {
                $this->RegisterError("Could not open cache dir: $this->cache_dir");
                $this->show_errors ? trigger_error("Could not open cache dir: $this->cache_dir", E_USER_WARNING) : null;
            } else {
                // Cache all result values
                $result_cache = array
                    (
                    'col_info' => $this->col_info,
                    'last_result' => $this->last_result,
                    'num_rows' => $this->num_rows,
                    'return_value' => $this->num_rows,
                );
                error_log(serialize($result_cache), 3, $cache_file);
            }
        }

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->Debug() : null;

        return $return_val;
    }

    /**
     * Register error class
     *
     * @param string $err_str
     */
    function RegisterError($err_str) {
        // Keep track of last error
        $this->last_error = $err_str;

        // Capture all errors to an error array no matter what happens
        $this->captured_errors[] = array
            (
            'error_str' => $err_str,
            'query' => $this->last_query
        );
    }

    /**
     * Returns custom error
     *
     * @param int $num
     * @return string
     */
    function GetError($num) {
        switch ($num) {
            case 1: return 'Require $dbuser and $dbpassword to connect to a database server';
                break;
            case 2: return 'Error establishing mySQL database connection. Correct user/password? Correct hostname? Database server running?';
                break;
            case 3: return 'Require $dbname to select a database';
                break;
            case 4: return 'mySQL database connection is not active';
                break;
            case 5: return 'Unexpected error while trying to select database';
                break;
        }
    }

    /**
     * To set mode to show error
     *
     */
    function ShowErrors() {
        $this->show_errors = true;
    }

    /**
     * To hide errors
     *
     */
    function HideErrors() {
        $this->show_errors = false;
    }

    /**
     *  Kill cached query results
     *
     */
    function Flush() {
        // Get rid of these
        $this->last_result = null;
        $this->col_info = null;
        $this->last_query = null;
        $this->from_disk_cache = false;
    }

    /**
     * Get one variable from the DB
     *
     * @param string $query
     * @param int $x
     * @param int $y
     * @return mixed
     */
    function GetVar($query = null, $x = 0, $y = 0) {

        // Log how the function was called
        $this->func_call = "\$db->GetVar(\"$query\",$x,$y)";

        // If there is a query then perform it if not then use cached results..
        if ($query) {
            $this->Query($query);
        }

        // Extract var out of cached results based x,y vals
        if ($this->last_result[$y]) {
            $values = array_values(get_object_vars($this->last_result[$y]));
        }

        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x] !== '') ? $values[$x] : null;
    }

    /**
     * Get one row from the DB
     *
     * @param string $query
     * @param mode $output
     * @param int $y
     * @return mixed
     */
    function GetRow($query = null, $output = OBJECT, $y = 0) {

        // Log how the function was called
        $this->func_call = "\$db->GetRow(\"$query\",$output,$y)";

        // If there is a query then perform it if not then use cached results..
        if ($query) {
            $this->query($query);
        }

        // If the output is an object then return object using the row offset..
        if ($output == OBJECT) {
            return $this->last_result[$y] ? $this->last_result[$y] : null;
        }
        // If the output is an associative array then return row as such..
        elseif ($output == ARRAY_A) {
            return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : null;
        }
        // If the output is an numerical array then return row as such..
        elseif ($output == ARRAY_N) {
            return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : null;
        }
        // If invalid output type was specified..
        else {
            $this->print_error(" \$db->GetRow(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
        }
    }

    /**
     * Function to get 1 column from the cached result set based in X index
     *
     * @param string $query
     * @param int $x
     * @return mixed
     */
    function GetCol($query = null, $x = 0) {

        // If there is a query then perform it if not then use cached results..
        if ($query) {
            $this->Query($query);
        }

        // Extract the column values
        for ($i = 0; $i < count($this->last_result); $i++) {
            $new_array[$i] = $this->GetVar(null, $x, $i);
        }

        return $new_array;
    }

    /**
     * Return the the query as a result set
     *
     * @param string $query
     * @param mode $output
     * @return mixed
     */
    function GetResults($query = null, $output = OBJECT) {

        // Log how the function was called
        $this->func_call = "\$db->GetResults(\"$query\", $output)";

        // If there is a query then perform it if not then use cached results..
        if ($query) {
            $this->Query($query);
        }

        // Send back array of objects. Each row is an object
        if ($output == OBJECT) {
            return $this->last_result;
        } elseif ($output == ARRAY_A || $output == ARRAY_N) {
            if ($this->last_result) {
                $i = 0;
                foreach ($this->last_result as $row) {

                    $new_array[$i] = get_object_vars($row);
                    if ($output == ARRAY_N) {
                        $new_array[$i] = stripslashes(array_values($new_array[$i]));
                    }

                    $i++;
                }

                return $new_array;
            } else {
                return null;
            }
        }
    }

    /**
     * Function to get column meta data info pertaining to the last query
     *
     * @param string $info_type
     * @param int $col_offset
     * @return mixed
     */
    function GetColInfo($info_type = "name", $col_offset = -1) {

        if ($this->col_info) {
            if ($col_offset == -1) {
                $i = 0;
                foreach ($this->col_info as $col) {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            } else {
                return $this->col_info[$col_offset]->{$info_type};
            }
        }
    }

    /**
     * Dumps the contents of any input variable to screen in a nicely
     * formatted and easy to understand way - any type: Object, Var or Array
     *
     * @param mixed $mixed
     */
    function VarDump($mixed = '') {

        echo "<p><table><tr><td bgcolor=ffffff><blockquote><font color=000090>";
        echo "<pre><font face=arial>";

        if (!$this->vardump_called) {
            echo "<font color=800080><b>Variable Dump..</b></font>\n\n";
        }

        $var_type = gettype($mixed);
        print_r(($mixed ? $mixed : "<font color=red>No Value / False</font>"));
        echo "\n\n<b>Type:</b> " . ucfirst($var_type) . "\n";
        echo "<b>Last Query</b> [$this->num_queries]<b>:</b> " . ($this->last_query ? $this->last_query : "NULL") . "\n";
        echo "<b>Last Function Call:</b> " . ($this->func_call ? $this->func_call : "None") . "\n";
        echo "<b>Last Rows Returned:</b> " . count($this->last_result) . "\n";
        echo "</font></pre></font></blockquote></td></tr></table>";
        echo "\n<hr size=1 noshade color=dddddd>";

        $this->vardump_called = true;
    }

    /**
     * Dumps the contents of any input variable to screen in a nicely
     * formatted and easy to understand way - any type: Object, Var or Array
     *
     * @param mixed $mixed
     */
    function DumpVar($mixed) {
        $this->VarDump($mixed);
    }

    /**
     * Displays the last query string that was sent to the database & a
     * table listing results (if there were any).
     * (abstracted into a seperate file to save server overhead).
     *
     */
    function Debug() {

        echo "<blockquote>";

        // Only show credits once..
        if (!$this->debug_called) {
            echo "<font color=800080 face=arial size=2><b>Debug..</b></font><p>\n";
        }

        if ($this->last_error) {
            echo "<font face=arial size=2 color=000099><b>Last Error --</b> [<font color=000000><b>$this->last_error</b></font>]<p>";
        }

        if ($this->from_disk_cache) {
            echo "<font face=arial size=2 color=000099><b>Results retrieved from disk cache</b></font><p>";
        }


        echo "<font face=arial size=2 color=000099><b>Query</b> [$this->num_queries] <b>--</b> ";
        echo "[<font color=000000><b>$this->last_query</b></font>]</font><p>";

        echo "<font face=arial size=2 color=000099><b>Query Result..</b></font>";
        echo "<blockquote>";

        if ($this->col_info) {

            // =====================================================
            // Results top rows

            echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
            echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";


            for ($i = 0; $i < count($this->col_info); $i++) {
                echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]->type} {$this->col_info[$i]->max_length}</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]->name}</span></td>";
            }

            echo "</tr>";

            // ======================================================
            // print main results

            if ($this->last_result) {

                $i = 0;
                foreach ($this->GetResults(null, ARRAY_N) as $one_row) {
                    $i++;
                    echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";

                    foreach ($one_row as $item) {
                        echo "<td nowrap><font face=arial size=2>$item</font></td>";
                    }

                    echo "</tr>";
                }
            } // if last result
            else {
                echo "<tr bgcolor=ffffff><td colspan=" . (count($this->col_info) + 1) . "><font face=arial size=2>No Results</font></td></tr>";
            }

            echo "</table>";
        } // if col_info
        else {
            echo "<font face=arial size=2>No Results</font>";
        }

        echo "</blockquote></blockquote><hr noshade color=dddddd size=1>";


        $this->debug_called = true;
    }

    /**
     * To execute an insert query
     *
     * @param string $tableName
     * @param mixed $fieldArray
     * @return integer
     */
    function Insert($tableName, $fieldArray) {

        $str = "INSERT INTO `$tableName` SET ";
        if (is_array($fieldArray)) {
            foreach ($fieldArray as $field => $value) {
                if (preg_match("/(DATE_FORMAT|CURRENT_TIMESTAMP|STR_TO_DATE)/i", $value)) {
                    $str .= "`$field` = " . $value . ",";
                } else {
                    $str .= "`$field` = \"" . $value . "\",";
                }
            }
            $str = substr($str, 0, -1);
            if (!get_magic_quotes_gpc()) {
                $str = ($str);
            }
            $this->Query($str);
            return $this->insert_id;
        } else {
            return false;
        }
    }

    /**
     * To execute an insert query
     *
     * @param string $tableName
     * @param mixed $fieldArray
     * @param string $condition
     * @return integer
     */
    function Update($tableName, $fieldArray, $condition = "") {
        $str = "UPDATE `$tableName` SET ";
        if (is_array($fieldArray)) {
            foreach ($fieldArray as $field => $value) {
                if (preg_match("/(DATE_FORMAT|CURRENT_TIMESTAMP|STR_TO_DATE)/i", $value)) {
                    $str .= "`$field` = " . $value . ",";
                } else {
                    $str .= "`$field` = '" . addslashes($value) . "',";
                }
            }
            $str = substr($str, 0, -1);
            if ($condition) {
                $str .= " WHERE " . $condition;
            }
            /*
              if (!get_magic_quotes_gpc())
              {
              //$str = addslashes($str);
              }
              echo $str;exit; */
            $affected = $this->Query($str); //echo "affected ".$affected; exit;
            //echo "affected: ".$affected."<br>";
            return $affected;
        } else {
            return false;
        }
    }

    /* Function delete record
     *
     * @param1		:	$table			: table name
     * @param2		:	$condition		: condition for query
     *
     * return 		:	true/false
     * description	:	Handles record deletion
     * */

    function Delete($table, $condition) {
        $sqlDelete = "DELETE FROM $table WHERE $condition";
        if ($this->Query($sqlDelete)) {
            return true;
        } else {
            return false;
        }
    }

    /*  function to get DB records
     *  appended a new function same as get result in the Disclosure function
     *
     */

    function getDBRecords($sqlSelect) {
        return $this->GetResults($sqlSelect, "ARRAY_A");
    }

}

?>
