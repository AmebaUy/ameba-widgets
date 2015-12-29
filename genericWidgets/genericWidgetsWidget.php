<?php
namespace AmebaWidget\genericWidgets;
use \WP_Widget;
use \WP_Query;

class genericWidgetsWidget extends WP_Widget {

    function __construct() {
        //call widget's metaboxes
        $callFplMetaboxes = new Metaboxes\genericMetaboxInit();
        parent::__construct(
            'generic-widget',
            'Generic Widget',
            array(
                'classname'     => 'widget_generic-class',
                'description'   => 'Displays generic Widget'
            )
        );
        $this->enqueues();
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueues') );
    }

    function enqueues(){
        // wp_register_style( 'require-style', plugin_dir_url( __FILE__ ).'css/fpl_styles.css', array(), '1.0.0');
        // wp_register_style( 'optional-style', plugin_dir_url( __FILE__ ).'css/fpl_styles_optional.css', array('fpl-require-style'), '1.0.0');

        // wp_enqueue_style( 'optional-style' );
        // wp_enqueue_style( 'require-style' );
    }

    function admin_enqueues(){
        // wp_register_script( 'admin-script', plugin_dir_url( __FILE__ ).'js/fpl_admin-script.js', array('jquery') );
        // wp_enqueue_script( 'admin-script' );
    }

    function form( $instance ) {

        // Example parse args
        $instance = wp_parse_args(
            (array)$instance,
            array(
                'title'      => '',
                'postType' => 'post',
                'postCat' => '',
                'showSource' => NULL,
                'showDate' => NULL,
                'showExcerpt' => NULL,
                'imgSize' => 'medium',
                'imgMask' => NULL
            )
        );

        //get post types
        // Html Example Form Widget

        ?><div class="esto-es-una-prueba"></div><?php
    }

    function update( $new_instance, $old_instance ) {
        $old_instance['title'] = strip_tags( stripcslashes($new_instance['title']) );

        return $old_instance;
    }

    function widget( $args, $instance ) {
        // HTML widget

    }//end method

}


 ?>
