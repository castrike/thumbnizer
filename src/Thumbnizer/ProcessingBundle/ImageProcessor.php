<?php
namespace Thumbnizer\ProcessingBundle;

use Symfony\Component\Yaml\Yaml;
use Aws\S3\S3Client;
use Guzzle\Http\Client;


class ImageProcessor {
	// (resource) original image
	private $image;
	// (resource) result image
	private $canvas;
	// (array) Hold image information
	private $imageInfo;

	/**
	 * Constructor
	 * Instantiate the image resource for the image we want to edit.
	 * If an image can't be retrieved or resource can't be created, throw an exception.
	 * @param (string)	$image_url	url/path of the image.
	 * @param (Array)	$aws 		AWS information ( signedurl, mime type and content length)
	 */
	public function __construct($image_url, $aws = '') {
		if(is_array($aws)) {
			$this->imageInfo = array();
	    	$this->imageInfo['src'] = $aws['signedurl'];
	        $this->imageInfo['mime'] = $aws['mime'];
	        $this->imageInfo['length'] = $aws['length'];
		} else {
			$image_url = urldecode($image_url);
			$headers = @get_headers($image_url,1);
	    	if(empty($headers) || !preg_match('/200/',$headers[0])) {
	    		throw new \Exception("Image was not found.");
	    	}
	    	$this->imageInfo = array();
	    	$this->imageInfo['src'] = $image_url;
	        $this->imageInfo['mime'] = $headers['Content-Type'];
	        $this->imageInfo['length'] = $headers['Content-Length'];		
		}
		switch($this->imageInfo['mime']) {
        	case 'image/jpeg': 
        		$this->image = @imagecreatefromjpeg($this->imageInfo['src']); 
        		break;
        	case 'image/png': 
        		$this->image = @imagecreatefrompng($this->imageInfo['src']); 
        		break;
        	case 'image/gif': 
        		$this->image  = @imagecreatefromgif($this->imageInfo['src']); 
        		break;
		}
		if(empty($this->image)) {
			throw new \Exception("Image object was not created.");
		}
	}

	/**
	 * resizeByWidthAndHeight
	 * Resize the image based on a width and height
	 *	@param (int) $width Width to crop the image to.
     *	@param (int) $height Height to crop the image to.
     *	@return (ImageProcessor) Return this object to enable method chaining.
	 */
	public function resizeByWidthAndHeight($newWidth, $newHeight) {
		// Check for invalid width and height values
		if(!is_numeric($newWidth) || !is_numeric($newHeight) || $newWidth < 0 || $newHeight < 0) {
			throw new \Exception("Invalid width/height parameters.");
		}
		// Retrieve original's image width and height.
		list($width,$height) = getimagesize($this->imageInfo['src']);
		// Compute dimensions
		$dimensions = array();
		// Who said that math wouldn't be helpful in real life?
		if($width/$height < $newWidth/$newHeight) {
	        $dimensions['width'] = $newWidth;
	        $dimensions['height'] = $newWidth * $height / $width;
	        $dimensions['x'] = 0;
	        $dimensions['y'] = ($dimensions['height']-$newHeight)/2;
		} else {
	        $dimensions['height'] = $newHeight;
	        $dimensions['width'] = $newHeight * $width / $height;
	        $dimensions['x'] = ($dimensions['width']-$newWidth)/2;
	        $dimensions['y'] = 0;
		}
		// Initialize canvas
		$this->canvas = imagecreatetruecolor ($newWidth, $newHeight);
		// Move data over
		imagecopyresampled($this->canvas, $this->image, $dimensions['x']*-1, $dimensions['y']*-1, 0, 0, $dimensions['width'], $dimensions['height'], $width, $height);
		return $this;
	}

