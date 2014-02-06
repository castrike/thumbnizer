<?php

namespace Thumbnizer\ProcessingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProcessingController extends Controller {
    public function indexAction() {
        return $this->render('ThumbnizerProcessingBundle:Processing:default.html.twig', array('message' => "Silence is golden!"));
    }

    /**
     *	processingWHP
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $width Width to crop the image to.
     *	@param (int) $height heightto crop the image to.
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingWHPAction($width, $height, $source) {
    	$source = urldecode($source);
    	$query_width = (int) $width;
    	$query_height = (int) $height;
    	$headers = @get_headers($source,1);
    	if(empty($headers) || !preg_match('/200/',$headers[0])) 
    		return new Response('<html><body><pre>Image Not Found</body></html>');	

    	$imageInfo = array();
    	$imageInfo['src'] = $source;
        $imageInfo['mime'] = $headers['Content-Type'];
        $imageInfo['length'] = $headers['Content-Length'];

        /* Its Cropping time! */
		list($width,$height) = getimagesize($imageInfo['src']);
		$new_width =  (int) abs($query_width);
		$new_height = (int) abs($query_height);

		$dimensions = array();
		if($width/$height < $new_width/$new_height) {
		        $dimensions['width'] = $new_width;
		        $dimensions['height'] = $new_width * $height / $width;
		        $dimensions['x'] = 0;
		        $dimensions['y'] = ($dimensions['height']-$new_height)/2;
		} else {
		        $dimensions['height'] = $new_height;
		        $dimensions['width'] = $new_height * $width / $height;
		        $dimensions['x'] = ($dimensions['width']-$new_width)/2;
		        $dimensions['y'] = 0;
		}

		$gmdate_expires = gmdate ('D, d M Y H:i:s', strtotime ('now +120  days')) . ' GMT';
		$gmdate_modified = gmdate ('D, d M Y H:i:s') . ' GMT';

		switch($imageInfo['mime']) {
        	case 'image/jpeg': $image = @imagecreatefromjpeg($imageInfo['src']); break;
        	case 'image/png': $image = @imagecreatefrompng($imageInfo['src']); break;
        	case 'image/gif': $image  = @imagecreatefromgif($imageInfo['src']); break;
		}

		$canvas = imagecreatetruecolor ($new_width, $new_height);
		imagecopyresampled($canvas, $image, $dimensions['x']*-1, $dimensions['y']*-1, 0, 0, $dimensions['width'], $dimensions['height'], $width, $height);
		ob_start();
		switch($imageInfo['mime']) {
        	case 'image/jpeg': imagejpeg($canvas); break;
        	case 'image/png': imagepng($canvas); break;
        	case 'image/gif': imagegif($canvas); break;
		}
		$finalImage = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($image);
		imagedestroy($canvas);

		$headers = array(
			'Content-Type' => $imageInfo['mime'],
			'Last-Modified'=> $gmdate_modified,
			'Cache-Control' => 'max-age=10368000, must-revalidate', //120 days
			'Expires' => $gmdate_expires,
		);

		return new Response($finalImage, 200, $headers);
    }
}
