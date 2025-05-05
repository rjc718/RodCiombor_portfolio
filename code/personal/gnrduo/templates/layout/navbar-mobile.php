<div class="navbar-mobile">
    <div id="mob-header" class="fh-50">
        <button 
            id="menuButton" 
            class="py-5 px-10" 
            type="button" 
            aria-label="Click to toggle navigation menu" 
            aria-pressed="false"
        >
            <span class="bar1 fw-30 my-8"></span>
            <span class="bar2 fw-30 my-8"></span>
            <span class="bar3 fw-30 my-8"></span>
        </button>
    </div>
    <div id="mobileMenu" aria-label="Navigation Menu" role="navigation" aria-expanded="false">
        <a class="mob-menu-item py-10 px-24" href="">Home</a>
        <?php
            foreach($menus as $menu){
                echo $menu;
            }
        ?>
    </div>
</div>
