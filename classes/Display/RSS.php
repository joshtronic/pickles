<?php

/**
 * RSS Display Class File for PICKLES
 *
 * PHP version 5.3+
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * RSS Display
 *
 * Displays data as an RSS formatted XML string.
 */
class Display_RSS extends Display_Common
{
	// {{{ Feed Defaults

	/**
	 * RSS Version
	 *
	 * @access private
	 * @var    string
	 */
	private $version = '2.0';

	/**
	 * Date Format
	 *
	 * @access private
	 * @var    string
	 */
	private $date_format = 'r';

	// }}}

	// {{{ Channel Defaults

	/**
	 * Title
	 *
	 * @access private
	 * @var    string
	 */
	private $title = '';

	/**
	 * Link
	 *
	 * @access private
	 * @var    string
	 */
	private $link = '';

	/**
	 * Description
	 *
	 * @access private
	 * @var    string
	 */
	private $description = '';

	/**
	 * Language
	 *
	 * @access private
	 * @var    string
	 */
	private $language = 'en-us';

	/**
	 * Generator
	 *
	 * @access private
	 * @var    string
	 */
	private $generator = 'https://github.com/joshtronic/pickles';

	// }}}

	/**
	 * Renders the data in RSS format
	 */
	public function render()
	{
		// Throws off the syntax highlighter otherwise
		echo '<' . '?xml version="1.0" ?' . '><rss version="' . $this->version . '"><channel>';

		// Loops through the passable channel variables
		$channel = array();
		foreach (array('title', 'link', 'description', 'language') as $variable)
		{
			if (isset($this->module_return[$variable]))
			{
				$this->$variable = $this->module_return[$variable];
			}

			$channel[$variable] = $this->$variable;
		}

		$channel['generator'] = $this->generator;

		// Loops through the items
		$items      = '';
		$build_date = '';
		if (isset($this->module_return['items']) && is_array($this->module_return['items']))
		{
			foreach ($this->module_return['items'] as $item)
			{
				// Note: time is the equivalent to pubDate, I just don't like camel case variables
				$publish_date = date($this->date_format, is_numeric($item['time']) ? $item['time'] : strtotime($item['time']));

				if ($build_date == '')
				{
					$build_date = $publish_date;
				}

				if (isset($item['link']))
				{
					$item['guid'] = $item['link'];
				}

				$item['pubDate'] = $publish_date;

				unset($item['time']);

				$items .= Convert::arrayToXML($item);
			}
		}

		$channel['pubDate']       = $build_date;
		$channel['lastBuildDate'] = $build_date;

		echo Convert::arrayToXML($channel) . $items . '</channel></rss>';
	}
}

?>
