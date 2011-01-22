<?php
	class admin extends modele {
		protected static $table = "admin";
		
		public function isConnected(){
			return $this->id != 0;
		}
		
		public function logIn($l,$p){
			$ret = false;
			$q = $this->pdo->query("select id from ".self::$table." where login = ".$this->pdo->quote($l)." and password = '".hash('sha512',$p)."'");
			if($q->rowCount()==1){
				$res = $q->fetch();
				$ret = true;
				$_SESSION['adminid'] = $res['id'];
				$this->__construct($res['id']);
			}
			return $ret;
		}
		
		public function logout(){
			$_SESSION = array();
			$this->id = 0;
		}
		
		public function __toString(){
			return $this->prenom." ".$this->nom;
		}
		
	}
?>
