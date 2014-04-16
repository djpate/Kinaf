<?php
namespace kinaf;
/* this is nothing else but a PDO wrapper */
class Db extends Singleton {
    
    private $pdoInstance;
    public $db;
 
    protected function __construct() {

        $conf = Configuration::get();

        $this->pdoInstance = new \PDO($conf['pdo']['driver'].':host='.$conf['pdo']['host'].';dbname='.$conf['pdo']['dbname'],$conf['pdo']['user'],$conf['pdo']['pass']);
        $this->pdoInstance->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION); 
        $this->pdoInstance->exec("set names 'utf8'");
        $this->db = $conf['pdo']['dbname'];
        
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
            throw new \Exception("Error : \n".$str."\n". $e->getMessage() . "\n".$e->getTraceAsString());
        }
    }
    
    public function exec($str){
        try {
            return $this->pdoInstance->exec($str);
        } catch (\PDOException $e) {
            throw new \Exception("Error : \n".$str."\n". $e->getMessage() . "\n".$e->getTraceAsString());
        }
    }
    
    public function prepare($query, $options = array() ){
        return $this->pdoInstance->prepare($query,$options);
    }
   
}
?>
