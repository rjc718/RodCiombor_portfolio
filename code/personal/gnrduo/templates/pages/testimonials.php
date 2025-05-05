<?php 
    if(empty($rows)){
?>
    <div class="list-row-container">
        <div class="empty-list pt-10 px-20" tabindex="0">
            Nobody has written a testimonial yet.
            <br>
            Please keep checking back for future updates!
        </div>
    </div>
<?php 
    } else{ 
        echo $rows;
    } 
?>