	public function resizeByWidthOrHeight($newWidth,$newHeight) {
		if( !( ($newWidth == "auto" && is_numeric($newHeight) ) || 
			   ($newHeight == "auto" && is_numeric($newWidth) ) ) ) {
			throw new \Exception("Invalid width/height parameters.");
		}
		// Retrieve original's image width and height.
		list($width,$height) = getimagesize($this->imageInfo['src']);
		// Compute dimensions
		$dimensions = array();
		// I rather be dancing than doing math.
		switch($newWidth) {
			// Resize image by height
			case "auto":
				$newWidth = round($width * $newHeight / $height,2); 
				break;
			// Resize image by width
			default:
				$newHeight = round($height * $newWidth / $width,2); 
		}
		$this->canvas = imagecreatetruecolor ($newWidth, $newHeight);
		imagecopyresized($this->canvas, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		return $this;
	}

	public function resizeByPercent($percent) {
		if(!is_numeric($percent) || $percent < 0) {
			throw new \Exception("Invalid percent parameter.");	
		}
		// Convert percent to decimal
		$percent = $percent / 100;
		// Retrieve original's image width and height.
		list($width,$height) = getimagesize($this->imageInfo['src']);
		// Math again... compute new width and height values.
		$newWidth = $width * $percent;
		$newHeight = $height * $percent;
		// Initialize canvas
		$this->canvas = imagecreatetruecolor ($newWidth, $newHeight);
		imagecopyresized($this->canvas, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		return $this;
	}

	/**
	 * retrieveFinal
	 * Retrieve the final binaries of the resulting image.
	 * Controls the output buffer so that the image is not displayed.
	 * @return (string) Binary string with image data.
	 */
	public function retrieveFinal() {
		ob_start();
		switch($this->imageInfo['mime']) {
        	case 'image/jpeg': 
        		imagejpeg($this->canvas); 
        		break;
        	case 'image/png': 
        		imagepng($this->canvas); 
        		break;
        	case 'image/gif': 
        		imagegif($this->canvas); 
        		break;
		}
		$finalImage = ob_get_contents();
		ob_end_clean();
		return $finalImage;
	}

	public function addEffect($effect) {
		switch($effect) {
			case 'grayscale':
				imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
				return $this;
			case 'oldfashioned':
				imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
				imagegammacorrect($this->canvas, 1.0, 0.5);
				imagefilter($this->canvas, IMG_FILTER_COLORIZE, 49, 56, 45);
				imagefilter($this->canvas, IMG_FILTER_CONTRAST, 20);
				return $this;
			case 'sepia':
				imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
				imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, -20);
				imagefilter($this->canvas, IMG_FILTER_CONTRAST, -10);
				imagefilter($this->canvas, IMG_FILTER_COLORIZE, 80, 35, -10);
				return $this;
			case 'antique':
				imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, -15);
				imagefilter($this->canvas, IMG_FILTER_CONTRAST, -10);
				imagefilter($this->canvas, IMG_FILTER_COLORIZE, 85, 50, 25);
				return $this;
			case 'vintage':
				imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 15);
				imagefilter($this->canvas, IMG_FILTER_CONTRAST, -25);
				imagefilter($this->canvas, IMG_FILTER_COLORIZE, -10, -5, -15);
				imagefilter($this->canvas, IMG_FILTER_SMOOTH, 6);
				imagefilter($this->canvas, IMG_FILTER_MEAN_REMOVAL);
				return $this;
			case 'lomo':
				imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 0);
				imagefilter($this->canvas, IMG_FILTER_CONTRAST, -30);
				imagefilter($this->canvas, IMG_FILTER_COLORIZE, 85, 50, 25);
				return $this;
			default:
				throw new \Exception("Invalid effect to be applied.");	
		}
	}

	/**
	 * getMime
	 * Return the file type of the image.
	 * @return (string) Mime information about the image
	 */
	public function getMime() {
		return $this->imageInfo['mime']."";
	}

	/**
	 * Destructor
	 * Remove resources of the instances created.
	 */
	public function __destruct() {
		if(!empty($this->image)) {
			imagedestroy($this->image);
		}
		if(!empty($this->canvas)) {
			imagedestroy($this->canvas);
		}
	}

	private function hsv2rgb($h,$s,$v) { 
	if ($s==0) {
		return array($v,$v,$v); 
	} else { 
		$h=($h%=360)/60; 
		$i=floor($h); 
		$f=$h-$i; 
		$q[0]=$q[1]=$v*(1-$s); 
		$q[2]=$v*(1-$s*(1-$f)); 
		$q[3]=$q[4]=$v; 
		$q[5]=$v*(1-$s*$f); 
		//return(array($q[($i+4)%5],$q[($i+2)%5],$q[$i%5])); 
		return(array($q[($i+4)%6],$q[($i+2)%6],$q[$i%6])); //[1] 
	} 
} 
}