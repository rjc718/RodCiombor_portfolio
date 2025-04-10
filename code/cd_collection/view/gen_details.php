<!--This file pulls album ID from session variable, and gets all information from database about record with matching album id-->
<?php

require '../model/database.php';

$imgPath = "../assets/img/albums/";

function getID(){
    if(isset($_GET['album_id'])){
        $album_id = $_GET['album_id'];
        
        return $album_id;
    }

    else{
        echo '<p>Error:  Album ID not set</p>';
    }  
}


        //Get album ID from session          
        $alb_id = getId();
        
        //Query string for details page
        $dq = "SELECT al.albumID as 'album ID', al.albumName as 'Album Name',
            al.releaseDate as 'Release Date', al.imgURL as 'imgURL', al.trackList as 'Track List',
            ar.artistName as 'Artist Name', g.genre as 'Genre'
            FROM albums as al
            INNER JOIN artists as ar
            ON
            al.artist_id = ar.artistID
            INNER JOIN genres as g
            ON
            al.genre_id = g.genreID
            WHERE al.albumID = " . $alb_id . ";";
        
        //pass query into database, return array
          $dq_result = $dcon->query($dq);
          $r = $dq_result->fetch(PDO::FETCH_ASSOC);
          
        //Assign each index of array to one of variables
          
          $artistName = $r['Artist Name'];
          $albumName = $r['Album Name'];
          $releaseDate = $r['Release Date'];
          $genre = $r['Genre'];
          $trackList = $r['Track List'];
          $imgURL = $r['imgURL'];
        
        //Display detail page content
        include 'detail_content.php';
          
?>