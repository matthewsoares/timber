<?php

	function get_twig($uri){
		require_once(TIMBER_URI.'/Twig/lib/Twig/Autoloader.php');
		Twig_Autoloader::register();
		if (is_array($uri)){
			$loaders = array();
			foreach($uri as $u){
				$loaders[] = new Twig_Loader_Filesystem($u.'/views/');
			}
			$loader = new Twig_loader_Chain($loaders);
		} else {
			$loader = new Twig_Loader_Filesystem($uri.'/views/');
		}
		$twig = new Twig_Environment($loader, array(
    		/*'cache' => TIMBER_URI.'/twig-cache',*/
			'debug' => false,
			'autoescape' => false
		));
		$twig->addExtension(new Twig_Extension_Debug());
		$twig->addFilter('resize', new Twig_Filter_Function('twig_resize_image'));
		$twig->addFilter('excerpt', new Twig_Filter_Function('twig_make_excerpt'));
		$twig->addFilter('print_r', new Twig_Filter_Function('twig_print_r'));
		$twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
		$twig->addFilter('tojpg', new Twig_Filter_Function('twig_img_to_jpg'));
		$twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
		$twig->addFilter('editable', new Twig_Filter_Function('twig_editable'));
		$twig->addFilter('cdn', new Twig_Filter_Function('twig_cdn'));

		$twig->addFilter('wp_head', new Twig_Filter_Function('twig_wp_head'));
		$twig->addFilter('wp_footer', new Twig_Filter_Function('twig_wp_footer'));

		
		return $twig;
	}

	function twig_wp_head(){
		wp_head();
	}

	function twig_wp_footer(){
		wp_footer();
	}

	function twig_cdn($path){
		return 'http://yourcdn.com'.$path;
	}

	function render_twig($filename, $data = array(), $render = true){
		/*
		$uri = TIMBER_URI;
		if (file_exists(THEME_URI.'/'.$filename)){
			$uri = THEME_URI;
		}
		*/
		if(!$data){
			$data = array();
		}
		$uri = TIMBER_URI;
		if (THEME_URI != TIMBER_URI){
			$uri = array();
			$uri[] = THEME_URI;
			$uri[] = TIMBER_URI;
		}
		$twig = get_twig($uri);
		$output = $twig->render($filename, $data);
		if ($render){
			echo $output;
		}
		return $output;
	}

	function twig_editable($content, $ID, $field){
		if (!function_exists('ce_wrap_content')){
			return $content;
		}		
		return ce_wrap_content_field($content, $ID, $field);
	}

	function twig_print_r($arr){
		return print_r($arr, true);
	}

	function twig_get_path($url){
		$url = parse_url($url);
		return $url['path'];
	}

	function twig_make_excerpt($text, $length = 55){
		return wp_trim_words($text, $length);
	}

	function twig_img_to_jpg($src){
		$output = str_replace('.png', '.jpg', $src);
		if (file_exists($_SERVER['DOCUMENT_ROOT'].$output)){
			return $output;
		}
		$image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].$src);
		$w = imagesx($image);
		$h = imagesy($image);
		$bg = imagecreatetruecolor($w, $h);
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $image, 0, 0, 0, 0, $w, $h);
    	imagejpeg($bg, '/'.$_SERVER['DOCUMENT_ROOT'].$output, 90);
    	imagedestroy($image);
    	return $output;
	}
	
	function twig_resize_image($src, $w, $h = 0, $ratio = 0, $append = ''){
		return get_resized_image($src, $w, $h, $ratio, $append);
	}

	