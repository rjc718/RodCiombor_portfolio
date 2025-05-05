<?php
require 'includes/classes/Models/Data/Nav.php';
require 'includes/classes/Models/Data/Testimonials.php';
require 'includes/classes/Models/Data/Shows.php';
require 'includes/classes/Models/Data/Songs.php';

	class PageModel
	{
		protected $config;
		protected $view;
		protected $builder;
		private $page;
		private $db;	
		private $pageInfo;
			
		public function __construct(
			Database $db, 
			PhpTemplateView $view,
			Configuration $config, 
			PageInfo $page, 
			PageBuilder $builder,
			string $pageKey
		)
		{
			$this->db = $db;
			$this->view = $view;
			$this->config = $config;
			$this->page = $page;
			$this->builder = $builder;
			$this->pageInfo = $this->page->getPageInfo($pageKey);
			$this->cssLinks = [];
			$this->scripts = [];
		}
		public function getPageViewName(): string
		{
			return $this->pageInfo['view_name'] ?? ''; 
		}
		public function getPageId(): int
		{
			return $this->pageInfo['page_id'] ?? 0; 
		}
		public function getPageCategory(): int
		{
			return $this->pageInfo['page_category'] ?? 0; 
		}
		public function getPageTitle(): string
		{
			return $this->pageInfo['page_title'] ?? ''; 
		}
		public function getMainImgSrc(): string
		{
			return $this->pageInfo['main_img_src'] ?? ''; 
		}
		public function getSiteName(): string
		{
			return $this->config->getConfigValue('SITE_NAME') ?? ''; 
		}
		public function getPageLayoutParams(
			string $viewClasses, 
			array $extraData = []
		): array
		{
			return array_merge(
				[
                	'pageTitle' => $this->getPageTitle() ?? '',
                	'mainImgSrc' => $this->getMainImgSrc() ?? '',
                	'viewClasses' => $viewClasses ?? ''
            	], 
				$extraData
			);
		}
		public function getPageWidgetData(): array
		{	
			return $this->page->getWidgetsByPageId(
				$this->getPageId()
			) ?? [];
		}
		public function loadSectionViewParams(): array
		{				
			$data = $this->loadSectionData();
			return [
				'header'	=> $this->getHeaderViewParams($data),
				'navbar'	=> $this->getNavViewParams(),
				'mobileNav'	=> $this->getNavViewParams(),
				'headTag'	=> $this->getHeadTagViewParams($data),
				'footer'	=> $this->getFooterViewParams($data)
			];
		}
		protected function getContactInfo(array $cData): array
		{	
			return [
				'rodPhone'		=> $cData['ROD_PHONE'],
				'georgePhone' 	=> $cData['GEORGE_PHONE'],
				'siteEmail' 	=> $cData['SITE_EMAIL']				
			];
		}
		protected function getSocialLinks(array $cData): array
		{	
			return [
				'common' => [
					'facebook' => [
						'title' => 'Follow us on Facebook', 
						'link' => $cData['SOCIAL_LINK_FACEBOOK'], 
						'icon' => 'facebook-icon.svg', 
						'icon-black' => 'facebook-icon-black.svg'
					],
					'youtube' => [
						'title' => 'Check us out on YouTube',
						'link' => $cData['SOCIAL_LINK_YOUTUBE'], 
						'icon' => 'youtube-icon.svg', 
						'icon-black' => 'youtube-icon-black.svg'
					],
					'instagram' => [
						'title' => 'Follow us on Instagram', 
						'link' => $cData['SOCIAL_LINK_INSTAGRAM'], 
						'icon' => 'instagram-icon.svg', 
						'icon-black' => 'instagram-icon-black.svg'
					],
					'tiktok' => [
						'title' => 'Follow us on TikTok', 
						'link' => $cData['SOCIAL_LINK_TIKTOK'], 
						'icon' => 'tiktok-icon-black.svg', 
						'icon-black' => 'tiktok-icon-black.svg'
					],
					'email' => [
						'title' => 'Email Us', 
						'link' => 'mailto:' . $cData['SITE_EMAIL'], 
						'icon' => 'mail-icon.svg', 
						'icon-black' => 'mail-icon-black.svg'
					],
				],
				'niche' => [
					'gigsalad' => [
						'title' => 'Gig Salad', 
						'link' => $cData['SOCIAL_LINK_GIG_SALAD'], 
						'icon' => '',
						'icon-black' => ''
					],
					'bash' => [
						'title' => 'The Bash', 
						'link' => $cData['SOCIAL_LINK_THE_BASH'], 
						'icon' => '',
						'icon-black' => ''
					],
					'acebooking' => [
						'title' => 'ACE Booking', 
						'link' => $cData['SOCIAL_LINK_ACE_MUSIC_BOOKING'], 
						'icon' => '', 
						'icon-black' => ''
					],
					'thumbtack' => [
						'title' => 'Thumbtack', 
						'link' => $cData['SOCIAL_LINK_THUMBTACK'], 
						'icon' => '', 
						'icon-black' => ''
					]
				]
			];
		}
		private function loadSectionData(): array
		{
			$cData = $this->config->getSiteConfigs();
			return [
				'site'		=> $this->getSiteInfo($cData),
				'contact' 	=> $this->getContactInfo($cData),
				'social' 	=> $this->getSocialLinks($cData),
				'meta' 		=> $this->getMetaData($cData),
			];
		}
		private function getSiteInfo(array $cData): array
		{	
			return [
				'siteName'		=> $cData['SITE_NAME'],
				'siteUrl'		=> $cData['SITE_URL'],
				'siteUrlDev'	=> $cData['SITE_URL_DEV'],	
				'baseDir' 		=> $cData['BASE_DIR']				
			];
		}
		private function getMetaData(array $cData): array
		{	
			return [
				'title'			=> $cData['SITE_TITLE'],
				'description' 	=> $cData['META_DESC_DEFAULT'],
				'keywords' 		=> $cData['META_KEYWORDS_DEFAULT']				
			];
		}
		private function getHeaderViewParams(array $input): array
		{			
			$data = [
				'siteName' => $input['site']['siteName']
			];
			return $data;
		}
		private function getNavViewParams(): array
		{
			$data = [];
			$navData = NavData::getInstance()->getNavData();
			
			$data['links'] = $navData;
			
			return $data;
		}
		private function getHeadTagViewParams(array $input): array
		{
			$meta = $input['meta'];
			$site = $input['site'];

			$baseHref = $site['siteUrl'];
			if($this->config->isDev()){
				$baseHref = $site['siteUrlDev'];
			}

			return [
				'title'			=> $meta['title'],
				'description' 	=> $meta['description'],
				'keywords' 		=> $meta['keywords'], 
				'baseHref' 		=> $baseHref,
				'stylesheets' 	=> $this->page->getPageCss(
									$this->getPageCategory()
								),
				'scripts' 		=> $this->page->getPageJS(
									$this->getPageId()
								)
			];
		}
		private function getFooterViewParams(array $input): array
		{			
			$testimonial = TestimonialData::getInstance();
			$testimonialData = $testimonial->getFooterTestimonial(
				$this->pageInfo['page_id']
			);

			$now = new DateTime();
			$date = new DateTime(
				$testimonialData['date_created']
			);
			$testimonialData['date'] = $date->format('F d, Y');

			if(!empty($testimonialData['location'])){
				$testimonialData['location'] .= ', IL.';
			}

			return [
				'testimonials' => $testimonialData,
				'nav' => $this->getNavViewParams(),
				'social' => $input['social']['common'],
				'info' => [
					'pageId' => $this->getPageId(),
					'siteName' => $input['site']['siteName'],
					'siteEmail' => $input['contact']['siteEmail'],
					'copyRightYear' => $now->format('Y')
				]
			];
		}

		protected function getLinksForPageView(): array
		{
			$pageId = $this->getPageId();
			$allLinks = $this->page->getPageLinkList();
			$pageLinks = [];
			$output = [];

			switch($pageId){
				case 1: //Home
					//Live, DJ, Wedding, Testimonials, Shows
					$pageLinks = [2, 3, 5, 10, 11];
					break;
				case 2: //Live Music
					//DJ, Shows, Song List
					$pageLinks = [3, 11, 14];
					break;
				case 3: //DJ Services
					//Live, Weddings, Corporate, School, Parties, Bars
					$pageLinks = [2, 5, 6, 7, 9, 17];
					break;
				case 4: //Karaoke
					//DJ
					$pageLinks = [3];
					break;
				case 5: //Weddings
					//Live, DJ
					$pageLinks = [2, 3];
					break;
				case 6: //Corporate Events
					//Live, DJ, Holiday
					$pageLinks = [2, 3, 8];
					break;
				case 7: //School Dances
					//DJ
					$pageLinks = [3];
					break;
				case 8: //Holiday Parties
					//Live, DJ, Corporate
					$pageLinks = [2, 3, 6];
					break;
				case 9: //Parties
					//Live, DJ
					$pageLinks = [2, 3];
					break;
				case 10: //Testimonials
					//NONE
					break;
				case 11: //Shows
					//NONE
					break;
				case 12: //Contact Us
					//NONE
					break;
				case 13: //404
					//Home, Contact Us
					$pageLinks = [1, 12];
					break;
				case 14: //Song List
					//NONE
					break;
				case 15: //Block Parties
					//Live, DJ
					$pageLinks = [2, 3];
					break;
				case 16: //Restaurants
					//Live
					$pageLinks = [2];
					break;
				case 17: //Bars & Clubs
					//Live, DJ, Weddings, Corporate, Parties
					$pageLinks = [2, 3, 5, 6, 9];
					break;
				default:
					break;
			}

			//Always add Contact Us link
			array_push($pageLinks, 12);

			if(sizeof($pageLinks) > 0){
				for($i=0; $i < sizeof($pageLinks); $i++)
				{	
					$output[$pageLinks[$i]] = $allLinks
					[
						$pageLinks[$i]
					];
				}
			}
			return $output;
		}
	}
?>