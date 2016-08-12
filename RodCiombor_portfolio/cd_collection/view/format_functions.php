<!--functions that format data to display on page-->

<?php

//functions for formatting date
function get_format_date($dateStr){
    $date = date_create($dateStr);
    $date_f = date_format($date, 'F j, Y');
    return $date_f;
}

function formatDate($dateStr){
    $date = date_create($dateStr);
    echo date_format($date, 'F j, Y');    
}

//function to break track list string into array
function trackList($t_str){
    $tList = explode("_", $t_str);
    
    foreach($tList as $track){
        echo '<li>' . $track . '</li>';
    }   
}

?>