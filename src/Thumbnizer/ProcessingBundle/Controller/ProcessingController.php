<?php

namespace Thumbnizer\ProcessingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

use Thumbnizer\ProcessingBundle\ImageProcessor;

class ProcessingController extends Controller {
	
	private function validateUrl($source) {
		$config = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/allowedurls.yml'));
		if(empty($config['urls'])) return true;
		if(strpos($source,implode("|",$config['urls']))!==FALSE) return true;
		return false;
	}

    public function indexAction() {
        return $this->render('ThumbnizerProcessingBundle:Processing:default.html.twig', array('message' => "Silence is golden!"));
    }

    /**
     *	processingWH
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $width Width to crop the image to.
     *	@param (int) $height height to crop the image to.
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingWHAction($width, $height, $source) {
    	try {
    		if(!$this->validateUrl($source)) throw new \Exception("Invalid Url");
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
     *  processingWHP
     *  Crop image to a given width or height proportionally and return the cropped version
     *  @author Cristobal Sepulveda <cris@castrike.com>
     *  @param (string) $dimension Dimension to crop the image to.
     *  @param (int) $value value to crop the image to.
     *  @param (string) $url url of the image
     *  @return (Response) Image Data to be displayed
     */
    public function processingWHPAction($dimension, $value, $source) {
        try {
        	if(!$this->validateUrl($source)) throw new \Exception("Invalid Url");
            $processor = new ImageProcessor($source);
            switch($dimension) {
                case 'width':
                    $width = $value;
                    $height = "auto";
                    break;
                case 'height':
                    $width = "auto";
                    $height = $value;
            }
            $finalImage = $processor->resizeByWidthOrHeight($width,$height)->retrieveFinal();
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
    	try {
    		if(!$this->validateUrl($source)) throw new \Exception("Invalid Url");
    		$processor = new ImageProcessor($source);
    		$finalImage = $processor->resizeByWidthAndHeight($width,$height)->addEffect($effect)->retrieveFinal();
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
     *	processingP
     *	Crop image to a given width and height and return the cropped version
     *	@author Cristobal Sepulveda <cris@castrike.com>
     *	@param (int) $percent percent to resize the image to.
     *	@param (string) $url url of the image
     *	@return (Response) Image Data to be displayed
     */
    public function processingPAction($percent, $source) {
    	try {
    		if(!$this->validateUrl($source)) throw new \Exception("Invalid Url");
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