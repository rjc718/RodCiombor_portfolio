<div 
	id="imgGallery" 
	class="fw-<?= $width ?> fh-<?= $height ?>" 
	data-gallery-id="<?= $galleryId ?>"
>
	<input type="hidden" id="imgStr" value="<?= $imgList ?>">
	<input type="hidden" id="imgIndex" value="<?= $imgIndex ?>">
	<div class="pos-relative">
		<?= $images ?>
	</div>	
</div>