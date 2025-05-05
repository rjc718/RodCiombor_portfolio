<?php
class ShowsData
{
	protected $db;
	private const START_YEAR = 2021;
    
    private const GET_ALL_SHOWS = <<<SQL
        SELECT * FROM shows s 
        LEFT JOIN venues v 
        ON s.venue_id = v.venue_id 
        WHERE status = 1 
        ORDER BY date DESC;
    SQL;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}
	public static function getInstance(?Database $db = null): ShowsData
	{				
		if (empty($db)) {
			$db = Database::getInstance();
		}
		return new self($db);
	}
    private function getAll()
    {			
        $params = [
            'sql' => self::GET_ALL_SHOWS,
            'types' => '*'
        ];
		return $this->db->getQueryData(
            $params
        ) ?? [];
    }
    private function groupShows(array $shows): array
    {
        $groups['upcoming'] = [];
        $groups['past'] = [];
            
        $years = $this->loadYears();
            
        $today = new DateTime();
        $yesterday = $today->sub(
            new DateInterval('P1D')
        );
    
        foreach($shows as $show){
            $date = DateTime::createFromFormat(
                'Y-m-d', 
                $show['date']
            );

            if($date > $yesterday){
                $groups['upcoming'][] = $show;
            }
            else{
                $year = $date->format('Y');
                $groups['past'][$year][] = $show;
            }
        }

        $size = sizeof($groups['upcoming']);
        if($size > 0){
            $sort = [];
            for($i = $size - 1; $i >= 0; $i--)
            {
                array_push(
                    $sort, 
                    $groups['upcoming'][$i]
                );
            }
            $groups['upcoming'] = $sort;
        }
        return $groups ?? [];
    }
    public function loadShows(): array
    {
        $data = $this->getAll();

        if(sizeof($data) > 0)
        {
            $shows = $this->groupShows($data);
        }
        return $shows ?? [];
    }
    public function loadYears(): array
    {
        $years = [];
        $start = $this->getStartYear();
        $d = new DateTime();
        $thisYear = $d->format('Y');

        for($i = $start; $i <= $thisYear; $i++)
        {
            $years[] = $i;
        }
        return $years;
    }
    public function getStartYear(): int
    {
        return self::START_YEAR;
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
            'year' => $dateArray[0],
            'month' => $dateArray[1],
            'day' => $dateArray[2],
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
