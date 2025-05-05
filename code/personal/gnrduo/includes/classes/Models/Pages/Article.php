<?php 
require 'includes/classes/Models/PageModel.php';

//Get article text.  get Videos?  getSongList? modalData? 
//Data to set inputParams for Templates
class Article extends PageModel{
    public function getPageViewParams(): array
    {
        $title = $this->getPageTitle();
        $links = $this->getLinksForPageView();

        if($this->getPageId() == 13){
            $layout = [
                'ctaButton' => ''
            ];
        }
        else{
            $layout = [
                'ctaButton' => $this->view->render(
                    'components/cta-button.php', 
                    [
                        'link' => $links[12]['href'],
                        'text' => 'Contact us about ',
                        'text2' => $title,
                        'title' => 'Contact us about ' . $title
                    ]
                )
            ];
        }
        return [
            'layout'     => $this->getPageLayoutParams('', $layout),
            'widgetData' => $this->getPageWidgetData(),
            'pageData'   => [
                'links' => $links,
                'siteName' => $this->getSiteName()
            ]
        ];
    }
}
