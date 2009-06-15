{if is_array($module.posts)}
	{foreach from=$module.posts item=post}
		<h2>{$post.title}</h2>
		<div class="post-info">Posted by {mailto text=$post.posted_by address=$post.email encode='javascript'} on {$post.posted_at|date_format:"%A, %B %e, %Y"}</div>
		<div class="post-body">{$post.body}</div>
		<div class="tags">
			{if is_array($post.tags)}
				{foreach from=$post.tags item=tag name=tags}{if !$smarty.foreach.tags.first}, {/if}<a href="/category/{$tag}" onclick="alert('Not yet... soon.'); return false;">{$tag}</a>{/foreach}
			{/if}
		</div>
		<div class="comments"><a href="#" onclick="alert('Not yet... soon.'); return false;">{$post.comments} Comment{if $post.comments != 1}s{/if}</a></div>
	{/foreach}
	{if $module.page > 1}<div style="float: left"><a href="/{$module_name.0}/{$module.page-1}">&laquo; Newer</a></div>{/if}
	{if $module.page < $module.last}<div style="float: right"><a href="/{$module_name.0}/{$module.page+1}">Older &raquo;</a></div>{/if}
	<br style="clear: both" />
{else}
	<em>There are currently no posts.</em>
{/if}
