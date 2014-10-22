<div id="comiceasel-resources">
		<div class="comiceasel-options">
				<?php 
					$got_file = wp_remote_get( 'http://frumph.net/downloads/index.html' );
					if (isset($got_file['body']) && !empty($got_file['body'])) echo $got_file['body'];
				?>
			<br />
		</div>
</div>
