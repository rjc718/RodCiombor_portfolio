<!--HTML content of details page-->

<?php

require '../model/database.php';
require 'format_functions.php';
?>

<div id="details-wrapper">
    
    <div id="details-header">
        <h1>Album Name: <?php echo $albumName ?></h1>
        <h2>Artist: <?php echo $artistName ?></h2>
    </div>

    <div id="details-content">
        <div class="fl_l pct_40">
            <img src=<?php echo "$imgPath$imgURL" ?> />
        </div>
        <div class="fl_r pct_60">
            <div id="details-info">
                <div class="fl_l pct_50">Genre: <?php echo $genre ?></div>
                <div class="fl_r pct_50">Release Date: <?php echo formatDate($releaseDate) ?></div>
            </div>
            <div id="detail-track-list">
                <h4 id="detail-tl-title">Track List:</h4>
                <ol>
                    <?php trackList($trackList) ?>
                </ol>
            </div>
        </div>
    </div>

    <div id="details-footer">
        <a href="index.php">Back</a>

    </div>

</div>

