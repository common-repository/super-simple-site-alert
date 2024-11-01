<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?PHP
    $date = new DateTime();
    $timestamp = $date->getTimestamp();

    $site_id = get_current_blog_id();

    //get all sites
    $all_blogs = get_sites();

    foreach ($all_blogs as $key => $current_blog) {
        // switch to each blog to get the posts
        $blog_id = $current_blog->blog_id;
        switch_to_blog($blog_id);
        // fetch all the posts
        $alerts = get_posts(array('post_type' => 'site_alert'));
        $c = 0;
        foreach ($alerts as $alert) {
            $post_id = $alert->ID;
            $title = $alert->post_title;
            $message = $alert->post_content;

            $priority = get_post_meta($post_id, 'priority', true);
            $priority_color_border = "";
            $priority_color_border_popup = "";
            $priority_color_bg = "";
            $priority_color_text = "";
            switch ($priority) {
                case 1:
                    $priority_class = 'pl-low';
                    break;
                case 2:
                    $priority_class = 'pl-medium';
                    break;
                case 3:
                    $priority_class = 'pl-high';
                    break;
                case 4:
                    $priority_class = 'pl-emergency';
                    break;
                case 5:
                    $priority_class = 'pl-custom';
                default:
                    $priority_class = 'alert-secondary';
                    $priority_color_border = get_post_meta($post_id, 'priority_color_border', true);
                    $priority_color_border_popup = "2px solid $priority_color_border";
                    $priority_color_bg = get_post_meta($post_id, 'priority_color_bg', true);
                    $priority_color_text = get_post_meta($post_id, 'priority_color_text', true);
                    break;
            }

            $location = get_post_meta($post_id, 'location', true);
            $location_custom = get_post_meta($post_id, 'location_custom', true);

            $icon = get_post_meta($post_id, 'icon', true);
            switch ($icon) {
                case 2:
                    $icon_class = 'alert-icon-info';
                    break;
                case 3:
                    $icon_class = 'alert-icon-exclamation';
                    break;
                case 4:
                    $icon_class = 'alert-icon-gear';
                    break;
                case 1:
                default:
                    $icon_class = '';
                    break;
            }
            $option_all_pages = get_post_meta($post_id, 'allpages', true);
            $option_all_sites = get_post_meta($post_id, 'allsites', true);
            $excluded_sites = explode(',', get_post_meta($post_id, 'exclude', true));
            $option_is_static = get_post_meta($post_id, 'isstatic', true) == 1 ? 'isstatic' : '';
            $option_is_sticky_footer = get_post_meta($post_id, 'isstickyfooter', true) == 1 ? 'isstickyfooter' : '';
            $option_is_popup = get_post_meta($post_id, 'ispopup', true) == 1 ? 'ispopup' : '';
            $publish_date = get_post_meta($post_id, 'publish-date', true);
            $unpublish_date = get_post_meta($post_id, 'unpublish-date', true);
            //if alert belongs the site and not in the excluded list, show alert. Else, if alert does not belong the the site, but alert is set to show on all sites and not in exclude list, show alert
            if(($blog_id == $site_id && !in_array($site_id, $excluded_sites)) || (($blog_id !== $site_id) && $option_all_sites == 1 && !in_array($site_id, $excluded_sites))){
                if( ((!$option_all_pages && is_front_page()) || $option_all_pages) && ($publish_date == "" || ( ($publish_date < $timestamp && !$unpublish_date) || ($publish_date < $timestamp && $unpublish_date > $timestamp))) ){
                    if($option_is_static){
?>
<div class="site-alert static <?PHP print esc_attr($priority_class);?> <?PHP print esc_attr($icon_class);?>" location="<?PHP print esc_attr($location);?>" location-custom="<?PHP print esc_attr($location_custom);?>" style="border-color: <?PHP print esc_attr($priority_color_border);?>; background: <?PHP print esc_attr($priority_color_bg);?>; color: <?PHP print esc_attr($priority_color_text);?>">
    <?PHP print wp_kses_post($message);?>
</div>
<?PHP
                    }
                    if($option_is_popup){
?>
<div class="modal site-alert-modal fade <?PHP print esc_attr($option_is_popup);?>" id="alert-modal-<?PHP print esc_attr($post_id);?>" data-id="<?PHP print esc_attr($post_id);?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: <?PHP print esc_attr($priority_color_border_popup);?>;">
            <div class="site-alert <?PHP print esc_attr($priority_class);?> <?PHP print esc_attr($icon_class);?> <?PHP print esc_attr($option_is_popup);?>" style="background: <?PHP print esc_attr($priority_color_bg);?>; color: <?PHP print esc_attr($priority_color_text);?>">
                <div class="modal-body">
                    <div class="alert-message">
                        <?PHP print wp_kses_post($message);?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-button" data-bs-dismiss="modal">Close</button>
                    <input type="checkbox" name="donotshow" id="donotshow"/> <small>Dismiss this window</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?PHP
                    }
                    if($option_is_sticky_footer){
?>
<div class="site-alert sticky-footer <?PHP print esc_attr($priority_class);?>" style="border-color: <?PHP print esc_attr($priority_color_border);?>; background: <?PHP print esc_attr($priority_color_bg);?>; color: <?PHP print esc_attr($priority_color_text);?>" data-id="<?PHP print esc_attr($post_id);?>">
    <i class="sa-closed dashicons dashicons-warning" alt="Alert Notification" title="Alert Notification"></i>
    <div class="site-alert-content">
        <i class="sa-close dashicons dashicons-no"></i>
        <?PHP
            $full_message = wp_kses_post($message);
            $length = 50;
            $short_message = sssa_truncate_html($full_message, $length)."...";
        ?>
        <div class="short-length"><?PHP print $short_message;?></div>
        <div class="full-length"><?PHP print $full_message;?></div>
    </div>
</div>
<?PHP
                    }
                    $c++;
                }
            }
        }
        restore_current_blog();
    }
