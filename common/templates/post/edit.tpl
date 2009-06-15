<script type="text/javascript" src="/static/contrib/fckeditor/fckeditor.js"></script>

<form method="post" action="/post/save">
	<dl class="post">
		<dt>Title:</dt>
		<dd><input type="text" name="title" value="{$module.post.title}" /></dd>
		<dt>Body:</dt>
		<dd><textarea name="body" rows="5" class="form_input">{$module.post.body}</textarea></dd>
		<dt>Date:</dt>
		<dd>{html_select_date time=$module.post.posted_at}</dd>
		<dt>Time:</dt>
		<dd>{html_select_time time=$module.post.posted_at use_24_hours=false}</dd>
		<dt>Hidden:</dt>
		<dd>
			<input type="radio" name="hidden" value="1" {if $module.post.hidden == 1} checked="checked"{/if} /> Yes
			<input type="radio" name="hidden" value="0" {if $module.post.hidden != 1} checked="checked"{/if} /> No
		</dd>
	</dl>
	<input type="hidden" name="id" value="{$module.post.post_id}" />
	<input type="reset" value="Reset" /> <input type="submit" value="Save Post" />
</form>

<script type="text/javascript">
	var oFCKeditor        = new FCKeditor('body');
	oFCKeditor.BasePath   = "/static/contrib/fckeditor/";
	oFCKeditor.ToolbarSet = "ThatGirlJen";
	oFCKeditor.Width      = "500px";
	oFCKeditor.Height     = "250px";
	oFCKeditor.ReplaceTextarea();
	console.debug(oFCKeditor);
</script>
