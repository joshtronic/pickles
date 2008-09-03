<?php

class Viewer_RSS extends Viewer_Common {

	public function display() {
		$config  = Config::getInstance();
		$data    = $this->model->getData();

		if (isset($data['channel'])) {
			$channel = $config->rss[$data['channel']];
			
			if (isset($data['items'])) {
				$items = $data['items'];
			}
			else {
				// Error - no items
			}
		}
		else {
			// Error - no channel specified
		}

		header('Content-type: application/rss+xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		?>
		<rss version="2.0">
			<channel>
				<title><?=$channel['title'];?></title>
				<link>http://<?=$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];?></link>
				<description><?=$channel['description'];?></description>
				<category><?=$channel['category'];?></category>
				<language><?=$channel['language'] ? $channel['language'] : 'en-us';?></language>
				<?php
				if (is_array($items)) {
					foreach ($items as $key => $item) {
						$date = date('r', strtotime($item['date']));

						if ($key == 0) {
							echo "<lastBuildDate>{$date}</lastBuildDate>";
						}
						?>
						<item>
							<title><?=$item['title'];?></title>
							<link><?=$item['link'];?></link>
							<description><![CDATA[<?=$item['description'];?>]]></description>
							<author><?=$item['author'];?></author>
							<pubDate><?=$date;?></pubDate>
							<guid><?=$item['guid'];?></guid>
						</item>
						<?php
					}
				}
				?>
			</channel>
		</rss>
		<?php
	}
}
?>
