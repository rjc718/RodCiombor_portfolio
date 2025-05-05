<div class="list-row-container">
        <div class="col-left">
            <div 
                class="event-date px-24 py-20 center" 
                tabindex="0" 
                aria-label="We have a show on <?= $month . '-' . $day . '-' . $year ?>"
            >
                <div class="pb-20"><?= $month ?></div>
                <div><?= $day ?></div>
            </div>
        </div>
        <div class="col-right">
            <?php if(!empty($imgSrc)){ ?>
                <div class="img-container">
                    <a href="<?= $imgSrc ?>" target="_blank" tabindex="-1">
                        <img 
                            src="<?= $imgSrc ?>" 
                            alt="<?= $title ?>"
                            height="165"
                            width="165"
                            class="ms-10"
                        >
                    </a>
                </div>
            <?php } ?>

            <div class="event-info">
                <div class="inner">
                    <div class="font-size-28 font-weight-bold lh-36 pb-5" tabindex="0">
                        <?= $title ?>
                    </div>
                    <div class="event-location" tabindex="0">
                        <?= $location ?>
                    </div>
                    <?php if(!empty($address)){ ?>
                        <div class="address-block">
                            <?php if(!empty($gmap_link)){ ?>
                                <a href="<?= $gmap_link ?>" target="_blank">
                                    <img 
                                        alt="Google Maps" 
                                        src="assets/img/map-icon.jpeg" 
                                        height="16" 
                                        width="16"
                                    >
                                    <?= $address ?>
                                </a>
                            <?php 
                                }
                                else{
                                    echo $address;
                                } 
                            ?>
                        </div>
                    <?php } ?>
                    <?php if(!empty($time)){ ?>
                        <div class="lh-one-half" tabindex="0">
                            Time: <?= $time ?>
                        </div>
                    <?php } ?>
                </div>
                <?php if(!empty($description)){ ?>
                    <div>
                        <span 
                            class="read-more-btn link-style" 
                            data-target="<?= $evtId ?>" 
                            tabindex="0" 
                            role="button" 
                            aria-label="Read More About This Event"
                            aria-pressed="false"
                        >
                            Read <span class="status">More</span>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="spcr"></div>
        <?php if(!empty($description)){ ?>
            <div 
                class="event-description hide" 
                data-evt-id="<?= $evtId ?>" 
                tabindex="0" 
                aria-expanded="false"
                role="menu"
            >
                <div class="inner">
                    <?= $description ?>
                </div>
            </div>
            <div class="spcr"></div>
        <?php } ?>
    </div> 