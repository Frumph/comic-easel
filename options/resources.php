<div id="comiceasel-resources">
		<div class="comiceasel-options">
			<table class="widefat">
				<thead>
					<tr>
						<th colspan="3"><?php _e('Resources','comiceasel'); ?></th>
					</tr>
				</thead>
				<tr>
					<td>
				<?php 
					$got_file = wp_remote_get( 'http://frumph.net/downloads/webcomic-lists.html' );
					if (isset($got_file['body']) && !empty($got_file['body'])) echo $got_file['body'];
				?>
					</td>
				</tr>
			</table>
			<br />
		</div>
</div>
