<?php

/**
 * Image Utilities Class File for PICKLES
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
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Incomplete collection of image manipulation utilities
 *
 * I'm not entirely sure if this class is being utilized anywhere, especially
 * since a lot of the functions are not finished, or var_dump() stuff.  The
 * functions are (or eventually will be) wrappers for ImageMagick commands.  The
 * other route would be to code it all in pure PHP as to not have to add another
 * dependency to PICKLES.  After checking the PHP documentation on image
 * processing, it seems there's an ImageMagick module that can utilized, that may
 * end up being the route that is taken.  Until then, this class is useless shit.
 *
 * @todo Make the class usable.
 */
class ImageUtils {

	/**
	 * Checks an image format
	 *
	 * This is basically just a wrapper for in_array().  The passed image type is
	 * checked against the passed array of types (else it will use the default
	 * list).
	 *
	 * @param  string $type The type of being being checked
	 * @param  array $types An array of valid image types, defaults to the common
	 *         web formats of jpg, gif and png
	 * @return boolean If the passed type is in the list of valid types
	 */
	static function check($type, $types = array('image/jpeg', 'image/gif', 'image/png')) {
		if (!is_array($types)) {
			$types[0] = $types;
		}

		return in_array($type, $types);
	}

	/**
	 * Moves an uploaded file
	 *
	 * Handles not only moving an uploaded file to another location, but removes
	 * the original file once it's been moved.  It's ashame that the function
	 * move_uploaded_file() just copies a file, and doesn't actually move it.
	 *
	 * @param string $origin The uploaded file that is to be moved
	 * @param string $destination The destination for the origin file
	 * @todo  This function needs to return the status of the move
	 * @todo  Currently if the move fails, the origin file will still be removed.
	 */
	static function move($origin, $destination) {
		move_uploaded_file($origin, $destination);
		imagedestroy($origin);
	}

	static function resize() {
		
	}

	static function convert($original, $destination, $keep_original = true) {
		var_dump('convert ' . $original . ' ' . $destination);

		var_dump( exec('convert ' . $original . ' ' . $destination) );
	}
	
	/*


        if ($_FILES['image']['type'] == 'image/jpeg') {
            $original = $directory . 'original.jpg';

            $source = imagecreatefromjpeg($original);
            list($width, $height) = getimagesize($original);

            $sizes = array('small' => 75, 'medium' => 150, 'large' => 500);
            foreach ($sizes as $name => $size) {
                $temp = imagecreatetruecolor($size, $size);
                imagecopyresampled($temp, $source, 0, 0, 0, 0, $size, $size, $width, $height);
                imagejpeg($temp, "{$directory}{$name}.jpg", 85);

                imagedestroy($temp);
            }
                
            imagedestroy($source);
			*/
}

?>
