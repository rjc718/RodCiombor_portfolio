<?php
require 'includes/classes/Models/PageModel.php';
//I legit don't know.  This might get deleted

class Home extends PageModel{
    public function getPageViewParams(): array
    {      
        $modulesList = [
            'welcome',
            'core-services',
            'shows',
            'extra-services',
            'testimonials'
        ];
        $modules = '';
        foreach($modulesList as $module){
            $modules .= $this->view->render(
                'pages/home/' . $module . '.php', 
                $this->getModuleViewParams($module)
            );
        }

        return [
            'layout'     => $this->getPageLayoutParams(
                'home-page', 
                $layout ?? []
            ),
            'widgetData' => $this->getPageWidgetData(),
            'page'       => [
                'modules' => $modules
            ]
        ];
    }
    private function getModuleViewParams(string $key): array
    {    
        $links = $this->getLinksForPageView();
        $siteName = $this->getSiteName();
        $data = [
            'welcome' => [
                'imgGallery' => ImageGallery::getInstance()->createGallery(1),
                'siteName' => $siteName,
                'ctaButton' => $this->view->render(
                    'components/cta-button.php', 
                    [
                        'link' => $links[12]['href'],
                        'text' => $links[12]['title'],
                        'text2' => '',
                        'title' => $links[12]['title']
                    ]
                )
            ],
            'core-services' => [
                'boxes' => $this->getCoreServiceBoxes($links)
            ],
            'testimonials' => [
                'siteName' => $siteName,
                'ctaButton' => $this->view->render(
                    'components/cta-button.php', 
                    [
                        'link' => $links[10]['href'],
                        'text' => 'Read More',
                        'text2' => '',
                        'title' => 'Read Our Testimonials'
                    ]
                )
            ],
            'extra-services' => [
                'siteName' => $siteName,
                'services' => [
                    'Anniversaries',
                    'Vow Renewals',
                    'Birthdays',
                    'Graduations',
                    'School Dances',
                    'Class Reunions',
                    'Block Parties',
                    'Fundraisers',
                    'Galas',
                    'Holiday Parties'
                ]
            ],
            'shows' => [
                'ctaButton' => $this->view->render(
                    'components/cta-button.php', 
                    [
                        'link' => $links[11]['href'],
                        'text' => 'Learn More',
                        'text2' => '',
                        'title' => 'Learn About Upcoming Shows'
                    ]
                )
            ]
        ];
        return $data[$key] ?? [];
    }
    private function getCoreServiceBoxes(array $links)
    {
        $output = '';
        $dir = 'pages/home/components/services/';
        $data[0] = [
            'title' => $links[2]['title'],
            'icon' => 'assets/img/icons/guitar.svg',
            'link' => $links[2]['href'],
            'description' => $this->view->render(
                $dir . 'text/live-music.php', []
            )
        ];
        $data[1] = [
            'title' => $links[3]['title'],
            'icon' => 'assets/img/icons/turntable.svg',
            'link' => $links[3]['href'],
            'description' => $this->view->render(
                $dir . 'text/dj-services.php', []
            )
        ];
        $data[2] = [
            'title' => $links[5]['title'],
            'icon' => 'assets/img/icons/diamond-ring.svg',
            'link' => $links[5]['href'],
            'description' => $this->view->render(
                $dir . 'text/weddings.php', []
            )
        ];
        foreach($data as $box){
            $output .= $this->view->render(
                $dir . 'box.php', $box
            );
        }
        return $output;
    }
}
