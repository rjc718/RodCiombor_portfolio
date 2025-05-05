<?php
require 'includes/classes/Database.php';
require 'includes/classes/PhpTemplateView.php';
require 'includes/classes/Configuration.php';
require 'includes/classes/PageInfo.php';
require 'includes/classes/PageBuilder.php';

$db = Database::getInstance();
$view = PhpTemplateView::getInstance();
$builder = PageBuilder::getInstance();
$config = Configuration::getInstance();
$page = new PageInfo($db);
$slugList = $config->getAllPageSlugs();

$url  = $_SERVER['REQUEST_URI']; //SANITIZE THIS
$path = explode('/', $url); 
$slug = end($path);

if(empty($slug) || $slug == '/')
{
	$slug = 'home';
}

$slugExists = false;
foreach($slugList as $s){
	if($s['page_slug'] == $slug){
		$slugExists = true;
	}
}
if(!$slugExists)
{
	$targKey = 'SITE_URL';
	if($config->isDev()){
		$targKey = 'SITE_URL_DEV';
	}
	http_response_code(404);
	header("Location: " . $config->getConfigValue($targKey) . "404");
	exit();
}

$modelName = $page->getModelNameBySlug($slug);

require 'includes/classes/Controllers/PageController.php';
require 'includes/classes/Models/Pages/' . $modelName . '.php';

$model = new $modelName($db, $view, $config, $page, $builder, $slug);

$controller = new PageController($model, $builder);
$controller->execute();
