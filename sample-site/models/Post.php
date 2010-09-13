<?php

/**
 * Post Model
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package    PICKLES
 * @subpackage Sample Site
 * @author     Josh Sherman <josh@phpwithpickles.org>
 * @copyright  Copyright 2007-2010, Gravity Boulevard, LLC
 * @license    http://www.gnu.org/licenses/gpl.html GPL v3
 * @link       http://phpwithpickles.org
 */
class Post extends Model
{
	/**
	 * Table Name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $table = 'pickles_posts';

	/**
	 * Columns to Order By
	 *
	 * @access protected
	 * @var    string
	 */
	protected $order_by = 'posted_at DESC';
}

?>
