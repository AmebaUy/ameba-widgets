<?php 
namespace AmebaWidget\featuredPostList;
use \WP_Widget;
use \WP_Query;

class featuredPostListWidget extends WP_Widget {
     
    function __construct() {
        //call widget's metaboxes
        $callFplMetaboxes = new fplMetabox\fplMetaboxInit();
        parent::__construct(
            'featured-post-list',
            'Custom Post List',
            array(
                'classname'     => 'widget_featured-post-list',
                'description'   => 'Displays a custom list of post'
            )
        );
        $this->enqueues();
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueues') );
    }

    function enqueues(){
        wp_register_style( 'fpl-require-style', plugin_dir_url( __FILE__ ).'css/fpl_styles.css', array(), '1.0.0');
        wp_register_style( 'fpl-optional-style', plugin_dir_url( __FILE__ ).'css/fpl_styles_optional.css', array('fpl-require-style'), '1.0.0');

        wp_enqueue_style( 'fpl-optional-style' );
        wp_enqueue_style( 'fpl-require-style' );
    }

    function admin_enqueues(){
        wp_register_script( 'fpl-admin-script', plugin_dir_url( __FILE__ ).'js/fpl_admin-script.js', array('jquery') );
        wp_enqueue_script( 'fpl-admin-script' );
    }
     
