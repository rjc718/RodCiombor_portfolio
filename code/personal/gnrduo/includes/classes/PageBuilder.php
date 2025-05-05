<?php 
	require 'includes/classes/Models/Widgets/Video.php';
	require 'includes/classes/Models/Widgets/ImageGallery.php';

	class PageBuilder
	{
		protected $view;
						
		public function __construct(PhpTemplateView $view)
		{
			$this->view = $view;
		}
		public static function getInstance(?PhpTemplateView $view = null): PageBuilder
		{				
			if (empty($view)) {
				$view = PhpTemplateView::getInstance();
			}
			return new self($view);
		}
		private function buildScriptTags(array $userScripts = []): string
		{
			$html = '';
			$scripts = [];
			$scripts[0] = [
				'src' => 'js/main.js',
				'async' => false,
				'defer' => false,
				'params' => '',
			];
			$scripts[1] = [
				'src' => 'js/widgets.js',
				'async' => false,
				'defer' => false,
				'params' => '',
			];

			if(sizeof($userScripts) > 0){
				for($i=0; $i < sizeof($userScripts); $i++){
					$scripts[$i + 1] = [
						'src' => 'js/' . $userScripts[$i]['src'],
						'async' => false,
						'defer' => false,
						'params' => '',
					];
				}
			}
			foreach($scripts as $script){
				$html .= $this->view->render(
					'tags/script.php', 
					$script
				) . "\n"; 
			}
			return $html;
		}
		private function buildCssLinks(array $links = []): string
		{
			$params = [];
			$html = '';
			if(sizeof($links) > 0){
				for($i=0; $i < sizeof($links); $i++){
					$params[$i] = [
						'rel' => 'stylesheet', 
						'href' => 'css/' . $links[$i]['href'] . '.css'
					];
					$html .= $this->view->render(
						'tags/link.php', $params[$i]
					) . "\n"; 
				}
			}
			return $html;
		}
		private function buildHeadTag(array $params): string
		{						
			//Passes in page meta data as $params
			//Adds CSS and Scripts
			$params['linkTags'] = $this->buildCssLinks(
				$params['stylesheets']
			);
			$params['scriptTags'] = $this->buildScriptTags(
				$params['scripts']
			);
			return $this->view->render(
				'tags/head.php', 
				$params
			);
		}
		private function buildHeader(array $params): string
		{
			return $this->view->render(
				'layout/header.php', 
				$params
			);
		}
		private function buildNavbar(array $data): string
		{
			$params['menus'] = [];
			for($i=0; $i<sizeof($data['links']); $i++){
				$params['menus'][$i] = $this->buildNavMenuItem(
					$data['links'][$i]
				);
			}
			//Possibly break the menus into smaller templates
			return $this->view->render(
				'layout/navbar.php', 
				$params
			) ?? '';
		}
		private function buildNavMenuItem(array $link, $mobile = false): string
		{	
			$dir = 'nav';
			if($mobile){
				$dir = 'mobile-nav';
			}
			
			if(empty($link['nav_link'])){
				$html = $this->view->render(
					$dir . '/dropdown.php', [
						'title' => $link['nav_title'],
						'sublinks' => $this->buildNavSubLinks(
							$link['sublinks'],
							$mobile
						)
					]
				);
			}
			else{
				$html = $this->view->render(
					$dir . '/link.php', [
						'title' => $link['nav_title'],
						'href' => $link['nav_link'],
						'new_tab' => $link['new_tab']
					]
				);
			}
			return $html ?? '';
		}
		private function buildNavSubLinks(array $sublinks, $mobile = false): array
		{
			$list = [];

			$dir = 'nav';
			if($mobile){
				$dir = 'mobile-nav';
			}

			foreach($sublinks as $sublink){
				$html = $this->view->render(
					$dir . '/link.php', [
						'title' => $sublink['nav_title'],
						'href' => $sublink['nav_link'],
						'new_tab' => $sublink['new_tab']
					]
				);
				array_push($list, $html);
			}
			return $list;
		}
		private function buildNavBarMobile(array $data): string
		{		
			$params['menus'] = [];
			for($i=0; $i<sizeof($data['links']); $i++){
				$params['menus'][$i] = $this->buildNavMenuItem(
					$data['links'][$i],
					1
				);
			}
			//Possibly break the menus into smaller templates
			return $this->view->render(
				'layout/navbar-mobile.php', 
				$params
			) ?? '';
		}
		private function buildFooter(array $data): string
		{						
			$testimonial = '';
			$testimonialExclude = [10];
			$info = $data['info'];

			if(!in_array($info['pageId'], $testimonialExclude)){
				$testimonial = $this->view->render(
					'layout/footer/testimonials.php', 
					$data['testimonials']
				);
			}
			return $this->view->render(
				'layout/footer.php', [
					'views' => [
						'nav' => $this->buildFooterNav($data),
						'testimonial' => $testimonial ?? ''
					],
					'info' => $info
				]
			);
		}
		private function buildFooterNav(array $data): string
		{
			$menus = '';
			$contact = '';
			$social = '';

			$navData = $data['nav']['links'];
			$socialLinks = $data['social'];
	
			foreach($navData as $nav){
				if($nav['nav_id'] == 4){
					foreach($socialLinks as $link){
						if(!empty($link['link'])){
							$social .= $this->view->render(
								'layout/footer/social-link.php', 
								[
									'title' => $link['title'],
									'link' => $link['link'],
									'icon' => $link['icon'],
								]
							);
						}
					}
					$contact = $this->view->render(
						'layout/footer/contact.php', 
						[
							'title' => $nav['nav_title'],
							'href' => $nav['nav_link'],
							'social' => $social,
						]
					);
				}
				else{
					$links = '';
					foreach($nav['sublinks'] as $link){
						$links .= $this->view->render(
							'layout/footer/nav-link.php', 
							[
								'title' => $link['nav_title'],
								'href' => $link['nav_link'],
							]
						);
					}
					$menus .= $this->view->render(
						'layout/footer/nav-menu.php', 
						[
							'title' => $nav['nav_title'],
							'links' => $links
						]
					);
				}
			}
			
			$params = [
				'menus' => $menus,
				'contact' => $contact,
				'info' => $data['info']
			];

			$output = $this->view->render(
				'layout/footer/nav.php', 
				$params
			);
			return $output;
		}

		public function getLayoutTemplates(array $params): array
		{	
			return [
				'head' => $this->buildHeadTag($params['headTag']),
				'header' => $this->buildHeader($params['header']),
				'navbar' => $this->buildNavbar($params['navbar']),
				'mobileNav' => $this->buildNavBarMobile($params['mobileNav']),
				'footer' => $this->buildFooter($params['footer']) 	
			];
		}

		private function buildPageView(string $view, array $data): string
		{
			return $this->view->render(
				'pages/' . $view . '.php', 
				$data
			);
		}
		
		private function getMainContentLayoutTemplate(int $pageCategory): string
		{
			switch($pageCategory){
				case 1:
					$template = 'articlePage';
					break;
				case 2:
					$template = 'listPage';
					break;
				case 3:
					$template = 'homePage';
					break;
				default;
					$template = '';
			}
			return $template;
		}

		public function buildMainContent(
			string $view, 
			array $data, 
			int $pageCategory
		): string
		{	
			$layoutTemplate = $this->getMainContentLayoutTemplate(
				$pageCategory
			);
			
			$data['page']['widgets'] = $this->buildPageWidgets(
				$data['widgetData']
			);
			if(isset($data['pageData'])){
				if(isset($data['pageData']['links'])){
					$data['page']['links'] = $data['pageData']['links'];
				}
				if(isset($data['pageData']['siteName'])){
					$data['page']['siteName'] = $data['pageData']['siteName'];
				}
			}
			return $this->view->render(
				'layout/' . $layoutTemplate . '.php', 
				[
					'view' => $this->buildPageView(
						$view, 
						$data['page']
					),
					'data' => $data['layout']
				]
			);
		}
		
		public function generatePage(array $data): string
		{
			$html = $this->view->render('layout/page.php', $data);
			return $html;
		}
		public function buildPageWidgets($data): array
		{
			$widgets = [];
			foreach($data as $d){
				switch((int) $d['widget_type']){
					case 1:
						//Videos
						$c = Video::getInstance();
						$widgets['video_flipper'] = $c->createGallery(
							$d['widget_id']
						);
						break;
					case 2:
						//Image Flippers
						$c = ImageGallery::getInstance();
						$widgets['img_flipper'] = $c->createGallery(
							$d['widget_id']
						);
						break;
					case 3:
						//Song List Modals
						break;
					default;
						break;
				}
			}
			return $widgets;
		}
	}
?>