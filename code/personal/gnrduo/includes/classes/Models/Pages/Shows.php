<?php
require 'includes/classes/Models/PageModel.php';

class Shows extends PageModel
{
    public function getPageViewParams(): array
    {
        $newShowList = [];
        $pastShowList = [];
        $yearList = [];

        $showsData = ShowsData::getInstance();
        $shows = $showsData->loadShows();

        foreach($shows['upcoming'] as $show){
            $row = $this->view->render(
                'pages/shows/row.php', 
                $showsData->getShowParams($show)
            );
            $newShowList[] = $row; 
        }
        foreach($shows['past'] as $k => $v){  
            $yearList[] = $k;    
            foreach($v as $show){
                $row = $this->view->render(
                    'pages/shows/row.php', 
                    $showsData->getShowParams($show)
                );
                $pastShowList[$k][] = $row; 
            }      
        }

        $yearRows = '';
        $yearButtons = '';
     

        //echo '<pre>';
        $chunks = array_chunk($yearList, 4);
        $active = true;
        //echo var_dump($chunks);
        //die();

        foreach($chunks as $chunk){
            $yearButtons = '';
            for($i=0; $i<sizeof($chunk); $i++){
                $yearButtons .= $this->view->render(
                    'pages/shows/years/button.php', 
                    [
                        'active' => ($active ? ' active' : ''),
                        'pressed' => ($active ? 'true' : 'false'),
                        'year' => $chunk[$i]
                    ]
                );
                $active = false;
            }
            
            $yearRows .= $this->view->render(
                'pages/shows/years/row.php', 
                [
                    'links' => $yearButtons
                ]
            );
        }

        /*for($i=0; $i<sizeof($yearList); $i++){           
            
            $yearButtons .= $this->view->render(
                'pages/shows/years/button.php', 
                [
                    'active' => ($i == 0 ? ' active' : ''),
                    'pressed' => ($i == 0 ? 'true' : 'false'),
                    'year' => $yearList[$i]
                ]
            );
            
            if(($x==3) || ($x == sizeof($yearList) - 1)){
                $yearRows .= $this->view->render(
                    'pages/shows/years/row.php', 
                    [
                        'links' => $yearButtons
                    ]
                );
                $yearButtons = '';
                $x=0;
            }
            else{
                $x++;
            }
        }*/

        return [
            'layout' => $this->getPageLayoutParams('shows-page'),
            'widgetData' => $this->getPageWidgetData(),
            'page' => [
                'upcomingShowList' => $newShowList,
                'yearRows' => $yearRows,
                'pastShowLists' => $pastShowList
            ]
        ];
    }
}