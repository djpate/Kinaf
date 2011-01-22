<?php
	class pageadmin extends modele {
		protected static $table = "pageadmin";
		
		public function run(){
			
			if(!isset($_REQUEST['page'])||empty($_REQUEST['page'])){
				$this->rewriteUrl = "main";
			} else {
				$this->rewriteUrl = $_REQUEST['page'];
			}
			
			$this->admin = new admin($_SESSION['adminid']);
			
			$pq = $this->pdo->query("select id from ".static::$table." where rewriteUrl = ".$this->pdo->quote($this->rewriteUrl)." and id in (select Pageadmin from pageadmin_droit where Level = '".$this->admin->get('Level')->get('id')."')");

			
			if($pq->rowCount()==1){
				$result = $pq->fetch();
				$this->id = $result['id'];
				$this->load();
				if($this->isParent()){
					$this->loadFirstChild(); // a parent cannot be displayed only a child can
				}
				$this->loadMainMenu();
				$this->loadSubMenu();
				$this->display();
			} else {
				//todo
			}
			
		}
		
		private function loadMainMenu(){
			$this->mainMenu = array();
			
			$q = $this->pdo->query("select pageTitle,rewriteUrl from ".static::$table." where parent = 0 and onMenu = 1 and id in (select Pageadmin from pageadmin_droit where Level = '".$this->admin->get('Level')->get('id')."') order by `order`");
			
			foreach($q as $row){
				array_push($this->mainMenu,array("rewriteUrl"=>$row['rewriteUrl'],"pageTitle"=>$row['pageTitle']));
			}
		}
		
		private function loadSubMenu(){
			$this->subMenu = array();
			$q = $this->pdo->query("select pageTitle,rewriteUrl from ".static::$table." where parent = ".$this->parent." and onMenu = 1 and id in (select Pageadmin from pageadmin_droit where Level = '".$this->admin->get('Level')->get('id')."') order by `order`");
			foreach($q as $row){
				array_push($this->subMenu,array("rewriteUrl"=>$row['rewriteUrl'],"pageTitle"=>$row['pageTitle']));
			}
		}
		
		private function loadFirstChild(){
			$pq = $this->pdo->query("select id from ".static::$table." where parent = ".$this->id." and onMenu = 1 and id in (select Pageadmin from pageadmin_droit where Level = '".$this->admin->get('Level')->get('id')."') order by `order` limit 1");
			$res = $pq->fetch();
			$this->id = $res['id'];
			$this->load();
		}
		
		private function isParent(){
			return ($this->parent==0);
		}
		
		private function getModule(){
			$pq = $this->pdo->query("select moduleTitle from ".static::$table." where id = ".$this->parent);
			$res = $pq->fetch();
			return $res['moduleTitle'];
		}
		
		private function getParentUrl(){
			$pq = $this->pdo->query("select rewriteUrl from ".static::$table." where id = ".$this->parent);
			$res = $pq->fetch();
			return $res['rewriteUrl'];
		}
		
		private function display(){
			/* loads controller */
			@include('../controllers/backEnd/'.$this->getModule().'/'.$this->file);
			
			/* loads view */
			if($this->isWrapped==1&&!isset($_REQUEST['noWrap'])){
				/* we load header & footer */
				@include('../views/backEnd/header.php');
				@include('../views/backEnd/'.$this->getModule().'/'.$this->file);
				@include('../views/backEnd/footer.php');
			} else {
				@include('../views/backEnd/'.$this->getModule().'/'.$this->file); // mainly for ajax & popups
			}
			
		}
		
		public function __toString(){
			if($this->isParent()){
				return ucfirst($this->moduleTitle);
			} else {
				return "&nbsp;&nbsp;&nbsp;".$this->pageTitle;
			}
		}
		
	}
?>
