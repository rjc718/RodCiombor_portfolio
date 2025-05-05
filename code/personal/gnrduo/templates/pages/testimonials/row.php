<div class="list-row-container italic">
    <div class="pt-0 pt-sm-20 px-0 px-md-20 mb-30" tabindex="0">
        <?php if(!empty($imgSrc)){ ?>
            <img 
                class="mb-10 me-26" 
                width="300" 
                src="assets/img/testimonials/<?= $imgSrc ?>" 
                alt="<?= $date ?> Testimonial Picture"
            >
        <?php } ?>
        
        <?= $text ?>
        
    </div>
    <div class="right-align" tabindex="0">- <?= $customerName ?></div>
        <?php if(!empty($subText)){ ?>
            <div class="right-align" tabindex="0"><?= $subText ?></div>
    <?php } ?>
    <?php if(!empty($subText2)){ ?>
            <div class="right-align" tabindex="0"><?= $subText2 ?></div>
    <?php } ?>
    <?php if(!empty($location)){ ?>
        <div class="right-align" tabindex="0"><?= $location ?></div>
    <?php } ?>
    <div class="right-align" tabindex="0"><?= $date ?></div>
    <div class="stars" tabindex="0">
        <?php for($i=0; $i<5; $i++){ ?>   
            <img 
                src="assets/img/icons/star.svg" 
                alt="Rated 5 out of 5 stars" 
                height="25" 
                width="25"
            >
        <?php } ?> 
    </div>
    <div class="spcr"></div>
</div> 