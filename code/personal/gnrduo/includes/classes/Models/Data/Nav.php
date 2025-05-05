<?php
	class NavData
	{
		protected $db;
		
		private const GET_NAV_LINKS = <<<SQL
			SELECT * FROM nav_links WHERE nav_status = 1
		SQL;
        private const GET_NAV_SUB_LINKS = <<<SQL
			SELECT * FROM nav_sublinks WHERE nav_status = 1
		SQL;
				
		public function __construct(Database $db)
		{
			$this->db = $db;
		}
		public static function getInstance(
			?Database $db = null
		): NavData
		{				
			if (empty($db)) {
				$db = Database::getInstance();
			}
			return new self($db);
		}
        public function getNavData(): array
		{
			return $this->groupNavMenus(
				$this->getNavLinks(), 
				$this->getNavSubLinks()
			) ?? [];
        }
		private function groupNavMenus(
			array $links, 
			array $sublinks
		): array
		{
			for($i=0; $i<sizeof($links); $i++){
				$links[$i]['sublinks'] = [];
			}
			for($i=0; $i<sizeof($links); $i++){
				foreach($sublinks as $sublink){
					if($links[$i]['nav_id'] == $sublink['nav_group_id']){
						array_push($links[$i]['sublinks'], $sublink);
					}
				}
			}
			return $links ?? [];
		}
        private function getNavLinks(): array
		{		
			$params = [
                'sql' => self::GET_NAV_LINKS,
                'types' => '*'
            ];
			return $this->db->getQueryData(
                $params
            ) ?? [];
        }
        private function getNavSubLinks(): array
		{
			$params = [
                'sql' => self::GET_NAV_SUB_LINKS,
                'types' => '*'
            ];
			return $this->db->getQueryData(
                $params
            ) ?? [];
        }
	}
?>