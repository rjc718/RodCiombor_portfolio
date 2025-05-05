<div id="extraServicesModule" class="content-row module font-weight-bold center font-size-22" tabindex="0">
	<div class="content-box">
		<div class="top mb-20">
            In addition to those listed above,
            <?= $siteName ?> is able to provide music for many other types of occasions,&nbsp;including:
        </div>
		<div class="bottom">
            <?php 
				foreach($services as $s){
					echo $s . " &nbsp; • &nbsp;";
				}
			?>
			And Much More!
        </div>
		<ul class="left-align">
			<?php 
				foreach($services as $s){
					echo '<li>' . $s . '</li>';
				}
			?>
			<li>And Much More!</li>
		</ul>
	</div>
</div>