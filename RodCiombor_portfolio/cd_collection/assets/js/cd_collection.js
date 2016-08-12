function init() {
    

    
    //Assign sort_results function to drop down
    $('#sortField').change(sort_results);
    
    //Display default search results    
    sort_results();
    
}

function sort_results() {
    console.log("sort_results");
    
    var sortField = $('#sortField').val();
    
   // alert("Sort Field: " + sortField);
    
    var qResult = getQ(sortField); 
    
    //alert(qResult);
    /*Use GET method to call PHP file which will call function to get
    appropriate query string based on form input*/
    
    /*Use load mthod to call PHP file that will print HTML output and return as variable*/
    $('#album_list').load('../view/gen_srp_list.php', {"qResult": qResult});
}

function getQ(sortField){
    
   //alert("in function: " + sortField);
   
    var query;
    var q ="SELECT al.albumID as 'album ID', al.ImgURL as 'imgURL', al.albumName as 'Album Name', " +
    "ar.artistName as 'Artist Name', al.releaseDate as 'Release Date', g.genre as 'Genre' " +
    "FROM albums as al " +
    "INNER JOIN artists as ar " +
    "ON al.artist_id = ar.artistID " +
    "INNER JOIN genres as g " + 
    "ON al.genre_id = g.genreID " +
    "ORDER BY ";
    
    var fieldlist = ["albumAZ","albumZA","artistAZ","artistZA","genre","relDate","relDateRev"];
    
    if(fieldlist.indexOf(sortField) == -1){
        query = "error";
    } //End if
    else{
    //Use switch to determine appropriate query
        switch(sortField){
            case "albumAZ":
                query = q + "al.albumName, ar.artistName;";
                break;
            case "albumZA":
                query = q + "al.albumName DESC, ar.artistName DESC;";
                break;
            case "artistAZ":
                query = q + "ar.artistName, al.albumName;";
                break;
            case "artistZA":
                query = q + "ar.artistName DESC, al.albumName DESC;";
                break;
            case "genre":
                query = q + "g.genre, ar.artistName, al.albumName;";
                break;
            case "relDate":
                query = q + "al.releaseDate, ar.artistName, al.albumName;";
                break;
            case "relDateRev":
                query = q + "al.releaseDate DESC, ar.artistName DESC, al.albumName DESC;";
                break;
            default:
                query = q + "ar.artistName, al.albumName;";
                break;
        }//End switch
    }//End else
    
    return query;

}//End getQ function