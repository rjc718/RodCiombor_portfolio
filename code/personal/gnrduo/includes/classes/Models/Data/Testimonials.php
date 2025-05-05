<?php
	class TestimonialData
	{
		protected $db;

		private const GET_ALL_TESTIMONIALS = <<<SQL
			SELECT * 
			FROM testimonials 
			WHERE status = 1 
			ORDER BY date_created 
			DESC;
		SQL;
        
		private const GET_ACTIVE_FOOTER_TESTIMONIALS = <<<SQL
			SELECT * 
			FROM testimonials 
			WHERE status = 1 
		SQL;

		private const GET_PAGE_TESTIMONIAL_MIX = <<<SQL
			SELECT t.text, t.customer_name, t.sub_text, t.sub_text2, 
			t.date_created, t.testimonial_img, t.location
			FROM testimonials_to_pages t2p
			LEFT JOIN testimonials t
			ON t2p.testimonial_id = t.testimonial_id
			WHERE t.status = 1 
			AND t2p.page_id = ?; 
		SQL;
				
		public function __construct(Database $db)
		{
			$this->db = $db;
		}
		public static function getInstance(
			?Database $db = null
		): TestimonialData
		{				
			if (empty($db)) {
				$db = Database::getInstance();
			}
			return new self($db);
		}
        public function getAll()
		{    
			$params = [
				'sql' => self::GET_ALL_TESTIMONIALS,
				'types' => '*'
			];
			return $this->db->getQueryData($params) ?? [];
        }

        public function getFooterTestimonial(int $page_id): array
        {
			$result = [];
            $data = $this->getPageTestimonialMix($page_id);

            if(sizeof($data) == 0){
                $result = $this->getRandomFooter();
            }
			else{
				$randomIndex = array_rand($data);
				$result = $data[$randomIndex];	
			}
            return $result;
        }

        private function getPageTestimonialMix(int $page_id): array
        {
			$data = [];
			$params = [
				'sql' => self::GET_PAGE_TESTIMONIAL_MIX,
				'types' => 'i'
			];
			$result = $this->db->getQueryData($params, [$page_id]);
			if($result){
				$data = $result;
			}
			return $data;
        }
        private function getRandomFooter(){
            
			$data = [];
            $result = [];
			
			$params = [
				'sql' => self::GET_ACTIVE_FOOTER_TESTIMONIALS,
				'types' => '*'
			];
			$data = $this->db->getQueryData($params) ?? [];
            if(sizeof($data) > 0){
                $randomIndex = array_rand($data);
                $result = $data[$randomIndex];
            }
			return $result;
        }
	}
?>