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
                action: '<?php echo ceo_pluginfo('plugin_url') ?>/functions/uploader.php'
            });           
        }
        
        // in your app create uploader as soon as the DOM is ready
        // don't wait for the window to load  
        window.onload = createUploader;     
    </script>  
