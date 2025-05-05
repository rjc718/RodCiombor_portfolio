<?php
 
	class Database
	{
		private static $instance; 

		public static function getInstance(): Database
		{
			if(!defined('DB_HOST_NAME')){
				define('DB_HOST_NAME','mysql:host=PLACEHOLDER; dbname=PLACEHOLDER');
			}
			if(!defined('DB_USER')){
				define('DB_USER','PLACEHOLDER');
			}
			if(!defined('DB_PW')){
				define('DB_PW', 'PLACEHOLDER');
			}
					
			if (empty(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		private function bindSqlParams($userData, $types, $stmt){
		
			$typeList = explode(',', $types);
			
			for($i=0; $i<sizeof($userData); $i++){
				if($i < sizeof($typeList)){
					if($typeList[$i] == 'i'){
						$stmt->bindParam($i + 1, $userData[$i], PDO::PARAM_INT);
					}
					elseif($typeList[$i] == 's'){
						$stmt->bindParam($i + 1, $userData[$i], PDO::PARAM_STR);
					}
					elseif($typeList[$i] == 'b'){
						$stmt->bindParam($i + 1, $userData[$i], PDO::PARAM_BOOL);
					}
					elseif($typeList[$i] == 'l'){
						$stmt->bindParam($i + 1, $userData[$i], PDO::PARAM_LOB);
					}
				}
			}	
		}
		
		public function getQueryData($sqlParams, $userData = [])
		{
			try{
				$con = new PDO(DB_HOST_NAME, DB_USER, DB_PW);
				$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				if(!empty($sqlParams['sql']) && !empty($sqlParams['types'])){
					
					$stmt = $con->prepare($sqlParams['sql']);
					if($stmt){
						$this->bindSqlParams($userData, $sqlParams['types'], $stmt);
						$stmt->execute();
						$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
						return $result;
					}
				}
			}
			catch(PDOException $e){
				echo 'ERROR1: ' . $e->getMessage(); 
			}
		}
		public function getQueryResult($sqlParams, $userData = [])
		{
			try{
				$con = new PDO(DB_HOST_NAME, DB_USER, DB_PW);
				$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				if(!empty($sqlParams['sql']) && !empty($sqlParams['types'])){
					$stmt = $con->prepare($sqlParams['sql']);
					if($stmt){
						$this->bindSqlParams($userData, $sqlParams['types'], $stmt);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						return $result;
					}
				}
			}
			catch(PDOException $e){
				echo 'ERROR2: ' . $_SERVER['PHP_SELF'] . $e->getMessage(); 
			}
		}
	}

?>