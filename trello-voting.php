<?php
/*
Plugin Name: Trello voting
Description: Simple plugin for voting things in trello board
Version: 1.1
Author: Contrast Digital
Author URI: http://contrast.fi/en
License: GPL2
*/

if(!class_exists('TrelloVoting')) {

	class TrelloVoting {
		
		
		//Variable for parsedown class
		private $Parsedown;


		/**
		 * Add WordPress hooks, actions, shortcodes and include parsedown class
		 * @since  1.0
		 */
		public function __construct() {
			// Hooks
			register_activation_hook( __FILE__, array($this, 'activate'));

			// Actions
			add_action('init', array($this, 'init'));
			add_action('wp_enqueue_scripts', array($this, 'style_and_scripts'));
			add_action("wp_ajax_trello_vote", array($this, 'add_vote'));
			add_action("wp_ajax_nopriv_trello_vote", array($this, 'add_vote'));
			add_action("wp_ajax_trello_load_data", array($this, 'load_cards'));
			add_action("wp_ajax_nopriv_trello_load_data", array($this, 'load_cards'));

			// Shortcodes
			add_shortcode('trello_voting', array($this, 'voting'));
			add_shortcode('trello_voting_results', array($this, 'results'));

			// Filters
			add_filter('trello-parse-description', array($this, 'parse_description_filter'), '10');

			// Parsedown class
			include 'libraries/parsedown.php';
			$this->Parsedown = new Parsedown();
        } // end __construct


        /**
         * Add database table when user activates plugin
         * @since  1.0
         */
        public static function activate() {
			global $wpdb;
			$table_name = (is_multisite()) ? $wpdb->base_prefix.'trello_voting' : $wpdb->prefix.'trello_voting';

		    $sql = "CREATE TABLE $table_name (
		    	id int(11) NOT NULL AUTO_INCREMENT,
		    	card_id varchar(255) DEFAULT NULL,
		    	votes int(11) DEFAULT NULL,
		    	UNIQUE KEY id (id)
		    );";

		    require_once(ABSPATH.'wp-admin/includes/upgrade.php'); // otherwise we cannot use dbDelta function
		    dbDelta($sql); // add table only if it does not exist already
		} // end activate


		/**
		 * Load translations when plugin is used
		 * @since  1.0
		 */
		function init() {
			load_plugin_textdomain('trellovoting', false, dirname(plugin_basename(__FILE__)));
		}


		/**
		 * Add our stylesheets to page
		 * @since  1.0
		 */
		function style_and_scripts() {
			wp_enqueue_style('trellovoting-grid', plugins_url('includes/gridism.css', __FILE__));
			wp_enqueue_style('trellovoting-style', plugins_url('includes/style.css', __FILE__));
		}


		/**
		 * Show content for shortcode trello_voting_cards and same time first cards to vote
		 * @param  array  $atts atttributes from shortcode
		 * @return echo the content for shortcode
		 * @since  1.0
		 */
        function voting($atts) {
        	// Localize our javascript file and add it to the page
        	wp_register_script( "trello_vote_js", plugins_url('includes/trello_vote_js.js', __FILE__), array('jquery') );
   			wp_localize_script( 'trello_vote_js', 'trellovotingAjax', array(
   				'ajaxurl' => admin_url( 'admin-ajax.php' ),
   				'trellourl' => $atts['trello'],
   				'include_labels' => $atts['include_labels'],
   				'votingerr' => __('Error while registering your vote', 'trellovoting'),
   				'fetcherr' => __('New cards could not be fetched, please reload page', 'trellovoting'),
   			));
   			wp_enqueue_script( 'jquery' );
  			wp_enqueue_script( 'trello_vote_js' );

  			return $this->load_view('voting', array('results_url' => $atts['redirect']));
        } // end function voting


        /**
		 * Get data from Trello API and make it usable
		 * @return json array of all Trello cards
		 * @since  1.5
		 */
		function load_cards() {
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		    	$data = file_get_contents($_REQUEST['trellourl']);
				$data = json_decode($data, true);

				// Check that data is valid
				if($data === FALSE) {
					$return['status'] = false;
				} else {
					$cards = array();
					$include_labels = (!empty($_REQUEST['include_labels'])) ? explode('|', $_REQUEST['include_labels']) : array() ;

					foreach ($data['cards'] as $card) {
						if(!empty($card['labels'])) {
							foreach($card['attachments'] as $key => $attachment) {
								if($attachment['id'] == $card['idAttachmentCover']) {
									$img = $attachment['url'];
								}
							}

							$card_data = array(
								'id' 	=> $card['id'],
								'name' 	=> $card['name'],
								'desc'	=> apply_filters('trello-parse-description', $card['desc']),
								'image'	=> (!empty($img)) ? $img : plugin_dir_url( __FILE__ ).'includes/placeholder.png',
								'url'	=> $card['shortUrl'],
								'nonce' => wp_create_nonce('trello_vote_'.$card['id']),
							);

							foreach ($card['labels'] as $label) {
								if(in_array(strtolower($label['name']), $include_labels)) {
									$label_name = $label['name'];
									$cards[ $label_name ][] = $card_data;
								}
							}
						} // endif !empty($card['labels'])
					} // end foreach

					$return = array('status' => true, 'cards' => $cards);
				}

				// json encode and exit
				wp_send_json($return);
			} else {
		    	exit(__('No naughty business please', 'trellovoting'));
		   	}
		} // end function load_cards


        /**
         * Add vote for card
         * @since  1.0
         */
        function add_vote() {
			if (!wp_verify_nonce( $_REQUEST['nonce'], 'trello_vote_'.$_REQUEST['id'])) {
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			    	wp_send_json(array('status' => false));
				} else {
			    	exit(__('No naughty business please', 'trellovoting'));
			   	}
			}

			global $wpdb;
			$table_name = (is_multisite()) ? $wpdb->base_prefix.'trello_voting' : $wpdb->prefix.'trello_voting';			
			$result = $wpdb->get_row($wpdb->prepare("SELECT votes FROM $table_name WHERE card_id = %s", $_REQUEST['id']), ARRAY_A);

			// Check if card is already in database
			if(empty($result)) {
				$vote = $wpdb->insert($table_name, array('card_id' => $_REQUEST['id'], 'votes' => '1'), array('%s', '%d'));
			} else {
				$vote = $wpdb->update( $table_name, array('votes' => $result['votes']+1), array('card_id' => $_REQUEST['id']), array('%d'), array('%s'));
			}

			if(empty($vote)) {
				$return['status'] = false;
			} else {
				$return['status'] = true;
			}

		   	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		    	wp_send_json($return);
			} else {
		    	header("Location: ".$_SERVER["HTTP_REFERER"]);
		   	}
		}


		/**
		 * Show content for shortcode trello_voting_results
		 * @param  array $atts  attributes from shortcode
		 * @return echo the content for shortcode
		 * @since  1.0
		 */
		function results($atts) {
			global $wpdb;
			$table_name = (is_multisite()) ? $wpdb->base_prefix.'trello_voting' : $wpdb->prefix.'trello_voting';		
			$result_cards = $wpdb->get_results("SELECT card_id, votes FROM $table_name", OBJECT_K);

			$data = file_get_contents($atts['trello']);
			$data = json_decode($data, true);

			// Check that data is valid
			if($data === FALSE) {
				$return['status'] = false;
			} else {
				$return = array();
				$include_labels = (!empty($atts['include_labels'])) ? explode('|', $atts['include_labels']) : array() ;

				// Make Trello data usable
				foreach ($data['cards'] as $card) {
					if(!empty($card['labels'])) {

						$card_data = array(
							'id' 	=> $card['id'],
							'name' 	=> $card['name'],
							'url'	=> $card['shortUrl'],
							'votes' => @$result_cards[$card['id']]->votes,
						);

						foreach ($card['labels'] as $label) {
							if($card_data['votes'] != null && in_array(strtolower($label['name']), $include_labels)) {
								$label_name = $label['name'];
								$return[ $label_name ][] = $card_data;
							}
						}
					} // endif !empty($card['labels'])
				} // end foreach
			} // end if $data false
			
			// Sort array by votes
			function usortResults($a, $b) {
				return $b['votes'] - $a['votes'];
			}
			foreach ($return as $key => $label) {
				usort($label, 'usortResults');
				$return[$key] = $label;
			}

			return $this->load_view('results', array('labels' => $return));
		}

		/**
		 * Make view and add data to it
		 * @param  string $view view name 
		 * @param  array  $data data for view
		 * @return string       content of view
		 * @since  1.0
		 */
		function load_view($view, $data) {
			if(is_array($data)) {
				foreach($data as $key => $value) {
					${$key} = $value;
				}
			}

			// Check if theme wants to override view file with it's own
			$theme_dir = get_stylesheet_directory();
	        $plugin_dir = plugin_dir_path( __FILE__ );
			$view = (file_exists("{$theme_dir}/trello-voting/{$view}.php")) ? "{$theme_dir}/trello-voting/{$view}.php" : "{$plugin_dir}views/{$view}.php" ;

			ob_start();
			include $view;
			$result = ob_get_clean();
			return $result;
		}

		/**
		 * Modify markup syntax to html
		 * @param  string $string string to parse 
		 * @return string         parsed string
		 * @since  1.0
		 */
		function parse_description_filter($string) {
			$string = $this->Parsedown->text($string);
			return str_replace('<p><strong>Description</strong></p>', '', $string);
		}

	}
	new TrelloVoting();
} // endif !class_exists('TrelloVoting')

?>