    function form( $instance ) {
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

    ?>
    <?php  //get post types
     $PTArray = array();
        $allPostTypes = get_post_types();
        foreach ($allPostTypes as $select_post_type):
            $thisPTname = get_post_type_object($select_post_type)->label;
            $ptTaxArray = array();

            $excludePostTypes = array('attachment','revision','nav_menu_item');
            if(!in_array($select_post_type, $excludePostTypes)){
                $thisPTArray = array(
                    'postTypeSlug' => $select_post_type,
                    'postTypeName' => $thisPTname
                );
                array_push($PTArray, $thisPTArray);
            }
        endforeach; ?>
        <div class="fpl_form_wrapper">
            <p>
                <label>Title: </label>
                <input type="text" id="<?php echo $this->get_field_id( 'title' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" value="<?php echo  esc_attr( $instance['title'] ); ?>" />
            </p>
            <p>
                <label>Select Post Type: </label>
                <select id="<?php echo $this->get_field_id( 'postType' ) ?>" name="<?php echo $this->get_field_name( 'postType' ) ?>" class="widefat fpl_admin_pt_select">
                <?php foreach ($PTArray as $selPTData): ?>
                    <option value="<?php echo $selPTData['postTypeSlug']; ?>" <?php selected( $selPTData['postTypeSlug'], $instance['postType'], true ); ?>><?php echo $selPTData['postTypeName']; ?></option>
                <?php endforeach; ?>
                </select>
            </p>
             <p>
                <label>Select Post Category: </label>
                <select id="<?php echo $this->get_field_id( 'postCat' ) ?>" name="<?php echo $this->get_field_name( 'postCat' ) ?>" class="widefat fpl_admin_cat_select">
                    <option value="all" data-post-type="all" <?php selected( 'all', $instance['postCat'], true ); ?>>-- All --</option>
                <?php 
                foreach ($PTArray as $selPTData): 
                    $thisPtTax = get_object_taxonomies($selPTData['postTypeSlug']);
                    foreach ($thisPtTax as $singleTax):
                        if($singleTax != 'post_format'){
                            $thisTaxTerms = get_terms( $singleTax );
                            var_dump($singleTax);
                            foreach ($thisTaxTerms as $singleTerm) {
                                ?>
                                <option value="<?php echo $singleTax.'|-@taxCatSeparator@-|'.$singleTerm->slug; ?>" data-post-type="<?php echo $selPTData['postTypeSlug']; ?>" <?php selected( $singleTax.'|-@taxCatSeparator@-|'.$singleTerm->slug, $instance['postCat'], true ); ?>><?php echo $singleTerm->name ?></option>
                                <?php
                            }
                        }
                    endforeach;
                endforeach; 
                ?>
                </select>
            </p>
            <p>
                <label>Show Source: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showSource' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showSource' ) ?>" <?php if(!empty($instance['showSource']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showSource'] ); ?>" />
            </p>
            <p>
                <label>Show Date: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showDate' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showDate' ) ?>" <?php if(!empty($instance['showDate']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showDate'] ); ?>" />
            </p>
            <p>
                <label>Show Excerpt: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showExcerpt' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showExcerpt' ) ?>" <?php if(!empty($instance['showExcerpt']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showExcerpt'] ); ?>" />
            </p>
            <p>
                <label>Image Size: </label>
                 <select id="<?php echo $this->get_field_id( 'imgSize' ) ?>" name="<?php echo $this->get_field_name( 'imgSize' ) ?>" class="widefat">
                    <option value="medium" <?php selected( 'medium', $instance['imgSize'], true ); ?>>Medium</option>
                    <option value="small" <?php selected( 'small', $instance['imgSize'], true ); ?>>Small</option>
                </select>
            </p>
            <p>
                <label>Mask Image: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'imgMask' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'imgMask' ) ?>" <?php if(!empty($instance['imgMask']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['imgMask'] ); ?>" />
            </p>
            <script type="text/javascript">categorySelect_init();</script>
        </div>
    <?php
    }
     
    function update( $new_instance, $old_instance ) {    
        $old_instance['title'] = strip_tags( stripcslashes($new_instance['title']) );
        $old_instance['postType'] = strip_tags( stripcslashes($new_instance['postType']) );
        $old_instance['postCat'] = strip_tags( stripcslashes($new_instance['postCat']) );
        $old_instance['showSource'] = strip_tags( stripcslashes($new_instance['showSource']) );
        $old_instance['showDate'] = strip_tags( stripcslashes($new_instance['showDate']) );
        $old_instance['showExcerpt'] = strip_tags( stripcslashes($new_instance['showExcerpt']) );
        $old_instance['imgSize'] = strip_tags( stripcslashes($new_instance['imgSize']) );
        $old_instance['imgMask'] = strip_tags( stripcslashes($new_instance['imgMask']) );
        return $old_instance;   
    }
     
    function widget( $args, $instance ) {
        $categoryArray = explode('|-@taxCatSeparator@-|', $instance['postCat']);
        
        $outPut = '<section class="virt_post_list_widget">
                    <h2>'.$instance['title'].'</h2>';
        $outPut .= '<div class="virt_pl_title_separator">
                        <div class="virt_pl_title_dots"><span></span><span></span><span></span></div>
                        <div class="virt_pl_title_line"></div>
                    </div>';        
        $outPut .= '<ul class="virt_post_list">';
        switch ($categoryArray[0]) {
            case 'all':
                $args = array(
                    'post_type' => $instance['postType'],
                    'order_by' => 'date',
                    'order' => 'DESC'
                );
                break;
            
            default:
                $args = array(
                    'post_type' => $instance['postType'],
                    'tax_query' => array(
                        array(
                            'taxonomy' => $categoryArray[0],
                            'field'    => 'slug',
                            'terms'    => $categoryArray[1],
                        ),
                    ),
                    'order_by' => 'date',
                    'order' => 'DESC'
                );
                break;
        }

        $postQuery = new WP_Query($args);
        if($postQuery->have_posts()): while($postQuery->have_posts()): $postQuery->the_post();

            $postSource = get_post_meta( get_the_ID(), '_fplWidget_post_source', true );
            $postIcon = get_post_meta( get_the_ID(), '_fplWidget_post_icon', true );
            $defaultIcon = plugin_dir_url( __FILE__ ).'default_icon.png'; 
            $iconCLass = 'virt_pl_icon';
            $iconCLass .= (!empty($instance['imgMask']))? ' virt_pl_icon_mask': '';
            $iconCLass .= ($instance['imgSize']=='small')? ' virt_pl_icon_small': ' virt_pl_icon_medium';
            $outPut .= '<li><article>';
            
            $outPut .= (!empty($postIcon))? '<img src="'.$postIcon.'" class="'.$iconCLass.'"/>': '<img src="'.$defaultIcon.'" class="'.$iconCLass.'"/>';
            $outPut .='<div class="virt_pl_content"><h3><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';

            if($instance['postType'] != 'page'){
                $outPut .= (!empty($instance['showExcerpt']) && !empty(get_the_excerpt()) )? '<p>'.get_the_excerpt().'</p>': '';
            }

            $outPut .= '<div class="virt_pl_data">';
            $outPut .= (!empty($instance['showSource']) && !empty($postSource) )? '<span class="virt_pl_source">'.$postSource.'</span>': '';
            $outPut .= (!empty($instance['showSource']) && !empty($postSource) && !empty($instance['showDate']) )? '<span class="virt_pl_separator"> | </span>' : '';
            $outPut .= (!empty($instance['showDate']) )? '<span class="virt_pl_date">'.get_the_date().'</span>': '';
            $outPut .= '<div>';
            $outPut .= '</div></article></li>';

        endwhile; endif;
        wp_reset_postdata();

        $outPut .= '</ul>
                </section>';
        echo $outPut;

    }//end method
     
}


 ?>