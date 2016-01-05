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
            'Ameba - Custom Post List',
            array(
                'classname'     => 'widget_featured-post-list',
                'description'   => 'Displays a custom list of post'
            )
        );
        $this->register_layouts_enqueues();
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueues') );
    }

    function register_layouts_enqueues(){
        wp_register_style( 'fpl-layout-1', plugin_dir_url( __FILE__ ).'css/fpl_layout_1.css', array(), '1.0.0');
        wp_register_style( 'fpl-layout-2', plugin_dir_url( __FILE__ ).'css/fpl_layout_2.css', array(), '1.0.0');
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
                'limit' => '3',
                'layout' => '1',
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
            <p class="fpl_layout_all">
                <label>Title: </label>
                <input type="text" id="<?php echo $this->get_field_id( 'title' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" value="<?php echo  esc_attr( $instance['title'] ); ?>" />
            </p>
            <p class="fpl_layout_all">
                <label>Select Post Type: </label>
                <select id="<?php echo $this->get_field_id( 'postType' ) ?>" name="<?php echo $this->get_field_name( 'postType' ) ?>" class="widefat fpl_admin_pt_select">
                <?php foreach ($PTArray as $selPTData): ?>
                    <option value="<?php echo $selPTData['postTypeSlug']; ?>" <?php selected( $selPTData['postTypeSlug'], $instance['postType'], true ); ?>><?php echo $selPTData['postTypeName']; ?></option>
                <?php endforeach; ?>
                </select>
            </p>
            <p class="fpl_layout_all">
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
            <p class="fpl_layout_all">
                <label>Limit Query: </label>
                <input type="number" id="<?php echo $this->get_field_id( 'limit' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'limit' ) ?>" <?php if(!empty($instance['limit']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['limit'] ); ?>" />
            </p>
            <p class="fpl_layout_all">
                <label>Layout: </label>
                 <select id="<?php echo $this->get_field_id( 'layout' ) ?>" name="<?php echo $this->get_field_name( 'layout' ) ?>" class="widefat fpl_layout_select">
                    <option value="1" <?php selected( '1', $instance['layout'], true ); ?>>Layout 1</option>
                    <option value="2" <?php selected( '2', $instance['layout'], true ); ?>>Layout 2</option>
                </select>
            </p>
            <p class="fpl_layout_1_form_item">
                <label>Show Source: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showSource' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showSource' ) ?>" <?php if(!empty($instance['showSource']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showSource'] ); ?>" />
            </p>
            <p class="fpl_layout_1_form_item">
                <label>Show Date: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showDate' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showDate' ) ?>" <?php if(!empty($instance['showDate']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showDate'] ); ?>" />
            </p>
            <p class="fpl_layout_1_form_item">
                <label>Show Excerpt: </label>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'showExcerpt' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'showExcerpt' ) ?>" <?php if(!empty($instance['showExcerpt']) ){ echo 'checked'; } ?> value="<?php echo  esc_attr( $instance['showExcerpt'] ); ?>" />
            </p>
            <p class="fpl_layout_1_form_item">
                <label>Image Size: </label>
                 <select id="<?php echo $this->get_field_id( 'imgSize' ) ?>" name="<?php echo $this->get_field_name( 'imgSize' ) ?>" class="widefat">
                    <option value="medium" <?php selected( 'medium', $instance['imgSize'], true ); ?>>Medium</option>
                    <option value="small" <?php selected( 'small', $instance['imgSize'], true ); ?>>Small</option>
                </select>
            </p>
            <p class="fpl_layout_1_form_item">
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
        $old_instance['limit'] = strip_tags( stripcslashes($new_instance['limit']) );
        $old_instance['layout'] = strip_tags( stripcslashes($new_instance['layout']) );
        $old_instance['showSource'] = strip_tags( stripcslashes($new_instance['showSource']) );
        $old_instance['showDate'] = strip_tags( stripcslashes($new_instance['showDate']) );
        $old_instance['showExcerpt'] = strip_tags( stripcslashes($new_instance['showExcerpt']) );
        $old_instance['imgSize'] = strip_tags( stripcslashes($new_instance['imgSize']) );
        $old_instance['imgMask'] = strip_tags( stripcslashes($new_instance['imgMask']) );
        return $old_instance;   
    }
     
    function widget( $args, $instance ) {
        switch ($instance['layout']) {
            case '1':
                wp_enqueue_style( 'fpl-layout-1' );
                echo $this->output_layout_1($instance);
                break;
            case '2':
                wp_enqueue_style( 'fpl-layout-2' );
                echo $this->output_layout_2($instance);
                break;
            default:
                wp_enqueue_style( 'fpl-layout-1' );
                echo $this->output_layout_1($instance);
                break;
        }
    }//end method

    /**
     *  layout methods
     */
    function output_layout_1($instance){
        $categoryArray = explode('|-@taxCatSeparator@-|', $instance['postCat']);
        
        $outPut = '<section class="amb_post_list_widget">
                    <h2>'.$instance['title'].'</h2>';
        $outPut .= '<div class="amb_pl_title_separator">
                        <div class="amb_pl_title_dots"><span></span><span></span><span></span></div>
                        <div class="amb_pl_title_line"></div>
                    </div>';        
        $outPut .= '<ul class="amb_post_list">';
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
        if($postQuery->have_posts()): while($postQuery->have_posts()): 
            $postQuery->the_post();
            $postQuery->post_count = intval($instance['limit']);

            $postSource = get_post_meta( get_the_ID(), '_fplWidget_post_source', true );
            $postIcon = get_post_meta( get_the_ID(), '_fplWidget_post_icon', true );
            $defaultIcon = plugin_dir_url( __FILE__ ).'default_icon.png'; 
            $iconCLass = 'amb_pl_icon';
            $iconCLass .= (!empty($instance['imgMask']))? ' amb_pl_icon_mask': '';
            $iconCLass .= ($instance['imgSize']=='small')? ' amb_pl_icon_small': ' amb_pl_icon_medium';
            $outPut .= '<li><article>';
            
            $outPut .= (!empty($postIcon))? '<img src="'.$postIcon.'" class="'.$iconCLass.'"/>': '<img src="'.$defaultIcon.'" class="'.$iconCLass.'"/>';
            $outPut .='<div class="amb_pl_content"><h3><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';

            if($instance['postType'] != 'page'){
                $outPut .= (!empty($instance['showExcerpt']) && !empty(get_the_excerpt()) )? '<p>'.get_the_excerpt().'</p>': '';
            }

            $outPut .= '<div class="amb_pl_data">';
            $outPut .= (!empty($instance['showSource']) && !empty($postSource) )? '<span class="amb_pl_source">'.$postSource.'</span>': '';
            $outPut .= (!empty($instance['showSource']) && !empty($postSource) && !empty($instance['showDate']) )? '<span class="amb_pl_separator"> | </span>' : '';
            $outPut .= (!empty($instance['showDate']) )? '<span class="amb_pl_date">'.get_the_date().'</span>': '';
            $outPut .= '<div>';
            $outPut .= '</div></article></li>';

        endwhile; endif;
        wp_reset_postdata();

        $outPut .= '</ul>
                </section>';
        return $outPut;
    }//end layout 1 method

    function output_layout_2($instance){
        $categoryArray = explode('|-@taxCatSeparator@-|', $instance['postCat']);
        
        $outPut = '<section class="fpl_layout_2_wrapper">';      
        $outPut .= '<ul>';
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
        if($postQuery->have_posts()): while($postQuery->have_posts()): 
            $postQuery->the_post();
            $postQuery->post_count = intval($instance['limit']);
            
            $postThumb = wp_get_attachment_url( get_post_thumbnail_id( get_the_id() ) );
            $postIcon = get_post_meta( get_the_ID(), '_fplWidget_post_icon', true );
            $defaultIcon = plugin_dir_url( __FILE__ ).'default_icon.png'; 
            $thumbToPrint = '';
            
            if(!empty($postThumb)){
                $thumbToPrint = $postThumb;
            }else if(!empty($postIcon)){
                $thumbToPrint = $postIcon;
            }else{
                $thumbToPrint = $defaultIcon;
            }

            $outPut .= '<li><a href="'.get_permalink().'"><div style="background-image: url('.$thumbToPrint.');"></div></a></li>';

        endwhile; endif;
        wp_reset_postdata();

        $outPut .= '</ul>
                </section>';
        return $outPut;
        return $outPut;
    }//end layout 2 method
     
}


 ?>