?>
<script>
(function( $ ) {
    $(document).ready(function() {
       //Handle standard
       var site_alert_anchor = $('.site-alert-anchor');
       if(site_alert_anchor.length > 0) {
         site_alert_anchor.empty();
         $('.site-alert.static').each(function(){
           var e = $(this);
           e.appendTo(site_alert_anchor);
         });
       }else{
         $('.site-alert.static').each(function() {
             var e = $(this);
             var location = e.attr('location');
             if(location === "content"){
                 var location = 'body';
                 if($("main").length > 0) {
                   location = 'main';
                 }else if($('.site-content').length > 0){
                   location = 'site-content'
                 }
                 siteAlertCheckDom(e, location, 'prependTo');
             }else if(location === "footer"){
                 siteAlertCheckDom(e, 'footer', 'prependTo');
             }else if(location === 'custom-class'){
                 e.prependTo($(e.attr('location-custom')));
             }
         });
       }

       handleStickyFooterAlert();
    });

    //Handle PopUp
    var storage = window.localStorage;
    var numOfPopups = $('.site-alert-modal.ispopup').length;
    $('.site-alert-modal.ispopup').each(function(i){
       var e = $(this);
       var id = e.data('id');
       var storageId = sssa_getHost() + "___" + id;
       if(storage.getItem(storageId)){
           //if alert is set as 'do not show' by the browser
           e.remove();
       }
       e[0].addEventListener('hide.bs.modal', function(){
           var doNotShow = e.find('#donotshow');
           if(doNotShow.prop('checked')){
               storage.setItem(storageId, Date.now());
           }
       });

       //Handle multiple PopUp alerts
       //upon closing one alert will open the next until the end
       var next = $('.site-alert-modal.ispopup').eq(i + 1);
       if(next.length > 0){
           var nextTargetId = next.attr('id');
           e.find('.close-button').attr('data-bs-target', '#' + nextTargetId).attr('data-bs-toggle', 'modal');
       }
    });

    //pop up the first alert window
    var firstModal = $('.site-alert-modal.ispopup').first()[0];
    if(firstModal){
       var networkAlertModal = new bootstrap.Modal(firstModal);
       networkAlertModal.show();
       //changes the last alert's close button to close instead of recycling the popup alert windows
       $('.site-alert-modal.ispopup').last().find('.close-button').removeAttr('data-bs-target data-bs-toggle');
    }

    //handle sticky footer
    function handleStickyFooterAlert() {
        $('.site-alert.sticky-footer').each(function(i, e){
            var alert = $(e);
            var id = alert.data('id');
            var storageId = sssa_getHost() + "___" + id;
            if(storage.getItem(storageId)){
                //if alert is set as 'do not show' by the browser
                alert.addClass('truncate');
            }

            var offset = 5;
            var height = alert.outerHeight();
            var pos = 10 + (height * i) + (i * 5);
            alert.css('bottom', pos);

            //while is open
            alert.find('i.sa-close').on('click', function(e){
                e.stopPropagation();
                alert.addClass('truncate');
                alert.removeClass('expand');
                storage.setItem(storageId, Date.now());
            });

            alert.on('click', function(){
                if(!alert.hasClass('truncate')) {
                    if(!alert.hasClass('expand')) {
                        $('.site-alert.sticky-footer').removeClass('expand');
                        alert.addClass('expand');
                        alert.removeClass('truncate');
                    }else{
                        alert.addClass('truncate');
                        alert.removeClass('expand');
                    }
                }else{
                    alert.addClass('expand');
                    alert.removeClass('truncate');
                    storage.removeItem(storageId);
                }
            });
        });
    }

    function siteAlertCheckDom(e, name, insertion){
       var _tag = $(name);
       var _class = $('.' + name);
       var _id = $('#' + name);
       if(_tag.length > 0){
           e[insertion](_tag);
       }else if(_class.length > 0){
           e[insertion](_class);
       }else if(_id.length > 0){
           e[insertion](_id);
       }
    }
})( jQuery );
</script>
