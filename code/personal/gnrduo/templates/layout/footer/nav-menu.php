<div class="link_section">
    <div 
        class="section_title mobile hasMenus py-10 px-20 font-weight-bold font-size-24" 
        tabindex="0" 
        role="button" 
        aria-pressed="false"
    >
        <?= $title ?>
        <span class="plus_icon float-right">
            <img 
                src="assets/img/icons/icon-element-plus.svg" 
                alt = "Toggle Navigation Menu"
                height="20" 
                width="20" 
                class="fw-20 fh-20"
            >
        </span>
    </div>
    <div class="section_title desktop pb-10 font-weight-bold font-size-24" tabindex="0">
        <?= $title ?>
    </div>
    <div class="link_list">
        <?= $links ?>
    </div>
</div>
