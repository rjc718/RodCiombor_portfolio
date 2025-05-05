<?php
 
class PhpTemplateView
{
    private $path;
    private static $view;
 
    private function __construct()
    {
        $config = Configuration::getInstance();
        if($config->isDev()){
            $this->path = 'templates\\';
        }
        else{
            $this->path = dirname(__DIR__, 2) . '/templates/';
        }   
    }
 
    public static function getInstance(): PhpTemplateView
    {
        if (empty(self::$view)) {
            self::$view = new self();
        }
        return self::$view;
    }
 
    public function render(string $template, array $data = [])
    {
        if (!file_exists($this->path . $template)) {
            throw new InvalidArgumentException(
                "The template file: '$template' could not be found"
            );
        }
        if (!empty($data)) {
            extract($data);
        }
        ob_start();
       
		include $this->path . $template;
        return ob_get_clean();
    }
}
 

