<?php
/*
Plugin Name: WLM Latest
Plugin URI: http://wikilovesmonuments.fr/
Description: Permet de voir les dernières images publiées dans le cadre de Wiki Loves Monuments
Version: 1.0.0
Author: <a href="http://ashtree.eu">Sylvain Boissel</a>
Author URI: http://ashtree.eu
*/
if (!class_exists("wlm_latest")) {
    class wlm_latest {
		var $adminOptionsName = 'wlm_latestAdminOptions';  
		
        // Constructor
		function wlm_latest() {            
		}
		
		function init() {
			$this->getAdminOptions();
		}

        
        function addHeaderCode() {
			wp_enqueue_style('wp_jschat', '/wp-content/plugins/wlm-latest/css/wlm-latest.css');
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
            
            switch($filetype) {
				case 'tiff':
					$prefix='lossy-page1-';
					$ext='.jpg';
					break;
				default:
					$prefix='';
					$ext='';
					break;
			}
            $url = "http://upload.wikimedia.org/wikipedia/commons/thumb/{$m1}/{$m2}/".$image.'/'.$prefix.$width.'px-'.$image.$ext;
            return $url;
		}
		
		function getURL($image) {
			$options = $this->getAdminOptions();
			$language=$options['language'];
			$url = "https://commons.wikimedia.org/wiki/File:".$image."?uselang=".$language;
			return $url;
		}
		
		function getLastPictures($width=120,$number=0,$fixedheight=0) {
			$height="";
			if ($fixedheight == 1) { $height="fixedheight";}
			$list ='';
			$options = $this->getAdminOptions();
			$category = urlencode($options['category_name']);
			if($number == 0) {
				$number =$options['pics_number'];
			}
			
			$url="https://toolserver.org/~ash_crow/lastpics/getlastpics.php?category=$category&last=$number";
			$objects = json_decode($this->remote_get_contents($url),true);
			foreach ($objects AS $pic) {
				$list.="<a href=\"".$this->getURL($pic['filename'])."\"><div class=\"thumbnail $height\"><img src=\"".$this->getThumbnail($width,$pic['filename'],$pic['filetype'])."\" /><span class=\"thumbLegend\">".__('by ','wlmlatest').$pic['author']."</span></div></a>";
			}
			return $list;
		}

		
		// Admin-related functions
		function getAdminOptions() {
			$wlm_latestAdminOptions = array(
				'category_name' => 'Images from Wiki Loves Monuments 2012',
				'pics_number' => '20',
				'width' => '120',
				'widget_width' => '200',
				'language' => 'en'
			);
			
			$wlm_latestOptions = get_option($this->adminOptionsName);
			if (!empty($wlm_latestOptions)) {
				foreach ($wlm_latestOptions as $key => $option)
					$wlm_latestAdminOptions[$key] = $option;
			}
			
			update_option($this->adminOptionsName, $wlm_latestAdminOptions);
			return $wlm_latestAdminOptions;
		}
		
		function printAdminPage() {
			$options = $this->getAdminOptions();
			if (isset($_POST['update_wlm_latestSettings'])) {
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
				_e('Updated settings', 'wlmlatest');
				print '</strong></p></div>';
			}
			include('php/admin_settings.php');
		}
		
		// Widget
		function widget_wlmlast($args) {
			extract($args);
			print $before_widget;
			print $before_title;
			_e('Last submitted pic:', 'wlmlatest');
			print $after_title;
			include('php/widget.php');
			print $after_widget;
		}
		
		function initWidget() {
			register_sidebar_widget(__('WLM last picture','wlmlatest'), array(&$this, 'widget_wlmlast'));
		}
		
		function shortcode_wlmlatest($attributes, $initialContent = '') {
			$options = $this->getAdminOptions();
			$content = "<p>".__('Latest submitted pictures:', 'wlmlatest')."</p>";
			$content.=$this->getLastPictures($options['width'],$options['pics_number'],1);
			
			return $content;
        }
    } // End of wlm_latest class
}
if (class_exists("wlm_latest")) {
    $inst_wlm_latest = new wlm_latest();
}

if (!function_exists("wlm_latest_ap")) {  
	function wlm_latest_ap() {
		global $inst_wlm_latest;
		if (!isset($inst_wlm_latest)) {
			return;
        }
        if (function_exists('add_options_page')) {
			add_options_page('WLM Latest', 'WLM Latest', 9, basename(__FILE__), array(&$inst_wlm_latest, 'printAdminPage'));  
        }
    }
}

if (isset($inst_wlm_latest)) {
    add_action('wp_head', array(&$inst_wlm_latest, 'addHeaderCode'), 1);
    //add_filter('the_content', array(&$inst_wlm_latest, 'addContent'));
    add_action('activate_wlm_latest/wlm_latest.php',  array(&$inst_wlm_latest, 'init'));
    add_action('admin_menu', 'wlm_latest_ap'); 
    add_action('plugins_loaded', array(&$inst_wlm_latest, 'initWidget'));  
}

if(function_exists('add_shortcode')) {
    add_shortcode('wlmlatest',array(&$inst_wlm_latest, 'shortcode_wlmlatest'));
}

load_plugin_textdomain('wlmlatest','/wp-content/plugins/wlm-latest/lang/');
?>