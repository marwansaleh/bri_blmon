<?php
require_once("db_config.php");

class DatabaseConnection 
{
	private $db_host;
	private $db_user;
	private $db_password;
	private $db_name;
	private $error_message;
        private $last_id;
	
	private $numrecord=0;
	
	public $connection;  //database connection id ($link_id)
	public $sql;
	
	function __construct ($host=DB_HOST, $user=DB_USER, $pwd=DB_PWD, $dbname=DB_NAME) 
	{
		$this->db_host=$host;
		$this->db_user=$user;
		$this->db_password=$pwd;
		$this->db_name=$dbname;		
			
		//create connection to database
		//print "database connection connecting...<br />";
		$this->connect();
	}
	public function connect()
	{
		//create connection and store into class properties		
		$this->connection = @mysql_connect($this->db_host,$this->db_user,$this->db_password);
                if (!$this->connection)
                {
                    print "Maaf. Aplikasi tidak dapat dilanjutkan karena tidak bisa melakukan koneksi database";
                    exit;
                }
		
                if (!mysql_select_db($this->db_name, $this->connection))
                {
                    print "Maaf. Aplikasi tidak dapat dilanjutkan karena tidak bisa menggunakan database yang dituju";
                    exit;
                }
	}
	public function close() 
	{
		//print "database connection closing...<br />";
		if ($this->connection)
			mysql_close($this->connection);
		//print "database connection closed<br />";
	}
	public function execSQL($sql="")
	{
            //$sql = strip_tags($sql);
		if ($sql){
			$this->sql = $sql;
		}
		if (!$this->sql){
			return false;	
		} else {
			$result = mysql_query($sql, $this->connection);
			if ($result)
			{
				$this->numrecord = mysql_affected_rows($this->connection);
                                $this->last_id = mysql_insert_id($this->connection);
				$res_array = array();
				while ($r = mysql_fetch_array($result, MYSQL_ASSOC)){
					$res_array [] = $r;
				}
				mysql_free_result($result);
				
				return $res_array;
			} else {
				$this->error_message = mysql_error($this->connection);
				return false;
			}
		}
	}
        function fetch_obj($sql){
            $result = mysql_query($sql, $this->connection);
            if ($result)
            {
                    $this->numrecord = mysql_affected_rows($this->connection);
                    $this->last_id = mysql_insert_id($this->connection);
                    $res_array = array();
                    while ($r = mysql_fetch_object($result)){
                            $res_array [] = $r;
                    }
                    mysql_free_result($result);

                    return $res_array;
            } else {
                    $this->error_message = mysql_error($this->connection);
                    return false;
            }
        }
	public function getNumRecord() { return $this->numrecord; }
        public function getLastId() { return $this->last_id;}
	public function updateField($table,$field,$new_value,$condition="")
	{
		$where_clause = "";
		if ($condition!="")
			$where_clause = "WHERE ".$condition;
		$sql = "UPDATE $table SET $field='$new_value' $where_clause";
		return mysql_query($sql,$this->connection);
	}
	public function query($sql)
	{
            //$sql = strip_tags($sql);
		if ($sql<>'')
		{
			$result = mysql_query($sql, $this->connection);
			if ($result)
			{
                            $this->last_id = mysql_insert_id($this->connection);
                            $this->numrecord = mysql_affected_rows($this->connection);
                            return $result;
			}
			else
			{
				$this->error_message = mysql_error($this->connection);
				return false;
			}
		}
		else
			return false;
	}
	public function getQueryData($sql)
	{
		$result = $this->query($sql);
		
		$data = array();
		
		if ($result){			
			while ($r = mysql_fetch_array($result, MYSQL_ASSOC)){
				$data [] = $r;
			}
			mysql_free_result($result);
		}
		return $data;
	}
	public function singleValueFromQuery($sql)
	{
		$result = $this->query($sql);
		if ($result)
			return mysql_result($result,0);
		else
		{
			$this->error_message = mysql_error($this->connection);
			return false;
		}
	}
	public function getLastError() { return $this->error_message;}
}
?>