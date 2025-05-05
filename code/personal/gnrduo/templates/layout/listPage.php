<div id="mainContent">
    <div class="listPage">
        <div class="content-box">
            <div id="titleContainer" class="center my-20 my-md-30">
				<h1 tabindex="0"><?= $data['pageTitle'] ?></h1>
			</div>
            <div id="view"<?= (!empty($data['viewClasses']) ? ' class="' . $data['viewClasses'] . '"' : '' ) ?>>
                <?= $view; ?>
            </div>
        </div>
    </div>
</div>