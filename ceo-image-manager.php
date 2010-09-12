<div class="wrap">
<h2><?php _e('Comic Easel - Image Manager','comiceasel'); ?></h2>
</div>
<div id="file-uploader-demo1">		
		<noscript>			
			<p>Please enable JavaScript to use file uploader.</p>
			<!-- or put a simple form for upload here -->
		</noscript>         
</div>

<script>        
	function createUploader(){            
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-demo1'),
			action: '<?php echo admin_url( 'admin-ajax.php'  ); ?>',
			params: {
				action: 'ceo_uploader'
			}
		});           
	}
	// in your app create uploader as soon as the DOM is readyi
	// don't wait for the window to load  
	window.onload = createUploader;     
</script>  
