<?php
	class PageInfo
	{
		protected $dbo;
				
		public const GET_PAGE_INFO_BY_SLUG = <<<SQL
			SELECT * FROM pages WHERE page_slug = ? 
		SQL;
		public const GET_ALL_PAGE_LINKS = <<<SQL
			SELECT page_id, page_title, page_slug FROM pages; 
		SQL;
		public const GET_VIEW_NAME_BY_ID = <<<SQL
			SELECT view_name FROM views WHERE view_id = ? 
		SQL;
		public const GET_MODEL_NAME_BY_SLUG = <<<SQL
			SELECT m.model_name FROM models m LEFT JOIN pages p on m.model_id = p.model_id WHERE p.page_slug = ? 
		SQL;
		public const GET_MODEL_NAME_BY_ID = <<<SQL
			SELECT model_name FROM models WHERE model_id = ? 
		SQL;
		public const GET_SCRIPTS_BY_ID = <<<SQL
			SELECT src FROM page_scripts WHERE page_id = ? 
		SQL;
		public const GET_CATEGORY_STYLES = <<<SQL
			SELECT href FROM page_styles WHERE page_category = ? 
		SQL;
		public const GET_WIDGETS_BY_ID = <<<SQL
			SELECT wt.name, wp.widget_id, wp.widget_type FROM widgets_to_pages wp LEFT JOIN widget_types wt ON wt.id = wp.widget_type WHERE wp.page_id = ?; 
		SQL;
		
		public function __construct(Database $dbo)
		{
			$this->dbo = $dbo;
		}

		public function getPageInfo(string $pageKey): array
		{
			$data = [];
			
			$params['sql'] = self::GET_PAGE_INFO_BY_SLUG;
			$params['types'] = 's';
			
			$data = $this->dbo->getQueryResult($params, [$pageKey]);
			if(empty($data)){
				$data = $this->dbo->getQueryResult($params, ['404']);
			}

			$data['page_key'] = $pageKey;

			$data['view_name'] = 'article';
			if($data['view_id'] > 0){
				$data['view_name'] = $this->getViewNameById($data['view_id']);
			}

			$data['model_name'] =  $this->getModelNameById($data['model_id']);
	
			return $data;
		}

		public function getPageLinkList(): array
		{
			$output = [];
			$params['sql'] = self::GET_ALL_PAGE_LINKS;
			$params['types'] = '*';
			
			$data = $this->dbo->getQueryData($params, []);
			foreach($data as $d){
				$output[$d['page_id']] = [
					'title' => $d['page_title'] ?? '', 
					'href' => $d['page_slug'] ?? ''
				];
			}
			return $output;
		}
		
		private function getViewNameById(int $viewId): string
		{
			$data = [];
						
			$params['sql'] = self::GET_VIEW_NAME_BY_ID;
			$params['types'] = 'i';
			
			$data = $this->dbo->getQueryResult($params, [$viewId]);
			
			return $data['view_name'] ?? 'article';
		}
		
		private function getModelNameById(int $modelId): string
		{
			$data = [];
						
			$params['sql'] = self::GET_MODEL_NAME_BY_ID;

			$params['types'] = 'i';
			
			$data = $this->dbo->getQueryResult($params, [$modelId]);
			
			return $data['model_name'] ?? '';
		}
		
		public function getModelNameBySlug(string $slug): string
		{
			$data = [];
						
			$params['sql'] = self::GET_MODEL_NAME_BY_SLUG;
			$params['types'] = 's';
			
			$data = $this->dbo->getQueryResult($params, [$slug]);
			
			return $data['model_name'] ?? '';
		}
		
		public function getPageCss(int $pageCategory): array
		{
			$params['sql'] = self::GET_CATEGORY_STYLES;
			$params['types'] = 'i';

			$data = [];
			$data = $this->dbo->getQueryData($params, [$pageCategory]);
			return $data;
		}
		
		public function getPageJS(int $pageId): array
		{
			$params['sql'] = self::GET_SCRIPTS_BY_ID;
			$params['types'] = 'i';

			$data = [];
			$data = $this->dbo->getQueryData($params, [$pageId]);

			return $data;
		}
		
		public function getWidgetsByPageId(int $pageId): array
		{
			$params['sql'] = self::GET_WIDGETS_BY_ID;
			$params['types'] = 'i';

			$data = [];
			$data = $this->dbo->getQueryData($params, [$pageId]);

			return $data;
		}
	}
?>