<!--Generate and display content on the search results page based on the sort query selected from dropdown list-->
<?php

require '../model/database.php';
require 'format_functions.php';


$imgPath = "../assets/img/albums/";
$query = filter_input(INPUT_POST, "qResult");

//If field invalid query was entered, print error message
if($query == "error"){    
    echo "This is not a valid field name";
}

else{

    //Link query to connection, assign to $result variable
    //Set connection/query so it is an associative array
    //Each row within the $result array is it's own associative array, with imgURL, artistName and albumName
    $result = $con->query($query);
    $result->setFetchMode(PDO::FETCH_ASSOC);

    //Loop through each row in $result array
    //The variable containing the information will be $pass
    foreach ($result as $pass){

        //Use names of columns in database as keys in associative array, assign to variables
        $albumID = $pass['album ID'];
        $imgURL =  $pass['imgURL'];
        $artistName = $pass['Artist Name'];
        $albumName = $pass['Album Name'];
        $genre = $pass['Genre'];
        $relDate = $pass['Release Date'];
        
        //Format date into month, day, year
        $fDate = get_format_date($relDate);

        //Print HTML

echo <<<HERE

 <div class="item_container">
                <a href="index.php?album_id=$albumID">
                    <img src="$imgPath$imgURL"/>
                </a>
                <h2>
                    Album Name:  $albumName
                </h2>
                <h2>
                    Artist: $artistName
                </h2>
                <span class="genre_release">Genre: $genre &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     Release Date: $fDate</span>
                <br />
                <br />
                <a href="index.php?album_id=$albumID">View Details</a>         
            </div>
HERE;

     }//End loop

}//End else

?>