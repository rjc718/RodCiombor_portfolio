<!--This page sets up database connections-->

<?php
//Set up connection for search results page on desktop
/*$con=new PDO('mysql:host=localhost;dbname=practice',"Rod","w@rhammer83");
$con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

//Set up connection for details page
$dcon=new PDO('mysql:host=localhost;dbname=practice',"Rod","w@rhammer83");
$dcon->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);*/

//Set up connection for search results page on laptop
$con=new PDO('mysql:host=localhost;dbname=test',"Rod","w@rhammer83");
$con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

//Set up connection for details page
$dcon=new PDO('mysql:host=localhost;dbname=test',"Rod","w@rhammer83");
$dcon->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


?>