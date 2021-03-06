<?php

class sql {

    var $host = "";
    var $user = "";
    var $pass = "";
    var $db = "";
    var $attempt = null;
    var $take;
    var $transaction;
    var $cachestatus = false;
    var $cachetime = 60;
    var $cachefolder = "cachex";

    function __construct($host, $user, $pass, $db, $warn = "", $transaction = false) {

        $this->attempt = new mysqli($host, $user, $pass, $db);

        if (mysqli_connect_errno()) {
            exit($warn);
        }

        if ($transaction == true) {
            $this->attempt->query("SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE");
            $this->attempt->query('START TRANSACTION');
            $this->attempt->autocommit(FALSE);
        }

        $this->attempt->query("set names utf8");
    }

    function set_cachefolder($folder) {
        $this->cachefolder = $folder;
    }

    function get_cachefolder() {
        return $this->cachefolder;
    }

    function set_cachestatus($swt) {
        $this->cachestatus = $swt;
    }

    function get_cachestatus() {
        return $this->cachestatus;
    }

    function set_cachetime($second) {
        $this->cachetime = $second * 60;
    }

    function get_cachetime() {
        return $this->cachetime;
    }

    function qry($sql) {
        $this->attempt->query($sql);
    }

    function select($table, $cells, $query2, $warn = "", $debug = false) {

        if ($debug == true) {
            $this->debug("select $cells from $table $query2");
        }

        $take = $this->attempt->query("select $cells from $table $query2");

        if (!$take) {
            $this->error_log("select", $this->attempt->error);
            return $returnedvar = array(false, $warn);
        }
        else {
            return $take;
        }
    }

    function read($result, $ifjoin = false) {

        if (!$result) {
            $this->error_log("read", $this->attempt->error);
            return $returnedvar = array(false, $warn);
        }
        else {
            if ($ifjoin == false) {
                $readz = $result->fetch_assoc();
                return $returnedvar = array(true, $readz);
            }
            else {
                $readtemp = $result->fetch_assoc();
                $joininfo = $result->fetch_fields();
                foreach ($joininfo as $val) {
                    $readz[$val->table][$val->name] = $readtemp[$val->name];
                }
                return $returnedvar = array(true, $readz);
            }
        }
    }

    function readall($result, $ifjoin = false) {
        $readallz = array();

        if (!$result) {
            $this->error_log("readall", $this->attempt->error);
            return $returnedvar = array(false);
        }
        else {

            if ($ifjoin == false) {
                while ($reads = $result->fetch_assoc()) {
                    $readallz[] = $reads;
                }
                return $returnedvar = array(true, $readallz);
            }
            else {
                $joininfo = $result->fetch_fields();
                while ($reads = $result->fetch_assoc()) {

                    foreach ($joininfo as $val) {
                        $readz[$val->table][$val->name] = $reads[$val->name];
                    }

                    $readallz[] = $readz;
                }
                return $returnedvar = array(true, $readallz);
            }
        }
    }

    function insert($table, $val, $query2 = "", $warn = "", $debug = false) {

        $cells = "";
        $values = "";

        foreach ($val as $k => $v) {
            $cells.="$k,";
            if ($v != "null") {
                $values.="'$v',";
            }
            else {
                $values.="null,";
            }
        }

        $cells = substr($cells, 0, -1);
        $values = substr($values, 0, -1);

        if ($debug == true) {
            $this->debug("INSERT INTO $table ($cells) values ($values) $query2");
        }
        else {
            $insert = $this->attempt->query("INSERT INTO $table ($cells) values ($values) $query2");
            if (!$insert) {
                $this->error_log("insert", $this->attempt->error);
                return $returnedvar = array(false, $warn);
            }
            else {
                return $returnedvar = array(true);
            }
        }
    }

    function update($table, $val, $query2 = "", $warn = "", $debug = false) {

        $cellvalues = "";

        foreach ($val as $k => $v) {

            if ($v != "null") {
                $cellvalues.="$k='$v',";
            }
            else {
                $cellvalues.="$k=null,";
            }
        }


        $cellvalues = substr($cellvalues, 0, -1);

        if ($debug == false) {
            $update = $this->attempt->query("update $table set $cellvalues $query2");
        }
        else {
            $this->debug("update $table set $cellvalues $query2");
        }

        if (!$update) {
            $this->error_log("update", $this->attempt->error);
            return $returnedvar = array(false, $warn);
        }
        else {
            return $returnedvar = array(true);
        }
    }

    function delete($table, $query2, $warn = "", $debug = false) {

        if ($debug == false) {
            $delete = $this->attempt->query("delete from $table $query2");
        }
        else {
            $this->debug("delete from $table $query2");
        }

        if (!$delete) {
            $this->error_log("delete", $this->attempt->error);
            return $returnedvar = array(false, $warn);
        }
        else {
            return $returnedvar = array(true);
        }
    }

    function error_log($type, $sql) {
        $d = time();
        $exp = addslashes($sql);
        $path = addslashes($_SERVER["REQUEST_URI"]);
        $ip = $_SERVER["REMOTE_ADDR"];
        $this->attempt->query("INSERT INTO sql_error (processdate,ip,processtype,errorsql,filepath) values ('$d','$ip','$type','$exp','$path')");
    }

    function get_cache($table, $cells, $query2, $warn = "", $debug = false, $single = "read", $join = false, $filenameseperate = "", $timex = false) {

        if ($debug === true) {
            $this->debug("select $cells from $table $query2");
        }

        $queryz = "select $cells from $table $query2";
        $md5 = md5($queryz);
        $cachefile = $this->cachefolder . "/db-$filenameseperate-$md5.html";

        $cachetime = ($timex == false) ? $this->get_cachetime() : $timex * 60;

        if ($this->get_cachestatus() && file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
            return unserialize(file_get_contents($cachefile));
        }
        else {
            $newquery = $this->attempt->query($queryz);
            if ($single == "read") {
                $newreads = $this->read($newquery, $join);
            }
            else if ($single == "readall") {
                $newreads = $this->readall($newquery, $join);
            }

            $fp = fopen($cachefile, 'w');
            fwrite($fp, serialize($newreads));
            fclose($fp);
            return $newreads;
        }
    }

    function debug($sql) {
        echo "<div style='display:none' class='debug'>$sql</div>";
    }

    function close() {
        $this->attempt->close();
    }

    function rollback() {
        $this->attempt->rollback();
    }

    function commit() {
        $this->attempt->commit();
    }

    function free($freesql) {
        mysqli_free_result($freesql);
    }

    function last_id() {
        return $this->attempt->insert_id;
    }

    function rows_count($quer) {
        return $quer->num_rows;
    }

    function fetch_rows($quer) {
        return $quer->fetch_row();
    }

}

?>
