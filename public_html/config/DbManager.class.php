<?php

class DbManager
{
	public static function getConnection()
	{
		$db = NULL;
		try 
		{
//$dbarray = array('host'=>'localhost', 'username'=>'tamaki', 'password'=>'tamaki123', 'dbname'=>'pprun');
#			$db = Zend_Db::factory('Pdo_Pgsql', array(	'host'=>'localhost',
#														'username'=>'tamaki',
#														'password'=>'tamaki123',
#														'dbname'=>'pprun'));
#			$db = Zend_Db::factory('Pdo_Pgsql', $dbarray);
//global $config['dbhost'];
//var_dump($config);
      $dbinfo = array('host'=>DB_HOST,
                      'username'=>DB_USER,
                      'password'=>DB_PASSWORD,
                      'dbname'=>DB_NAME);
			$db = Zend_Db::factory('Pdo_Pgsql', $dbinfo);
#			$db = Zend_Db::factory('Pdo_Pgsql', array(	'host'=>'localhost',
#														'username'=>'postgres',
#														'password'=>'',
#														'dbname'=>'pprun'));
			$db->query('SET CLIENT_ENCODING TO UTF8');
		}
		catch(Zend_Exception $e)
		{
			echo "DB Connection Error";
			return false;
			//die($e->getMessage());
		}

		return $db;
	}
}

