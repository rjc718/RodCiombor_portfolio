<?php 
class ImageGallery{
    protected $view;
    protected $db;
    private const GET_DATA_BY_GALLERY_ID = <<<SQL
        SELECT * 
        FROM gallery_photos 
        WHERE gallery_id = ?
        ORDER BY sort
    SQL;

	public function __construct(
            PhpTemplateView $view, 
            Database $db
        )
		{
			$this->view = $view;
            $this->db = $db;
		}
        public static function getInstance(
            ?PhpTemplateView $view = null,
            ?Database $db = null
        ): self {
            if (empty($view)) {
                $view = PhpTemplateView::getInstance();
            }
            if (empty($db)) {
                $db = Database::getInstance();
            }
            return new self($view, $db);
        }

        public function createGallery(int $galleryId): string
        {
            $path = 'assets/img/galleries/gallery' . $galleryId . '/';
            $photoList = $this->getPhotoList($galleryId);
            $srcList = [];
            $height = 300;
            $width = 300;

            //Wont display unless there are two or more images in gallery
            if(sizeof($photoList) > 1){
                //Loop through and create images
                foreach($photoList as $photo){
                    $srcList[] = $photo['img_src'];
                }

                $images = '';
                for($i=0; $i<2; $i++){
                    $images .= $this->view->render(
                        'widgets/img-flipper/image.php', [
                            'src' => $path . $photoList[$i]['img_src'],
                            'num' => $i + 1,
                            'width' => $width,
                            'height' => $height
                        ]
                    );  
                }

                $html = $this->view->render(
                    'widgets/img-flipper/gallery.php', [
                        'imgList' => implode('|', $srcList),
                        'images' => $images,
                        'imgIndex' => 0,
                        'width' => $width,
                        'height' => $height,
                        'galleryId' => $galleryId
                    ]
                );
            }
            return $html ?? '';
        }

        private function getPhotoList(int $galleryId){
			$params['sql'] = self::GET_DATA_BY_GALLERY_ID;
			$params['types'] = 'i';
            return $this->db->getQueryData($params, [$galleryId]) ?? [];
        }
	}
?>