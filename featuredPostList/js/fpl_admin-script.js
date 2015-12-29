
function categorySelect_init(){
    jQuery('.fpl_admin_pt_select').each(function(){
        var thisSelect = jQuery(this);
        categorySelect(thisSelect);
    });

    jQuery('.fpl_admin_pt_select').change(function(){
        var thisSelect = jQuery(this);
        categorySelect(thisSelect);
        var catOptions = thisSelect.closest('.fpl_form_wrapper').find('.fpl_admin_cat_select').children('option');
        catOptions.removeAttr('selected');
    });
}
function categorySelect(thisSelect){
    var postTypeSelected = thisSelect.find("option:selected").val();
    var catOptions = thisSelect.closest('.fpl_form_wrapper').find('.fpl_admin_cat_select').children('option');
    catOptions.each(function(){
        var thisPostType = jQuery(this).data('post-type');
        if(thisPostType == postTypeSelected){
            jQuery(this).show();
        }else if(thisPostType != 'all'){
            jQuery(this).hide();
        }
    });

    
}