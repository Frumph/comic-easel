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
<tr>
	<td>Publish Date <span style="color: #f00;font-size: 11px;">(Required)</span>:</td>
	<td><input type="text" id="override-date" name="override-date" value="yyyy-mm-dd"></td>
</tr>
<tr>
	<td>Time:</td>
	<td><input type="text" id="override-time" name="override-time" value="hh:mm:ss"></td>
</tr>


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
</td>
</tr>
</table>
<script type="text/javascript">
	Calendar.setup({
		inputField: "override-date",
		ifFormat: "%Y-%m-%d",
		button: "override-date"
	});
</script>
</form>
</div>
