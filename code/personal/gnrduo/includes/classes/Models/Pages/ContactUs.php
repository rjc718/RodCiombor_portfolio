<?php
require 'includes/classes/Models/PageModel.php';
//Validate Contact Form Data.  Maybe do stuff with Recapthca? 
 
class ContactUs extends PageModel{
    public function getPageViewParams(): array
    {
        $cData = $this->config->getSiteConfigs();
        $contactInfo = $this->getContactInfo($cData);
        $socialLinksData = $this->getSocialLinks($cData);
        $socialLinks = '';
        
        foreach($socialLinksData['common'] as $key => $value){ 
            $social = $value;
            if(!empty($social['link']) && $key != 'email'){    
                $socialLinks .= $this->view->render(
                    'pages/contact-us/social-link.php', 
                    [
                        'link' => $social['link'],
                        'title' => $social['title'],
                        'icon' => $social['icon-black']
                    ]
                );
            }
        }
        $layout = [
            'ctaButton' => $this->view->render(
                'components/cta-button.php', 
                [
                    'link' => 'mailto:' . $contactInfo['siteEmail'],
                    'text' => 'Email Us',
                    'text2' => '',
                    'title' => 'Email Us'
                ]
            )
        ]; 
        return [
            'layout'     => $this->getPageLayoutParams(
                'contact-us-page', 
                $layout
            ),
            'widgetData' => $this->getPageWidgetData(),
            'page' => [
                'socialLinks' => $socialLinks,
                'contactInfo' => $contactInfo,
                'siteName' => $this->getSiteName()
            ]
        ];
    }
}
