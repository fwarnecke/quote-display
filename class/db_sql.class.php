<?php


class DB_SQL
{
    private $configFile = ROOT_DIR .'/db_acc.cfg';

    private $host;
    private $user;
    private $pw;
    private $name;

    static public $db_obj = null;
    static private $object;

    /**
     * instanciates database-object and tries to connect to database
     * 
     * @throws Exception if connection to database fails
     */
    private function __construct() {
        $this->getCredentials();

        $connection = 'mysql:dbname=' .$this->name .';host=' .$this->host;
        try {
            self::$db_obj = new PDO( $connection, $this->user, $this->pw);
        } catch ( PDOException $e) {
            throw new Exception( 'There was an error while establishing connection to the database. ', 1, $e->getMessage());
        }
    }

    public static function getInstance() {
        if ( self::$db_obj === null) {
            self::$object = new DB_SQL;
        }

        return self::$db_obj;
    }

    /**
     * reads DB-access-information from file defined in @see DB_SQL::$configFile
     * @throws Exception if file not found or missing neccessary information
     */
    private function getCredentials() {
        if ( file_exists( $this->configFile)) {
            $file = file_get_contents( $this->configFile);
            if ( preg_match('/host: *"(\S*)"/', $file, $matches)) {
                $this->host = $matches[1];
            } else {
                throw new Exception( 'Please enter hostname in config file', 2);
            }
            if ( preg_match('/user: *"(\S*)"/', $file, $matches)) {
                $this->user = $matches[1];
            } else {
                throw new Exception( 'Please enter username in config file', 2);
            }
            if ( preg_match('/password: *"(\S*)"/', $file, $matches)) {
                $this->pw = $matches[1];
            } else {
                throw new Exception( 'Please enter password in config file', 2);
            }
            if ( preg_match('/dbname: *"(\S*)"/', $file, $matches)) {
                $this->name = $matches[1];
            } else {
                throw new Exception( 'Please enter database name in config file', 2);
            }
        } else {
            throw new Exception( 'Config file not found.', 3);
        }
    }
    
    public final function __clone() {}
}
