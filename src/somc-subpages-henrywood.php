<?php
/*
Plugin Name: somc-subpages-henrywood
Plugin URI: https://github.com/henrywood/somc-subpages-henrywood/
Description: Creates a widget and shortcode for displaying the sub pages of the current page.
Author: HS
Version: 1.0
Author URI: henry.wood.dk@gmail.com
*/

class SomCSubPagesHENRYWOOD extends WP_Widget {

	const TRUNCATION_LEN = 20;

	private $pluginName;

	/*
	Constructor
	*/

	public function __construct() {

		$this->pluginName = dirname( plugin_basename(__FILE__));

		// Language
 		add_action('init', array($this, 'load_lang'));		

		// scripts
		add_action( 'wp_enqueue_scripts', array($this, 'reg_scripts'));
		add_action( 'widgets_init', array($this, 'load'));

		// stylesheet
		add_action('wp_print_styles', array($this, 'stylesheet'));

		// Add shortcode
		add_shortcode($this->pluginName, array($this, 'shortcode'));

		/* Widget settings */
		$widget_ops = array(	'classname'		=>	$this->pluginName,
								'description' 	=> 	__('Adds collapsible tree of sub-pages', $this->pluginName) 
							);

		/* Widget control settings */
		$control_ops = array();

		/* Create the widget */
		$this->WP_Widget($this->pluginName, __('SubPages', $this->pluginName), $widget_ops, $control_ops );

    	// Add shortcode support for widgets
    	//add_filter('somcsubpages-henrywood', 'do_shortcode');
	}

	/*
	Callbacks
	*/

