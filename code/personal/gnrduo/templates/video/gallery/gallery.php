<div class="video-gallery">
    <div class="slideshow-container">
        <?= $slides ?>
        <!-- Next and previous buttons -->
        <a 
            class="prev" 
            role="button" 
            tabindex="0" 
            data-change-slide="-1"
            aria-label="Previous Video"
        >
            &#10094;
        </a>
        <a 
            class="next" 
            role="button"
            tabindex="0" 
            data-change-slide="1"
            aria-label="Next Video"
        >
            &#10095;
        </a>
    </div>
    <!-- The dots/circles -->
    <div class="center">
    <?= $dots ?>
    </div>
</div>