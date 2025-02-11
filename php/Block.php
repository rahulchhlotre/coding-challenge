<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block) {
		
		ob_start();

		$post_types = get_post_types(  [ 'public' => true ] );
		$class_name = $attributes['className'];

		$currentpost = intval($_GET['post_id']);
		if (! $currentpost ) {
			$currentpost = '';
		}

       		 echo '<div class="'.$class_name.'">';
		 	echo '<h2>Post Counts</h2>';
		 	echo '<ul>';
			foreach ( $post_types as $post_type_slug ) :
                $post_type_object = get_post_type_object( $post_type_slug  );
                $post_count = wp_count_posts($post_type_slug); 
                
				 echo '<li>There are ' . $post_count . ' ' .
					  $post_type_object->labels->name . '.</li>';
			endforeach;

			echo '</ul>';
			echo '<p>The current post ID is ' . $currentpost . '.</p>'; 

			$query = new WP_Query(  array(
				'post_type' => ['post', 'page'],
				'posts_per_page' => 6,
				'post_status' => 'any',
				'tag'  => 'foo',
                		'category_name'  => 'baz',
				'date_query' => array(
					array(
						'hour'      => 9,
						'compare'   => '>=',
					),
					array(
						'hour' => 17,
						'compare'=> '<=',
					),
				),
			));

			if ($query->found_posts ) : 
				$posts = 0;
				echo '<h2>5 posts with the tag of foo and the category of baz</h2>';
				echo '<ul>';
				 while ( $query->have_posts() && $posts < 5 ) { 
					if (get_the_ID() != $currentpost ){ $posts++; 
						the_title( '<li>', '</li>');
					} 
				} 
				echo '</ul>';
                		wp_reset_postdata(); 
		 	endif; 
			
		echo '</div>';
		return ob_get_clean();
	}
