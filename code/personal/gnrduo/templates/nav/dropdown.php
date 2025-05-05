<div class="dropdown" aria-label="Navigation Menu" role="navigation">
    <button class="dropbtn py-14 px-24 lh-24 font-size-22" data-link="0" aria-expanded="false">
        <?= $title ?>
        <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content pos-absolute">
        <div class="float-left">
            <?php
                $itemCount = 1; 
                for($i=0; $i<sizeof($sublinks); $i++){
                    echo $sublinks[$i];
                    $itemCount++;
                    if($itemCount > 4){
                        $itemCount = 1;
                        echo '</div>';
                        echo'<div class="float-left">';
                    }
                } 
            ?>
        </div>
    </div>
</div>