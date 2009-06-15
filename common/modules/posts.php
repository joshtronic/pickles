<?php

class posts extends Module {

	public function __default() {

		// Sets up the pagination variables (5 per page)
		$page   = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$offset = $page > 1 ? ($page - 1) * 5 : 0;

		// Sets up the first (current) and last page numbers
		$this->setPublic('page',  $page);
		$this->setPublic('last',  $this->db->getField('SELECT COUNT(post_id) / 5 FROM posts ' . $where . ';'));

		// Constructions additional WHERE logic for non logged in users
		if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
			$where = '';
			$this->setPublic('admin', true);
		}
		else {
			$where = 'WHERE posted_at <= NOW() AND hidden IS FALSE';
			$this->setPublic('admin', false);
		}

		// Pulls the posts
		$posts = $this->db->getArray('
			SELECT p.post_id, p.title, p.body, p.tags, p.posted_at, p.hidden, u.name AS posted_by, u.email, COUNT(c.comment_id) AS comments
			FROM posts AS p
			LEFT JOIN users AS u ON u.user_id = p.user_id
			LEFT JOIN comments AS c ON c.post_id = p.post_id AND c.approved IS TRUE
			' . $where . '
			GROUP BY p.post_id
			ORDER BY posted_at DESC
			LIMIT ' . $offset . ', 5;
		');

		// Pulls all of the tags
		$all_tags = array();
		$tags     = $this->db->getArray('SELECT tag_id, name FROM tags;');
		foreach ($tags as $tag) {
			$all_tags[$tag['tag_id']] = $tag['name'];
		}

		// Loops through the posts and translates the tags
		foreach ($posts as $post_id => $post) {
			$post_tags = array();

			if (strpos($post['tags'], ',') !== false) {
				$tags = explode(',', $post['tags']);

				if (is_array($tags)) {
					foreach ($tags as $tag_id) {
						$post_tags[] = $all_tags[$tag_id];
					}
				}
			}

			$posts[$post_id]['tags'] = $post_tags;
		}

		// Passes the posts to the display class
		$this->setPublic('posts', $posts);
	}
}

?>
