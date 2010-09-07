<div class="wrap">
<h2><?php _e('Comic Easel - Upload','comiceasel'); ?></h2>
<form name="comicuploaderform">
<table class="widefat">
<thead>
	<tr>
	<th colspan="3"><?php _e('Comic Upload','comiceasel'); ?></th>
	</tr>
</thead>
<tr><td>Post Title <span style="color: #f00;font-size: 11px;">(Required)</span>:</td><td><input size="80" type="text" name="title" value="New Comic"></td></tr>
<tr><td>Content:</td><td><textarea type="test" name="content" style="height: 80px; width: 440px;" value=""></textarea></td></tr>
<tr><td>Publish Date <span style="color: #f00;font-size: 11px;">(Required)</span>:</td><td><input type="text" name="date" value="yyy-mm-dd"> Time: <input type="text" name="time" value="hh:mm:ss"></td></tr>
<tr><td>Tags:</td><td><input type="text" name="tags" value=" "></td></tr>
<tr><td>Chapter:</td><td><?php wp_dropdown_categories('taxonomy=chapters&hide_empty=0&orderby=slug'); ?></td></tr>
<tr>
<td colspan="2">
	<p>To upload a file, click on the button below. Selecting a file will use teh from data above to automaticly generate the comic post for the selected file.</p>
	
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
                action: '<?php echo ceo_pluginfo('plugin_url') ?>/functions/uploader.php',
				params: {
					userid: userSettings.uid
				},
				onSubmit: function(id, fileName){
					uploader.setParams({
						title:document.comicuploaderform.title.value,
						content:document.comicuploaderform.content.value,
						date:document.comicuploaderform.date.value,
						time:document.comicuploaderform.time.value,
						tags:document.comicuploaderform.tags.value,
						chapter:document.comicuploaderform.cat.value
					});
				},
				onComplete: function(id, fileName, responseJSON){
					document.comicuploaderform.reset()
				}
            });           
        }
        
        // in your app create uploader as soon as the DOM is ready
        // don't wait for the window to load  
        window.onload = createUploader;     
    </script> 
</td>
</tr>
</table>
</form>
</div>