<div class="sub-menu">
    <div 
        class="sub-menu-btn py-10 px-24" 
        data-link="0" 
        tabindex="0" 
        role="button" 
        aria-pressed="false"
    >
        <?= $title ?>
        <span class="plus-icon float-right">
            <img 
                alt = "Toggle Navigation Menu"
                height="20" 
                width="20" 
                class="fw-20 fh-20 mt-10" 
                src="assets/img/icons/icon-element-plus-gray.svg"
            >
        </span>
    </div>
    <div class="sub-menu-content" role="menu" aria-expanded="false">
        <?php    
            for($i=0; $i<sizeof($sublinks); $i++){
                echo $sublinks[$i];
            } 
        ?>
    </div>
</div>
