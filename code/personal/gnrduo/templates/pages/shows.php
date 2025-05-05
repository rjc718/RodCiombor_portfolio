<div class="center pb-20">
    <span 
        class="date-range-toggle active link-style" 
        data-range="0" 
        tabindex="0"
        role="button"
        aria-label="View Upcoming Shows"
        aria-pressed="true"
    >
        Upcoming Shows
    </span>
    <span 
        class="date-range-toggle link-style ms-24" 
        data-range="1"
        tabindex="0"
        role="button"
        aria-label="View Past Shows"
        aria-pressed="false"
    >
        Past Shows
    </span>
</div>
<div id="yearList" class="center pb-20 hide">
    <?= $yearRows ?>
</div>

<div id="upcomingShows">
    <?php if(sizeof($upcomingShowList) == 0){ ?>
        <div class="list-row-container">
            <div class="empty-list pt-10 px-20" tabindex="0">
                We currently have no shows booked at this time.
                <br>
                Please keep checking back for future updates!
            </div>
        </div>
    <?php 
        } else{
            foreach($upcomingShowList as $row){
                echo $row;
            }    
        } 
    ?>
</div>
<div id="pastShows" class="hide">
    <?php 
        $i=0;
        foreach($pastShowLists as $k => $v){
            echo '<div data-year="' . $k . '" class="year-range' . ($i == 0 ? '' : ' hide') . '">';
            foreach($v as $row){
                echo $row;
            }
            echo '</div>';
            $i++;
        }
    ?>
</div>