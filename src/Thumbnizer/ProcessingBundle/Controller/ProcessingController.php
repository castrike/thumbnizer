<?php

namespace Thumbnizer\ProcessingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Thumbnizer\ProcessingBundle\ImageProcessor;

class ProcessingController extends Controller {
    public function indexAction() {
        return $this->render('ThumbnizerProcessingBundle:Processing:default.html.twig', array('message' => "Silence is golden!"));
    }

    /**
     *	processingWH
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $width Width to crop the image to.
     *	@param (int) $height heightto crop the image to.
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingWHAction($width, $height, $source) {
    	try {
    		$processor = new ImageProcessor($source);
    		$finalImage = $processor->resizeByWidthAndHeight($width,$height)->retrieveFinal();
    		$gmdate_expires = gmdate ('D, d M Y H:i:s', strtotime ('now +120  days')) . ' GMT';
			$gmdate_modified = gmdate ('D, d M Y H:i:s') . ' GMT';
    		$headers = array(
				'Content-Type' => $processor->getMime(),
				'Last-Modified'=> $gmdate_modified,
				'Cache-Control' => 'max-age=10368000, must-revalidate', //120 days
				'Expires' => $gmdate_expires,
			);
    		return new Response($finalImage, 200, $headers);	
    	} catch(\Exception $e) {
    		return new Response('<html><body>'.$e->getMessage().'</body></html>');	
    	} 
    }


    

    /**
     *	processingWHE
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $width Width to crop the image to.
     *	@param (int) $height heightto crop the image to.
     *	@param (string) $effect Effect to use
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingWHEAction($width, $height, $effect, $source) {
    	$effects = array("grayscale","negate","sepia","vintage");

    }

    /**
     *	processingP
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $percent percent to resize the image to.
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingPAction($percent, $source) {
    	try {
    		$processor = new ImageProcessor($source);
    		$finalImage = $processor->resizeByPercent($percent)->retrieveFinal();
    		$gmdate_expires = gmdate ('D, d M Y H:i:s', strtotime ('now +120  days')) . ' GMT';
			$gmdate_modified = gmdate ('D, d M Y H:i:s') . ' GMT';
    		$headers = array(
				'Content-Type' => $processor->getMime(),
				'Last-Modified'=> $gmdate_modified,
				'Cache-Control' => 'max-age=10368000, must-revalidate', //120 days
				'Expires' => $gmdate_expires,
			);
    		return new Response($finalImage, 200, $headers);	
    	} catch(\Exception $e) {
    		return new Response('<html><body>'.$e->getMessage().'</body></html>');	
    	}
    }
}