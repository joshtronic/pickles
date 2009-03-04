<?php

/**
 * RSS Display Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * RSS Display
 *
 * Displays data in RSS version 2.0 format.
 *
 * @link http://cyber.law.harvard.edu/rss/rss.html
 * @todo Need to add support for RSS v1.0 as well as ATOM feeds.  This may
 *       result in my abstracting out these classes a bit more (Probably a
 *       Feed viewer that would take a parameter to determine which type of
 *       of feed to use).
 */
class Display_RSS extends Display_Common {

	/**
	 * Render the RSS feed data
	 *
	 * Uses a combination of configuration options and a properly formatted data
	 * array to create an RSS v2.0 feed.
	 *
	 * @todo Error handling is non-existant.
	 */
	public function render() {
		if (isset($this->data->channel)) {
			$channel = $this->data['channel'];
			$channel = $this->config->rss->$channel;

			if (isset($this->data->items)) {
				$items = $this->data['items'];
			}
			else {
				$this->error->addError('No items were provided');
			}
		}
		else {
			$this->error->addError('No channel was specified');
		}

		header('Content-type: application/rss+xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		?>
		<rss version="2.0">
			<channel>
				<title><?=$channel->title;?></title>
				<link>http://<?=$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];?></link>
				<description><?=$channel->description;?></description>
				<?php
				if (isset($channel->image)) {
					?>
					<image>
						<url><?=$channel->image;?></url>
						<title><?=$channel->title;?></title>
						<link>http://<?=$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];?></link>
					</image>
					<?php
				}
				?>
				<category><?=$channel->category;?></category>
				<language><?=isset($channel->language) ? $channel->language : 'en-us';?></language>
				<generator version="pre-beta 0.0.0">PICKLES (http://phpwithpickles.org)</generator>
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
