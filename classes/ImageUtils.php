<?php

class ImageUtils {

	static function check($type, $types = array('image/jpeg', 'image/gif', 'image/png')) {
		if (!is_array($types)) {
			$types[0] = $types;
		}

		return in_array($type, $types);
	}

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
