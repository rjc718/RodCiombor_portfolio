<?php
require 'includes/classes/Models/PageModel.php';

class Testimonials extends PageModel{
    public function getPageViewParams(): array
    {
        $rows = '';
        $params = [];

        $testimonials = TestimonialData::getInstance()->getAll();

        foreach($testimonials as $t){
            $location = $t['location'];
            if(!empty($t['location'])){
                $location .= ', IL.';
            }
            $date = new DateTime($t['date_created']);

            $rows .= $this->view->render(
                'pages/testimonials/row.php', 
                [
                    'imgSrc' => $t['testimonial_img'] ?? '',
                    'date' => $date->format('F d, Y'),
                    'customerName' => $t['customer_name'] ?? '',
                    'text' => $t['text'] ?? '',
                    'subText' => $t['sub_text'] ?? '',
                    'subText2' => $t['sub_text2'] ?? '',
                    'location' => $location ?? '',
                ]
            );
        }    
        return [
            'layout'     => $this->getPageLayoutParams('testimonials-page'),
            'widgetData' => $this->getPageWidgetData(),
            'page'  => [
                'rows' => $rows
            ]
        ];
    }
}