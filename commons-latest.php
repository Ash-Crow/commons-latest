<?php
/*
Plugin Name: Commons Latest
Plugin URI: http://blog.ash.bzh/commons-latest/
Description: Shows as a gallery the latest pictures from a given Wikimedia Commons Category
Version: 1.0.0
Author: <a href="http://ashtree.eu">Sylvain Boissel</a>
Author URI: http://ashtree.eu
*/
if (!class_exists("com_latest")) {
    class com_latest {
		var $adminOptionsName = 'com_latestAdminOptions';  
		
        // Constructor
		function com_latest() {            
		}
		
		function init() {
			$this->getAdminOptions();
		}


        function addHeaderCode() {
			wp_enqueue_style('com_latest', '/wp-content/plugins/commons-latest/css/commons-latest.css');
		}

	function addContent($content = '') {
			return $content;
		}
		
	// Get remote file contents, preferring faster cURL if available
	function remote_get_contents($url) {
		if (function_exists('$this->curl_get_contents') AND function_exists('curl_init')) {
			return $this->curl_get_contents($url);
		} else {
			// A litte slower, but (usually) gets the job done
			ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)'); // Fix for the error 403
			return file_get_contents($url);
		}
	}

	function curl_get_contents($url) {
		// Initiate the curl session
		$ch = curl_init();
			
		// Set the URL
		curl_setopt($ch, CURLOPT_URL, $url);
			
		// Removes the headers from the output
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		// Return the output instead of displaying it directly
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Pretend to be a browser
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 6.0)');
		
		// Pretend to be a browser
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 6.0)');
		
		// Execute the curl session
		$output = curl_exec($ch);
		
		// Close the curl session
		curl_close($ch);
			
		// Return the output as a variable
		return $output;
	}

	function getThumbnail($width,$image,$filetype) {
            $image2 = ucfirst( str_replace( " " , "_" , $image)) ;
            $m = md5($image2);
            $m1 = substr($m,0,1);
            $m2 = substr($m,0,2);

	    
	    $prefix='';
            
            switch($filetype) {
				case 'svg+xml':
					$ext='.png';
					break;
				case 'tiff':
					$prefix='lossy-page1-';
					$ext='.jpg';
					break;
				default:
					$ext='';
					break;
			}
            $url = str_replace('"','%22',"http://upload.wikimedia.org/wikipedia/commons/thumb/{$m1}/{$m2}/".$image.'/'.$prefix.$width.'px-'.$image.$ext);

            return $url;
	}
		
	function getURL($image) {
		$image=str_replace('"','%22',$image);
		$options = $this->getAdminOptions();
		$language=$options['language'];
		$url = "https://commons.wikimedia.org/wiki/File:".$image."?uselang=".$language;
		return $url;
	}
	
	function getLastPictures($category,$width=120,$number=0,$fixedheight=0) {
		$height="";
		if ($fixedheight == 1) { $height="fixedheight";}
		$list ='';
		$category = urlencode($category);
		
		if($number == 0) {
			$number =$options['pics_number'];
		}
		
		$url="https://tools.wmflabs.org/ash-dev/lastpics/getlastpics.php?category=$category&last=$number";
		$objects = json_decode($this->remote_get_contents($url),true);

		if(empty($objects)) {
			$list=__('No image found. Please check if the category exists.','comlatest');
		} else {
			foreach ($objects AS $pic) {
				if(($width >= $pic['width'])&&($pic['filetype']!='svg+xml')) {
					$thumb_width = $pic['width']-1; //Cap the thumbnail width to the one of the picture itself
				} else { $thumb_width = $width; }
				$list.="<a href=\"".$this->getURL($pic['filename'])."\"><div class=\"thumbnail $height\"><img src=\"".$this->getThumbnail($thumb_width,$pic['filename'],$pic['filetype'])."\" /><span class=\"thumbLegend\">".__('by','comlatest').' '.$pic['author']."</span></div></a>";
			}
		}
		return $list;
	}

		
	// Admin-related functions
	function getAdminOptions() {
		$com_latestAdminOptions = array(
			'category_name' => 'Featured pictures on Wikimedia Commons',
			'pics_number' => '20',
			'width' => '120',
			'widget_width' => '200',
			'language' => 'en'
		);

		$com_latestOptions = get_option($this->adminOptionsName);
			if (!empty($com_latestOptions)) {
				foreach ($com_latestOptions as $key => $option)
					$com_latestAdminOptions[$key] = $option;
		}
			
		update_option($this->adminOptionsName, $com_latestAdminOptions);
		return $com_latestAdminOptions;
	}
		
	function printAdminPage() {
		$options = $this->getAdminOptions();
		if (isset($_POST['update_com_latestSettings'])) {
			if (isset($_POST['category_name'])) {
				$options['category_name'] = $_POST['category_name'];
			}
			if (isset($_POST['pics_number'])) {
				$options['pics_number'] = $_POST['pics_number'];
			}
			if (isset($_POST['width'])) {
				$options['width'] = $_POST['width'];
			}
			if (isset($_POST['widget_width'])) {
				$options['widget_width'] = $_POST['widget_width'];
			}
			if (isset($_POST['language'])) {
				$options['language'] = $_POST['language'];
			}
			update_option($this->adminOptionsName, $options);
				print '<div class="updated"><p><strong>';
				_e('Updated settings', 'comlatest');
				print '</strong></p></div>';
		}
		include('php/admin_settings.php');
	}
		
		// Widget
		function widget_comlast($args) {
			extract($args);
			print $before_widget;
			print $before_title;
			_e('Last submitted pic:', 'comlatest');
			print $after_title;
			include('php/widget.php');
			print $after_widget;
		}
		
		function initWidget() {
			register_sidebar_widget(__('Commons last picture','comlatest'), array(&$this, 'widget_comlast'));
		}
		
		function shortcode_comlatest($attributes, $initialContent = '') {
			$options = $this->getAdminOptions();

			if(!empty($attributes['category'])) {
				$category=$attributes['category'];
			} else {
				$category=$options['category'];
			}

			if( (!empty($attributes['width'])) && (is_numeric($attributes['width'])) && ($attributes['width'] > 0) ) {
				$width=$attributes['width'];
			} else {
				$width=$options['width'];
			}
				
			if((!empty($attributes['quantity']))&&(is_numeric($attributes['quantity']))&&($attributes['quantity'] > 0)) {
				$pics_number=$attributes['quantity'];
			} else {
				$pics_number=$options['pics_number'];
			}
			$content = "<p>".__('Latest submitted pictures:', 'comlatest')."</p>";
			$content.=$this->getLastPictures($category,$width,$pics_number,1);
			
			return $content;
        }
    } // End of com_latest class
}
if (class_exists("com_latest")) {
    $inst_com_latest = new com_latest();
}

if (!function_exists("com_latest_ap")) {  
	function com_latest_ap() {
		global $inst_com_latest;
		if (!isset($inst_com_latest)) {
			return;
        }
        if (function_exists('add_options_page')) {
			add_options_page('Commons Latest', 'Commons Latest', 9, basename(__FILE__), array(&$inst_com_latest, 'printAdminPage'));  
        }
    }
}

if (isset($inst_com_latest)) {
    add_action('wp_head', array(&$inst_com_latest, 'addHeaderCode'), 1);
    //add_filter('the_content', array(&$inst_com_latest, 'addContent'));
    add_action('activate_com_latest/com_latest.php',  array(&$inst_com_latest, 'init'));
    add_action('admin_menu', 'com_latest_ap'); 
    add_action('plugins_loaded', array(&$inst_com_latest, 'initWidget'));  
}

if(function_exists('add_shortcode')) {
    add_shortcode('commonslatest',array(&$inst_com_latest, 'shortcode_comlatest'));
}

load_plugin_textdomain('comlatest','/wp-content/plugins/commons-latest/lang/');
?>
