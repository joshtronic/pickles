<?php

class posts extends Module
{
	public function __default()
	{
		$post = new Post(true); // You pass true to tell it to pull everything, this can be modified to support pagination

		return array('posts' => $post->records);
	}
}

?>
