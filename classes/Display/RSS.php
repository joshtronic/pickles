<?php

/**
 * RSS Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
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
class Display_RSS extends Display_Common
{
	/**
	 * Render the RSS feed data
	 *
	 * Uses a combination of configuration options and a properly formatted data
	 * array to create an RSS v2.0 feed.
	 *
	 * @todo Error handling is non-existant.
	 */
	public function render()
	{
		if (isset($this->data->channel) || is_object($this->data['channel']))
		{
			$channel = $this->data['channel'];

			if (!is_object($this->data['channel']))
			{
				$channel = $this->config->rss->$channel;
			}

			if (isset($this->data->items))
			{
				$items = $this->data['items'];
			}
			else
			{
				$this->error->addError('No items were provided');
			}
		}
		else
		{
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
				if (isset($channel->image))
				{
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
				if (is_array($items))
				{
					foreach ($items as $key => $item)
					{
						$date = date('r', strtotime($item['date']));

						if ($key == 0)
						{
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
