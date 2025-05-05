<?php 
	class Video
	{
		protected $view;
        protected $dbo;
        
        private const GET_DATA_BY_GALLERY_ID = <<<SQL
            SELECT v.video_id, v.title, v.video_src, v.description
            FROM videos v 
            LEFT JOIN video_galleries g 
            ON v.video_id = g.video_id 
            WHERE g.gallery_id = ? 
            ORDER BY g.sort;
        SQL;

        private const GET_DATA_BY_VIDEO_ID = <<<SQL
			SELECT * FROM videos WHERE video_id = ?
		SQL;
						
		public function __construct(PhpTemplateView $view, Database $dbo)
		{
			$this->view = $view;
            $this->dbo = $dbo;
		}
        public static function getInstance(
            ?PhpTemplateView $view = null,
            ?Database $dbo = null
        ): self {
            if (empty($view)) {
                $view = PhpTemplateView::getInstance();
            }
            if (empty($dbo)) {
                $dbo = Database::getInstance();
            }
            return new self($view, $dbo);
        }

        public function createGallery(int $galleryId): string
        {
            $params = [];
            $count = 1;
            $dots = '';
            $slides = '';
            $html = '';
            
            $videoList = $this->getDataByGalleryId($galleryId);
            
            if(sizeof($videoList) > 0){
                foreach($videoList as $v){    
                    $slides .= $this->view->render(
                        'video/gallery/slide.php', 
                        [
                            'video' => $video = $this->createVideo(
                                $v['video_id']
                            ) ?? '',
                            'currentSlide' => $count,
                            'slideCount' => sizeof($videoList)
                        ]
                    );
                    
                    $dots .= $this->view->render(
                        'video/gallery/dot-button.php', ['count' => $count]
                    );
                        
                    $count++;
                }

                $html = $this->view->render(
                    'video/gallery/gallery.php', 
                    [
                        'slides' => $slides,
                        'dots' => $dots,
                    ]
                );
            }
            return $html;
        }

        public function createVideo(int $videoId): string
        {
            $data   = $this->getVideoData($videoId); 
            
            return $this->view->render(
                'video/' . $data['template'] . '.php', 
                $this->getVideoTemplateParams($data)
            ) ?? '';
        }
        
        private function getDataByGalleryId(int $galleryId): array
        {
            $data = [];
			
			$params['sql'] = self::GET_DATA_BY_GALLERY_ID;
			$params['types'] = 'i';
			
			$data = $this->dbo->getQueryData($params, [$galleryId]);
			
			return $data;
        }

        private function getDataByVideoId(int $videoId): array
        {
            $data = [];
			
			$params['sql'] = self::GET_DATA_BY_VIDEO_ID;
			$params['types'] = 'i';
			
			$data = $this->dbo->getQueryResult($params, [$videoId]);
			
			return $data;
        }

        private function getVideoData(int $id): array
        {
            $videoData = [];

            $data = $this->getDataByVideoId($id);
            
            $width           = '100%';
            $height          = 425;
            $template        = 'click-load-video';
            $url             = $data['video_src'] ?? '';
            $videoType       = '';
            $secureLink      = '';
            $start_time      = 0;

            $videoData = [
                'title'    => $data['title'] ?? '',
                'originalSrc' => $url,
                'uploadDate'  => $data['date'] ?? '',
                'description'  => $data['description'] ?? '',
                'height'      => $height,
                'width'       => $width,
                'extraParams' => [],
                'classList'   => []
            ];

            //Check for standard YouTube Videos by extracting $videoId.  
            //If $videoId returns empty, it may be a different video format
            //Reset height and width and other attributes if necessary
            $videoId = $this->extractVideoId($url);

            // Check to see if its a playlist //
            if ($videoId == "") {
                $videoId   = $this->extractVideoId($url, 'playlist');
                $videoType = 'playlist';
            }
            //Check for Custom Player
            if ($videoId == "") {
                $videoId = $this->extractVideoId($url, 'custom_player');
                if ($videoId != "") {
                    $template            = 'object-tag';
                    $videoType           = 'custom_player';
                    $videoData['height'] = "";
                }
            }
            //Check for Vimeo
            if ($videoId == "") {
                $videoId   = $this->extractVideoId($url, 'vimeo');
                $videoType = 'vimeo';
    
                if ($videoId != "") {
                    $template = 'iframe';
                    $videoData['extraParams'] = [
                        'webkitAllowFullScreen', 
                        'mozallowfullscreen', 
                        'allowFullScreen'
                    ];
                } else {
                    //If still empty use MP4
                    $template = 'mp4-video';
                }
            }
    
            $start_time = $this->getStartTime($url);
            
            //Build the secure video link
            if ($videoId != "") {
                $secureLink = $this->buildSecureVideoLink(
                    $videoId, 
                    $videoType, 
                    $start_time
                );
            }
    
            //Add additional datapoints to array
            $videoData['videoId']   = $videoId;
            $videoData['src']       = $secureLink;
            $videoData['template']  = $template;
            $videoData['thumbNail'] = $this->getThumbNail($secureLink) ?? '';

            return $videoData;
        }

        public function getVideoLink(string $link): string
        {
            $result = '';
            if ($link != "") {
                $videoId   = '';
                $videoType = '';
    
                //Check for standard YouTube Videos by extracting $videoId.  
                //If $videoId returns empty, it may be a different video format
                $videoId = $this->extractVideoId($link);
    
                // Check to see if its a playlist //
                if ($videoId == "") {
                    $videoId   = $this->extractVideoId($link, 'playlist');
                    $videoType = 'playlist';
                }
                //Check for Custom Player
                if ($videoId == "") {
                    $videoId = $this->extractVideoId($link, 'custom_player');
                    if ($videoId != "") {
                        $videoType = 'custom_player';
                    }
                }
                //Check for Vimeo
                if ($videoId == "") {
                    $videoId   = $this->extractVideoId($link, 'vimeo');
                    $videoType = 'vimeo';
                }
        
                $start_time = $this->getStartTime($link);

                //Build the secure video link
                if ($videoId != "") {
                    $result = $this->buildSecureVideoLink(
                        $videoId, 
                        $videoType, 
                        $start_time
                    );
                } else {
                    $result = $link;
                }
            }
            return $result;
        }
    
        public function getThumbNail(string $link): string
        {
            $videoId = $this->extractVideoId($link);
            if (!empty($videoId)) {
                $result = 'http://img.youtube.com/vi/' . $videoId . '/1.jpg';
            }
            return $result ?? '';
        }
        
        private function getVideoTemplateParams(array $data): array
        {
            switch ($data['template']) {
                case 'click-load-video':
                    $params = [
                        'src'     => $data['src'],
                        'videoId' => $data['videoId'],
                        'title'   => $data['title'],
                        'width'  => $data['width'],
                        'height' => $data['height'],
                        'description' => $data['description']
                    ];
                    break;
                case 'object-tag':
                    $params = [
                        'src'    => $data['src'],
                        'width'  => $data['width'],
                        'height' => $data['height']
                    ];
                    break;
                case 'mp4-video':
                    $params = [
                        'originalSrc' => $data['originalSrc'],
                        'width'       => $data['width'],
                        'height'      => $data['height']
                    ];
                    break;
                case 'iframe':
                    $params = [
                        'src'    => $data['src'],
                        'width'  => $data['width'],
                        'height' => $data['height']
                    ];
                    break;
                default:
                    $params = [];
            }
            $params['classList']   = (count($data['classList']) > 0 ? implode(" ", $data['classList']) : '');
            $params['extraParams'] = (count($data['extraParams']) > 0 ? implode(" ", $data['extraParams']) : '');
    
    
            return $params;
        }
       
        private function extractVideoId(string $url, string $videoType = ''): string
        {
            $videoId = '';
            $matches = [];
            $parts   = [];

            switch ($videoType) {
                case 'playlist':
                    preg_match_all("/playlist\?list\=(.*)/i", $url, $matches);
                    $videoId = $matches[1][0];
                    break;
                case 'custom_player':
                    preg_match_all("/cp\/(.*)/i", $url, $matches);
                    $videoId = $matches[1][0];
                    break;
                case 'vimeo':
                    preg_match_all("/vimeo.*\/(.*?)$/i", $url, $matches);
                    $videoId = $matches[1][0];
                    break;
                default:
                    //Check for YouTube Videos
                    preg_match_all("/\?v\=(.*?)$/i", $url, $matches);
    
                    if(sizeof($matches[0]) > 0){
                        $videoId = $matches[0][0];
                        $videoId = substr($videoId, 3, 11);                        
                    }

                    if ($videoId == "") {
                        preg_match_all("/youtu\.be\/(.*?)$/i", $url, $matches);
                        if(sizeof($matches[0]) > 0){
                            $videoId = $matches[0][0];
                            $videoId = substr($videoId, 9, 11);
                        }
                    }
            
                    if ($videoId == "") {
                        preg_match_all("/embed.*\/(.*?)$/i", $url, $matches);
                        if(sizeof($matches[1]) > 0){
                            $videoId = $matches[1][0];
                            $parts   = explode('?', $videoId);
                            $videoId = $parts[0];
                        }
                    }

                    if ($videoId == "") {
                        preg_match_all("/shorts.*\/(.*?)$/i", $url, $matches);
                        if(sizeof($matches[1]) > 0){
                            $videoId = $matches[1][0];
                            $parts   = explode('?', $videoId);
                            $videoId = $parts[0];
                        }
                    }
            }
            return $videoId ?? '';
        }
    
        private function getStartTime(string $url): int
        {
            $str     = '';
            $time    = 0;
            $matches = [];
            $parts   = [];
    
            //Check t=
            preg_match_all("/[&?]t=\d+/", $url, $matches);
            if(sizeof($matches[0]) > 0){
                $str = $matches[0][0];
            }

            //Check start=
            if ($str == '') {
                preg_match_all("/[&?]start=\d+/", $url, $matches);
                if(sizeof($matches[0]) > 0){
                    $str = $matches[0][0];
                }
            }

            //Extract time and parse as int
            if ($str != '') {
                $parts = explode('=', $str);
                $str   = $parts[1];
    
                if ($str != '') {
                    $time = (int) $str;
                }
            }

            return $time ?? 0;
        }
    
        private function buildSecureVideoLink(
            string $videoId, 
            string $videoType, 
            int $start_time
        ): string
        {
            $link_src = '';

            if (!empty($videoId)) {
                switch ($videoType) {
                    case 'playlist':
                        $link_src = 'https://www.youtube-nocookie.com/embed?listType=playlist&list=' . $videoId . '?cc_load_policy=1';
                        break;
                    case 'custom_player':
                        $link_src = 'https://www.youtube-nocookie.com/cp/' . $videoId . '?cc_load_policy=1';
                        break;
                    case 'vimeo':
                        $link_src = 'https://player.vimeo.com/video/' . $videoId;
                        break;
                    default:
                        $link_src = 'https://www.youtube-nocookie.com/embed/' . $videoId . '?cc_load_policy=1' . ($start_time > 0 ? '&start=' . $start_time : '');
                }
            }

            return $link_src;
        }
	}
?>