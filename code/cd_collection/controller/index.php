<!--This file loads the appropriate view file depending on the presence of the album_id session variable-->

<?php
require '../model/database.php';

//Set action based on presence of album_id
if(isset($_GET['album_id'])){
    $action = 'details';
}
else{
    $action = 'search';
}

//Display appropriate view
if($action=='search'){
include '../view/srp.php';
}
else if($action == 'details'){
include '../view/details.php';
}

?>