<iframe 
    <?= !empty($classList) ? 'class="' . $classList .  '" ' : '' ?>
    src="<?= $src ?>" 
    width="<?= $width ?>" 
    height="<?= $height ?>" 
    frameborder="0" <?= $extraParams ?>
>
</iframe>