	function load_lang() {

        load_plugin_textdomain($this->pluginName, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    }		

	function stylesheet() {

		// TODO: Bootstrap should come from CDN

		// Load bootstrap
		$styleURL = plugins_url('bootstrap/css/bootstrap.min.css', __FILE__); 
		$styleFile = WP_PLUGIN_DIR . '/'.$this->pluginName.'/bootstrap/css/bootstrap.min.css';
		
		if ( file_exists($styleFile) ) {

			wp_register_style($this->pluginName.'-style1', "$styleURL");
			wp_enqueue_style($this->pluginName.'-style1');
		}

		// Load custom styling
		$styleURL = plugins_url('style.css', __FILE__); 
		$styleFile = WP_PLUGIN_DIR . '/'.$this->pluginName.'/style.css';
		
		if ( file_exists($styleFile) ) {

			wp_register_style($this->pluginName.'-style2', "$styleURL");
			wp_enqueue_style($this->pluginName.'-style2');
		}

	}

	function load() {
		
		register_widget($this->pluginName);
	}

	function reg_scripts() {

		// Load JQUERY from core
  		wp_enqueue_script('jquery');

		// TODO: Bootstrap should come from CDN

		// Bootstrap
    	//wp_register_script( 'bootstrap', get_stylesheet_directory_uri() . '/bootstrap/js/bootstrap.min.js', array('jquery'), '3.0.3', true );
   	    wp_register_script( 'bootstrap', WP_PLUGIN_URL . '/'.$this->pluginName.'/bootstrap/js/bootstrap.min.js', array('jquery'), '3.3.5', true );
	    wp_enqueue_script( 'bootstrap' );
    }

	/*
	Widget
	*/

	public function widget( $args ) {

		global $post;

		extract( $args );

		// Get ID of current page
		$page_id= $post->ID;

		$title = __("SubPages", $this->pluginName);

		echo $before_widget;
		echo $before_title . $title . $after_title;

		$count=0;
		$this->display($page_id);
		
		echo $after_widget;
	}

	/*
	Internal API/methods
	*/

	protected function truncate($string, $length, $dots = "...") {

	    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
	}

	protected function pagesRecursive($parentId, $lvl){ 

		$args=array('child_of' => $parentId, 'parent' => $parentId);
	
		$pages = get_pages($args); 
	
		// Start panel-group
		echo '<div class="panel-group" id="subpages-menu'.$parentId.'" role="navigation">';

		if ($pages) {
		
			$lvl++;
		
			foreach ($pages as $idx => $page) {
			
				$sortIcon = ($idx === 0) ? '<span data-order="none" title="'.__('Sort', $this->pluginName).'" class="sort glyphicon glyphicon-sort"></span>' : '';

				$imageCode	= (has_post_thumbnail($page->ID)) ? '<img class="img-rounded img-responsive" src="'.get_post_thumbnail($page->ID).'">' : '';

				$subPages	= (get_pages(array('child_of' => $page->ID, 'parent' => $page->ID)));

				// Start panel
				echo '<div class="panel panel-default">';	// This is parent whose children we will be sorting

				if ($subPages) {

	        		echo '

		        			<div class="panel-heading has-children" data-name="'.$page->post_title.'"> 
								<a class="title-link" href="' . $page->guid . '"> 
		 
		        					<div class="row">
		             					<div class="col-md-1 col-xs-3 col-lg-3">
		               						'.$imageCode.'
		            					</div>
		            					<div class="col-md-10 col-xs-8 col-lg-8">
		         							<h3 class="panel-title">
												'.$this->truncate($page->post_title).' 
		         							</h3>
		            					</div>
		            					<div class="col-md-1 col-xs-1 col-lg-1">
		            						<div class="pull-right">
			            						'.$sortIcon.'
			            					</div>
		            					</div>
		     						</div>
								</a>

								<!-- ARROW ICON -->

								<a data-toggle="collapse" class="arrow collapsed" data-parent="#subpages-menu'.$parentId.'" href="#collapse' . $lvl . '">
		 							<div class="awesome-triangle"></div>
								</a>

							</div> 
		 					<div id="collapse'.$lvl.'" class="panel-collapse collapse">
		                    	<div class="panel-body">
	 								'.$this->pagesRecursive($page->ID, $lvl).'
		 						</div>
		 					</div>
		 			';

		 		} else {

		 			echo '

		 		        	<div class="panel-heading" data-name="'.$page->post_title.'"> 
								<a class="title-link" href="' . $page->guid . '"> 
		 
		        					<div class="row">
		             					<div class="col-md-1 col-xs-3 col-lg-3">
		               						'.$imageCode.'
		            					</div>
		            					<div class="col-md-10 col-xs-8 col-lg-8">
		         							<h3 class="panel-title">
												'.$this->truncate($page->post_title).' 
		         							</h3>
		            					</div>
		            					<div class="col-md-1 col-xs-1 col-lg-1">

		            						<div class="pull-right">
		            							'.$sortIcon.'
		            						</div>

		            					</div>
		     						</div>
								</a>
							</div> 
					';
		 		}

		 		// End panel
				echo '</div>';
			} 
		}

		// End panel-group
		echo '</div>';
	}

	protected function display($page_id) {

		$t = __('Sort', $this->pluginName);

		// Sorting code
		$SORT_CODE =<<<SORT

			<script>

				\$(document).ready(function() {

					// Set up sorting click handlers
					\$('.sort').click(function() {

						var clickHandler = this;

						// Find the parent panel
						var \$container = \$(this).parent().parent().parent().parent().parent().parent();

						// Get the *CURRENT* sort order (initially, this will be 'none')
						var order = \$(this).data('order');

						// Find the children that we need to sort
						\$children = \$container.children('div');

						// If *CURRENT* sort order is none (ie. unspecified) or desc, let's now sort the children *ASCENDING*
						if (order == 'none' || order == 'desc') {

							//---------------------------------
							// ASCENDING SORTING
							//---------------------------------

							\$children.sort(function(a,b){
				
								var an = a.getAttribute('data-name'),
								var bn = b.getAttribute('data-name');

								if(an > bn) {
									return 1;
								}
					
								if(an < bn) {
									return -1;
								}
								return 0;
							});
						}

						// If *CURRENT* sort order is asc (ie. ascending), let's now sort the children *DESCENDING*
						if (order == 'asc') {

							//---------------------------------
							// DESCENDING SORTING
							//---------------------------------

							\$children.sort(function(a,b){
				
								var an = a.getAttribute('data-name'),
								var bn = b.getAttribute('data-name');

								if(an > bn) {
									return -1;
								}
					
								if(an < bn) {
									return 1;
								}
								return 0;
							});
						}

						var newSortIconClass;

						// Flip the sort order and set new glyphicon
						if (order == 'asc') {
							order = 'desc';
							newSortIconClass = 'glyphicon-sort-by-alphabet-alt'
						} else {
							order = 'asc';
							newSortIconClass = 'glyphicon-sort-by-alphabet';
						}

						// Put $children back in the DOM in the newly sorted order
						\$children.detach().appendTo(\$container);

						// Remove and re-insert the SORT icon (by removing its parent)
						\$(this).parent().remove();

						// Find the last DIV under the first row under the first "panel-heading" child of \$container
						\$sortIconContainer = \$container.find(".panel-heading:first .row:first div:last");
						
						// Add the new sort icon to \$sortIconContainer
						\$sortIconContainer.append(
  							\$('<div/>')
    							.addClass("pull-right")
    								.append("<span/>")
      									.addClass("sort")
      									.addClass('glyphicon')
      									.addClass(newSortIconClass)
      									.attr('title', '$t')
      									.data('order', order)
      									.click(clickHandler)
      					);
					});
				});

			</script>
SORT;

		// Output the subpages
		echo '<div class="subpages-menu-container">';
		$this->pagesRecursive($page_id, 0);
		echo '</div>';

		// Output sort code
		echo $SORT_CODE;
	}
	
	/*
	Short Code handling
	*/
	public function shortcode($atts) {

		global $post;
		extract( $atts );

		// TODO: Support for attributes here

		// extract the attributes into variables
    	/*
    	extract(shortcode_atts(array(
        	'truncation_len' => 20,
        	'caption' => true,
    	), $atts));
		*/

		// Get ID of current page
		$page_id = $post->ID;

		// SomCSubPagesHENRYWOOD::display() uses echo so buffer the output using an output buffer
		// so that we can return the HTML output as a string instead
		ob_start();
		$this->display($page_id);
		$content = ob_get_contents();
		ob_end_clean();

		// Return the buffered content
		return $content;
	}

} //class

new SomCSubPagesHENRYWOOD();

/* vim: set tabstop=4 softtabstop=4 shiftwidth=4 noexpandtab autoindent: */