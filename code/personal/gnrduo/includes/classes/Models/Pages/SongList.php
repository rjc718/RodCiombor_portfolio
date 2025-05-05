<?php
require 'includes/classes/Models/PageModel.php';

class SongList extends PageModel{
    public function getPageViewParams(): array
    {
        $rows = '';
        $songsData = SongsData::getInstance()->getAll();
        
        $date = $songsData[0]['date_added'];

        foreach($songsData as $song){
            if($song['date_added'] > $date){
                $date = $song['date_added'];
            }
            $rows .= $this->view->render(
                'pages/songs/row.php', 
                [
                    'title' => '"' . $song['title'] . '"' ?? '',
                    'artist' => $song['artist'] ?? ''
                ]
            );
        } 
        
        $dateUpdated = new DateTime($date);

        return [
            'layout'     => $this->getPageLayoutParams('song-list'),
            'widgetData' => $this->getPageWidgetData(),
            'page'  => [
                'dateUpdated' => $dateUpdated->format('m/d/y'),
                'rows' => $rows
            ]
        ];
    }
}