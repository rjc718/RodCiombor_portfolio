<?php
class SongsData
{
	protected $db;
	private const START_YEAR = 2021;
    
    private const GET_ALL_SONGS = <<<SQL
        SELECT * FROM songs 
        ORDER BY title;
    SQL;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}
	public static function getInstance(?Database $db = null): SongsData
	{				
		if (empty($db)) {
			$db = Database::getInstance();
		}
		return new self($db);
	}
    public function getAll()
    {			
        $params = [
            'sql' => self::GET_ALL_SONGS,
            'types' => '*'
        ];
		return $this->db->getQueryData(
            $params
        ) ?? [];
    }
    
    public function getShowParams(array $show): array
    {  
        $dateArray = explode(
            '-', 
            $show['date']
        );
        if(!empty($show['start_time']) && !empty($show['end_time']))
        {
            $time = $show['start_time'] . ' - ' . $show['end_time'];
        }

        $imgFolder = 'assets/img/shows/';
        if(!empty($show['img_src']))
        {
            $imgSrc = $imgFolder . $show['img_src'];
        }
        elseif(!empty($show['venue_img_src']) && $show['venue_id'] > 0)
        {
            $imgSrc = $imgFolder . $show['venue_img_src'];
        }

        if($show['venue_id'] > 0)
        {
            $location = sprintf(
                '%s (%s)',
                $show['venue_name'],
                $show['venue_city']     
            );
            $address = sprintf(
                '%s, %s, IL. %s',
                $show['address'],
                $show['venue_city'],
                $show['zipcode']
            );
            $gmapLink = $show['gmap_link'] ?? '';
        }
        else{
            $location = $show['city'] . ', IL.'; 
        }
        return [
            'day' => $dateArray[1],
            'month' => $dateArray[2],
            'evtId' => $show['id'],
            'title' => $show['title'] ?? '',
            'description' => $show['description'] ?? '',
            'time' => $time ?? '',
            'imgSrc' => $imgSrc ?? '',
            'location' => $location ?? '',
            'address' => $address ?? '',
            'gmap_link' => $gmapLink ?? ''
        ];
    }     
}
