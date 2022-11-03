<?php
class Database
{
    // Define database connection parameters
   // private static $hn      = 'localhost';
    private static $hn      = '107.6.54.113:3306';
    private static $un      = 'medidore_root';
    private static $pwd     = 'Qwerty4321';
    private static $db      = 'medidore_ioTwater';
    //private static $db      = 'medidore_SQuilichao';
    private static $cs      = 'utf8';
        
    private static $cont  = null;

    // Set up the PDO parameters
    private static $opt  = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );

    public function __construct() {
        die('Init function is not allowed');
    }
     
    public static function connect()
    {
       // One connection through whole application
       if ( null == self::$cont )
       {     
        try
        {
            //self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword); 
            self::$cont = new PDO("mysql:host=" . self::$hn . ";port=3306;dbname=" . self::$db . ";charset=" . self::$cs, self::$un, self::$pwd, self::$opt);  
        }
        catch(PDOException $e)
        {
          die($e->getMessage()); 
        }
       }
       return self::$cont;
    }
     
    public static function disconnect()
    {
        self::$cont = null;
    }
}
?>
