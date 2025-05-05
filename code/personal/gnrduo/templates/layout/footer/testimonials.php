<div id="footerTestimonial" class="font-weight-bold italic font-size-20">
    <div class="content-box">
        <div class="inner">
            <div tabindex="0" class="text my-24"><?= $text ?></div>
            <div tabindex="0" class="right-align mt-14">- <?= $customer_name ?></div>
            <div tabindex="0" class="right-align"><?= $location ?></div>

            <?php if(!empty($sub_text)){?>
                <div tabindex="0" class="right-align"><?= $sub_text ?></div>    
            <?php } ?>
            <?php if(!empty($sub_text2)){?>
                <div tabindex="0" class="right-align"><?= $sub_text2 ?></div>    
            <?php } ?>
            <div tabindex="0" class="right-align mb-10"><?= $date ?></div>
            <div class="stars right-align mb-24" tabindex="0">
                <?php for($i=0; $i<5; $i++){ ?>   
                    <img 
                        src="assets/img/icons/star.svg" 
                        alt="Rated 5 out of 5 stars" 
                        height="25" 
                        width="25" 
                        class="ms-5"
                    >
                <?php } ?> 
            </div>
        </div>
    </div>
</div>