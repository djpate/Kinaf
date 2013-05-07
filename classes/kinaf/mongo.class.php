<?
namespace kinaf;

class Mongo extends Singleton {
	
	private $mgInstance;
    public $collection;
 
    protected function __construct() {
		$conf = Configuration::get();
		$this->mgInstance = new \MongoClient($conf['mongo']['host'].':'.$conf['mongo']['port']);
        $this->mongo = $this->mgInstance->selectDB($conf['mongo']['dbname']);
	}

    public function collection() {
    	return $this->collection;
    }

    public function setCollection($nom){
		$this->collection = $this->mgInstance->$nom;
	}

	public function getConnexion(){
		return $this->mongo->{$this->collection};
	}

	public function getDb() {
		return $this->mongo;
	}

	public function getClient() {
		return $this->mgInstance;
	}

	public function save($params, $options = null) {
		if(is_object($params)) {
			$params = $this->cleanData($params, $params->orm->getFields());
		} else {
			exit;
		}

		try {
			 $this->mongo->{$this->collection}->insert($params, $options);
			 return $params->_id;
		}
		catch(\MongoCursorException $e) {
			//l'enregistrement existe déjà
			$this->mongo->{$this->collection}->save($params, $options);
		}
		
	}

	public function cleanData($object, $fields) {
		$results = new \stdClass();
		foreach($fields as $key=>$val){
			if(is_object($val)) {
				$this->cleanData($val, $val->orm->getFields());
			} else {
				$results->$val = $object->$val;
			}
		}
		return $results;
	}

	public function cleanArrayData($array) {
		$results = array();
		foreach($array as $o) {
			array_push($results, $this->cleanData($o, $o->orm->getFields()));
		}
		return $results;
	}
   
}
?>
