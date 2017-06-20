<?php


/**
 * This is a singleton class for connecting a mysql - database
 *
 *
 * @author Kaveh Raji <kr@vitabytes.de>
 */

class MySQL
{
	/*
	 * The variable which will contain the unique instance
	* @var string
	*/
	private static $unique; //The unique instannce of this object
	/*
	* The variable which contains the mysql - host
	* @var string
	*/
	private static $mysql_host;//The mysql - host
	/*
	* The variable which contains the mysql - user
	* @var string
	*/
	private static $mysql_user;//The mysql - user
	/*
	* The variable which contains the passwd of the mysql - database
	* @var string
	*/
	private static $mysql_passwd;//The mysql - password
	/*
	* The variable which contains the db
	* @var string
	*/
	private static $mysql_db;//The database name

	/*
	* This is the database link needed to connect to the database
	* @var db_connect
	*/
	private static $mysql_dbLink;//The needed db_link

	/*
	* This is the mysqli instance for escaping the queries
	*/
	private static $mysqli;//The mysqli instance for escaping the strings

	/*
	* The protected constructor which actually has the  effect to fill the class variables with the constants defined
	* to determine the database connection
	*/
	protected function __construct()
	{
	self::$mysql_host=MYSQL_HOST;
	self::$mysql_user=MYSQL_USER;
	self::$mysql_passwd=MYSQL_PASSWD;
	self::$mysql_db=MYSQL_DB;
	self::$mysql_dbLink=mysql_connect(self::$mysql_host,self::$mysql_user,self::$mysql_passwd);
	self::$mysqli=new mysqli(self::$mysql_host,self::$mysql_user,self::$mysql_passwd,self::$mysql_db);
	mysql_select_db(self::$mysql_db,self::$mysql_dbLink);
	self::select("set names 'utf8';");
	}

	/*
	* Also the __clone() - function is prohibited so the class cant be cloned
	*/
	private final function __clone()
	{
	//No effect needed
	}

	/*
	* This function getInstance will deliver a unique instance of this class
	* @return object the mysql - mapper object
	*/
	public static function getInstance() {
	if (self::$unique === NULL) {
	self::$unique = new MySQL;
	}
		return self::$unique;
	}

	/*
		* This is the select function which delivers a recordset matching the constraints defined in the query - param
		* @return recordset
		* @param string The query string
		*/
		public static function select($query)
		{
		$result=self::$mysqli->query($query);
		return($result);
}

		/*
		* This is the insert function which inserts a dataset defined by query and returns the inserted id
		* @return int The id of the inserted row
		* @param query The query which inherits the data to insert. this is obviously a valid sql - string
		*/
		public static function insert($query)
		{
		 $result=mysql_query($query,self::$mysql_dbLink);
		 return(mysql_insert_id());
        }


		/*
		* This is for escaping a string with mysql_connection
		*/
		public static function escape($obj)
		{
		 
		return(self::$mysqli->real_escape_string($obj));
}


}

?>