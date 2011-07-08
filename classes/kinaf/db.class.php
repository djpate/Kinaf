<?
namespace kinaf;
/* this is nothing else but a PDO wrapper */
class Db {
    
    private $pdoInstance;
   
    private static $instance;
    public $db;
 
    private function __construct() {

        $conf = Configuration::get();

        $this->pdoInstance = new \PDO($conf['pdo']['driver'].':host='.$conf['pdo']['host'].';dbname='.$conf['pdo']['dbname'],$conf['pdo']['user'],$conf['pdo']['pass']);
        $this->pdoInstance->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION); 
        $this->pdoInstance->exec("set names 'utf8'");
        $this->db = $conf['pdo']['dbname'];
        
    }
   
    private function __clone() {}
   
    public static function singleton() {
        
        if (!isset(self::$instance)) {
                $c = __CLASS__;
                self::$instance = new $c;
        }
        
        return self::$instance;
    
    }
    
    /* pdo functions */
    
    public function quote($str){
        return $this->pdoInstance->quote($str);
    }
    
    public function lastInsertId(){
        return $this->pdoInstance->lastInsertId();
    }
    
    public function query($str){
        try {
            $q = $this->pdoInstance->query($str);
            return $q;
        } catch (\PDOException $e) {
            throw new Exception("Error : \n".$str."\n". $e->getMessage() . "\n".$e->getTraceAsString());
        }
    }
    
    public function exec($str){
        try {
            return $this->pdoInstance->exec($str);
        } catch (\PDOException $e) {
            throw new Exception("Error : \n".$str."\n". $e->getMessage() . "\n".$e->getTraceAsString());
        }
    }
    
    public function prepare($statement, $options = array){
        return $this->pdoInstance->prepare($statement,$options);
    }
   
}
?>
