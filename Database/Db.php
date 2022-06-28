<?php
namespace Database;

class Db {
    private static $instance = NULL;
    static $PDO;

	public function __construct()
	{
	  global $dbName, $user, $pass;
	  $PDO = new \PDO('mysql:host=localhost;dbname='.$dbName, $user, $pass);
      self::$PDO = $PDO;
	}

	public static function getInstance()
	{
	  if(is_null(self::$instance)){
	    self::$instance = new Db;
	  }
	  return self::$instance;
	}
}
