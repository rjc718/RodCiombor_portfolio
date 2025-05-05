<?php
	class Configuration
	{
		protected $db;
		
		private const GET_ALL_SITE_CONFIGS = <<<SQL
			SELECT config_key, config_value FROM configuration 
		SQL;
		private const GET_CONFIG_VALUE = <<<SQL
			SELECT config_value FROM configuration WHERE config_key = ?
		SQL;
		private const GET_ALL_PAGE_SLUGS = <<<SQL
			SELECT page_slug FROM pages
		SQL;

		public function __construct(Database $db)
		{
			$this->db = $db;
		}
		public static function getInstance(
			?Database $db = null
		): Configuration
		{				
			if (empty($db)) {
				$db = Database::getInstance();
			}
			return new self($db);
		}
		public function getConfigValue(string $key): string
		{
			$params = [
                'sql' => self::GET_CONFIG_VALUE,
                'types' => 's'
            ];
			$data = $this->db->getQueryResult(
				$params, 
				[$key]
			) ?? [];
			return $data['config_value'] ?? '';
		}
		public function getSiteConfigs(): array
		{
			$params = [
                'sql' => self::GET_ALL_SITE_CONFIGS,
                'types' => '*'
            ];
			$configs = $this->db->getQueryData($params) ?? [];
			for ($i=0; $i < sizeof($configs); $i++){
				$data[$configs[$i]['config_key']] = $configs[$i]['config_value'];
			}
			return $data ?? [];
		}
		public function defineConfigs(): void
		{
			$configs = $this->getSiteConfigs();
			for ($i=0; $i < sizeof($configs); $i++){
				define(
					$configs[$i]['config_key'], 
					$configs[$i]['config_value']
				);
			}
		}
		public function getAllPageSlugs(): array
		{	
			$params = [
                'sql' => self::GET_ALL_PAGE_SLUGS,
                'types' => '*'
            ];
			return $this->db->getQueryData($params) ?? [];
		}

		public function isDev(): bool
		{
			$result = $this->getConfigValue('IS_DEV');
			return $result == 'true' ? true : false;
		}
	}
?>