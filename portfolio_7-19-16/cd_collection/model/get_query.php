<!--Function to query database on search results page, based on selection from drop down list-->

<?php
function getQuery(){

    //Initialize variables
    $za = "DESC";
    $q = "SELECT al.albumID as 'album ID', al.ImgURL as 'imgURL', al.albumName as 'Album Name',
    ar.artistName as 'Artist Name', al.releaseDate as 'Release Date', g.genre as 'Genre'
    FROM albums as al
    INNER JOIN artists as ar
    ON al.artist_id = ar.artistID
    INNER JOIN genres as g
    ON al.genre_id = g.genreID
    ORDER BY ";

    //Valid input values
    $fieldlist = array("albumAZ","albumZA","artistAZ","artistZA","genre","relDate","relDateRev");

    //Get input from form
    $sortField = filter_input(INPUT_POST, "sortField");

    //check against list of accepted responses array
    if(!in_array($sortField, $fieldlist)){
        echo "This is not a valid field name";
    }
    else{
    //Use switch to determine appropriate query
        switch($sortField){
            case "albumAZ":
                $query = $q . "al.albumName, ar.artistName;";
                break;
            case "albumZA":
                $query = $q . "al.albumName $za, ar.artistName $za;";
                break;
            case "artistAZ":
                $query = $q . "ar.artistName, al.albumName;";
                break;
            case "artistZA":
                $query = $q . "ar.artistName $za, al.albumName $za;";
                break;
            case "genre":
                $query = $q . "g.genre, ar.artistName, al.albumName;";
                break;
            case "relDate":
                $query = $q . "al.releaseDate, ar.artistName, al.albumName;";
                break;
            case "relDateRev":
                $query = $q . "al.releaseDate $za, ar.artistName $za, al.albumName $za;";
                break;
            default:
                $query = $q . "ar.artistName, al.albumName;";
                break;
        } //End switch
        
        return $query;
        
    }//End else
     
}//end getQuery

?>