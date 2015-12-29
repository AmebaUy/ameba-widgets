<?php
namespace AmebaWidget\genericWidgets\Metaboxes;
class genericMetaboxInit{
	public $prefix = '_genericWidget_';
	public function __construct(){
		$this->addMetaboxes();
	}
	/**
	 *	add actions hooks
	 */
	public function addMetaboxes(){
		add_action( 'cmb2_admin_init', array($this, 'genericWidget_data') );
	}
	/**
	 * Metaboxes methods
	 */
	public function genericWidget_data() {

		$fplWidget_listing_data = new_cmb2_box( array(
			'id'            => $this->prefix . 'listing_data',
			'title'         => __( 'Listing Settings', 'cmb2' ),
			'object_types'  => array( 'post')
		) );

		$fplWidget_listing_data->add_field( array(
			'name'       => __( 'Post Icon', 'cmb2' ),
			'id'         => $this->prefix . 'post_icon',
			'type'       => 'file'
		) );

		$fplWidget_listing_data->add_field( array(
			'name'       => __( 'Post Source', 'cmb2' ),
			'id'         => $this->prefix . 'post_source',
			'description' => 'fill this field if you want to display the source of this post',
			'type'       => 'text'
		) );

	}


} //end class
