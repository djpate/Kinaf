<?php
	
	namespace Kinaf\Cache;

	class cache{
		
		public static function add($key,$value){
				return apc_store($key,serialize($value));
		}
		
		public static function fetch($key){
			return unserialize(apc_fetch($key));
		}
		
		public static function exists($key){
			return apc_exists($key);
		}
		
		public static function delete($key){
			return apc_delete($key);
		}
		
	}
	
?>
