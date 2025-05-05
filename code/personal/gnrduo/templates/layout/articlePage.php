<div id="mainContent">
    <div class="articlePage">
        <div class="content-box">
            <?php if(!empty($data['mainImgSrc'])){ ?>
                <div id="mainImgContainer" class="mt-40">
                    <img 
                        src="assets/img/page-banners/<?= $data['mainImgSrc'] ?>" 
                        alt="Main Banner Image - <?= $data['pageTitle'] ?>"
                    >
                </div>
            <?php } ?>
            <div 
                id="titleContainer" 
                class="center <?= !empty($data['mainImgSrc']) ? 'pt-md-10 my-20' : 'pt-10 my-20 my-md-30' ?>"
            >
				<h1 tabindex="0">
                    <?= $data['pageTitle'] ?>
                </h1>
			</div>
            <div id="view"<?= (!empty($data['viewClasses']) ? ' class="' . $data['viewClasses'] . '"' : '' ) ?>>
                <?= $view; ?>
                <div class="spcr"></div>
                <div class="mt-40 mb-60">
                    <?= $data['ctaButton'] ?>
                </div>
            </div>
        </div>
    </div>
</div>