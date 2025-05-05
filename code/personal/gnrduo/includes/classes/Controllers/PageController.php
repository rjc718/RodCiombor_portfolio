<?php
	class PageController
	{
        private $model;
		private $builder;
		
		public function __construct(
			PageModel $model,
			PageBuilder $builder
		)
		{
			$this->model = $model;
			$this->builder = $builder;
		}

		public function execute(): void
		{
			$templateList = [];

			//Get Site Info and load page assets			
			$sectionParams = $this->model->loadSectionViewParams();
			
			//Build the Site Layout 
			$templateList = $this->builder->getLayoutTemplates(
				$sectionParams
			);

			//Build the View/Main Content 			
			$templateList['body'] = $this->builder->buildMainContent(
				$this->model->getPageViewName(), 
				$this->model->getPageViewParams(), 
				$this->model->getPageCategory()
			);

			//Generate the Page
			$output = $this->builder->generatePage(
				$templateList
			); 
			echo $output;	
		}
	}
?>