<?php

/**
* Provide a admin area view for the plugin
*
* This file is used to markup the admin-facing aspects of the plugin.
*
*/
?>

<?PHP
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
if($action == ''){
    $action = 'add';
}
$post_id = isset($_GET['post-id']) ? intval($_GET['post-id']) : '';
$post_status = 'publish';
$post_status_checked = 'checked';

$exclude_by_group = isset($_POST['exclude_by_group']) ? sanitize_text_field($_POST['exclude_by_group']) : '';

//declare variables
$title = $alert_message = $priority = $icon = $option_all_pages =
$option_all_sites = $option_is_static = $location = $option_all_sites_checked = $option_is_popup =
$publish_date = $unpublish_date = $error = $notice = '';

//form handler
//when form is submitted
if(isset($_POST['submit'])){
    $post_id = intval($_POST['post-id']);
    $action = sanitize_text_field($_POST['action']);
    $title = sanitize_text_field($_POST['title']);
    $alert_message_raw = wp_kses_post($_POST['alert_message']);
    $alert_message = stripslashes($alert_message_raw);
    $priority = sanitize_text_field($_POST['priority']);
    $priority_color_border = isset($_POST['priority_color_border']) ? sanitize_hex_color($_POST['priority_color_border']) : '#333333';
    $priority_color_bg = isset($_POST['priority_color_bg']) ? sanitize_hex_color($_POST['priority_color_bg']) : '#aaaaaa';
    $priority_color_text = isset($_POST['priority_color_text']) ? sanitize_hex_color($_POST['priority_color_text']) : '#545454';

    $location = sanitize_text_field($_POST['location']);
    $location_custom = isset($_POST['location_custom']) ? sanitize_text_field($_POST['location_custom']) : '';
    $icon = sanitize_text_field($_POST['icon']);
    $option_all_pages = isset($_POST['allpages']) ? intval($_POST['allpages']) : '';
    $option_all_sites = isset($_POST['allsites']) ? intval($_POST['allsites']) : '';
    $option_is_static = isset($_POST['isstatic']) ? intval($_POST['isstatic']) : '';
    $option_is_sticky_footer = isset($_POST['isstickyfooter']) ? intval($_POST['isstickyfooter']) : '';
    $option_is_popup = isset($_POST['ispopup']) ? intval($_POST['ispopup']) : '';
    $publish_date = isset($_POST['publish-date']) ? sanitize_text_field($_POST['publish-date']) : '';
    $unpublish_date = isset($_POST['unpublish-date']) ? sanitize_text_field($_POST['unpublish-date']) : '';
    $post_status = isset($_POST['publish-status']) ? sanitize_text_field($_POST['publish-status']) : 'draft';
    $post_status_checked = $post_status == 'publish' ? 'checked' : '';

    $excluded_sites = array();
    foreach($_POST as $name => $val) {
        // Split the name into an array
        $splitString = explode("-", $name);
        if ($splitString[0] == "exclude") {
            array_push($excluded_sites, $_POST[$splitString[0]."-".$splitString[1]]);
        }
    }

    $data = array(
        'post_title' => $title,
        'post_content' => $alert_message_raw,
        'post_status' => $post_status,
        'post_type' => 'site_alert'
    );

    //validate form for missing/empty fields
    $validate_array = array();
    if(!$title){
        array_push($validate_array, 'Title');
    }

    if(!$alert_message_raw){
        array_push($validate_array, 'Message');
    }

    if(count($validate_array) > 0){
        foreach ($validate_array as $fault) {
            $error .= '<li><span class="dashicons dashicons-minus"></span> Provide a '.$fault.'.</li>';
        }
    }else{
        //if newly entered entry
        if($action != 'edit'){
            $result = wp_insert_post($data);

            if($result && ! is_wp_error($result)){
                $post_id = $result;
                add_post_meta($post_id, 'priority', $priority);
                add_post_meta($post_id, 'location', $location);
                add_post_meta($post_id, 'location_custom', $location_custom);
                add_post_meta($post_id, 'icon', $icon);
                add_post_meta($post_id, 'allpages', $option_all_pages);
                add_post_meta($post_id, 'allsites', $option_all_sites);
                add_post_meta($post_id, 'exclude', implode(",", $excluded_sites));
                add_post_meta($post_id, 'exclude_by_group', $exclude_by_group);
                add_post_meta($post_id, 'isstatic', $option_is_static);
                add_post_meta($post_id, 'isstickyfooter', $option_is_sticky_footer);
                add_post_meta($post_id, 'ispopup', $option_is_popup);
                add_post_meta($post_id, 'publish-date', $publish_date);
                add_post_meta($post_id, 'unpublish-date', $unpublish_date);
                $notice = 'Entry has been created. You can edit its features below.';
            }
            $action = 'edit';
        }elseif($action == 'edit'){
            //if editing the entry
            $data['ID'] = $post_id;
            $result = wp_update_post($data);
            if($result && ! is_wp_error($result)){
                update_post_meta($post_id, 'priority', $priority);
                update_post_meta($post_id, 'priority_color_border', $priority_color_border);
                update_post_meta($post_id, 'priority_color_bg', $priority_color_bg);
                update_post_meta($post_id, 'priority_color_text', $priority_color_text);
                update_post_meta($post_id, 'location', $location);
                update_post_meta($post_id, 'location_custom', $location_custom);
                update_post_meta($post_id, 'icon', $icon);
                update_post_meta($post_id, 'allpages', $option_all_pages);
                update_post_meta($post_id, 'allsites', $option_all_sites);
                update_post_meta($post_id, 'exclude', implode(",", $excluded_sites));
                update_post_meta($post_id, 'exclude_by_group', $exclude_by_group);
                update_post_meta($post_id, 'isstatic', $option_is_static);
                update_post_meta($post_id, 'isstickyfooter', $option_is_sticky_footer);
                update_post_meta($post_id, 'ispopup', $option_is_popup);
                update_post_meta($post_id, 'publish-date', $publish_date);
                update_post_meta($post_id, 'unpublish-date', $unpublish_date);
                $notice = 'Entry has been updated.';
            }
        }
    }
}elseif($action == "edit"){
    //when the form is not submitted and opened from the manage dataTable
    $post_obj = get_post($post_id);
    $title = $post_obj->post_title;
    $post_status = $post_obj->post_status;
    $post_status_checked = $post_status == 'publish' ? 'checked' : '';
    $post_status = $post_obj->post_status;
    $alert_message_raw = $post_obj->post_content;
    $alert_message = stripslashes($alert_message_raw);
    $priority = get_post_meta($post_id, 'priority', true);
    $priority_color_border = get_post_meta($post_id, 'priority_color_border', true);
    $priority_color_bg = get_post_meta($post_id, 'priority_color_bg', true);
    $priority_color_text = get_post_meta($post_id, 'priority_color_text', true);
    $location = get_post_meta($post_id, 'location', true);
    $location_custom = get_post_meta($post_id, 'location_custom', true);
    $icon = get_post_meta($post_id, 'icon', true);
    $option_all_pages = get_post_meta($post_id, 'allpages', true);
    $option_all_sites = get_post_meta($post_id, 'allsites', true);
    $excluded_sites = explode(',', get_post_meta($post_id, 'exclude', true));
    $exclude_by_group = get_post_meta($post_id, 'exclude_by_group', true);
    $option_is_static = get_post_meta($post_id, 'isstatic', true);
    $option_is_sticky_footer = get_post_meta($post_id, 'isstickyfooter', true);
    $option_is_popup = get_post_meta($post_id, 'ispopup', true);
    $publish_date = get_post_meta($post_id, 'publish-date', true);
    $unpublish_date = get_post_meta($post_id, 'unpublish-date', true);
}

$page_icon = $action == 'add' ? 'dashicons dashicons-plus-alt' : 'dashicons dashicons-edit-large';
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class='wrap site-alert-admin'>
<h1 class="wp-heading-inline"><?PHP print ucfirst($action);?> Alert</h1>
<br/><br/>
<?PHP
if($error){
    print "<div id='setting-error-settings-updated' class='notice notice-error is-dismissible'>Error:<ul>".$error.'</ul></div>';
}

if($notice){
    print "<div id='setting-error-settings-updated' class='notice notice-success is-dismissible'>".$notice."</div>";
}
?>
<form class="form" action="" method="post">
    <input type="hidden" name="action" value="<?PHP print esc_attr($action);?>"/>
    <input type="hidden" name="post-id" value="<?PHP print esc_attr($post_id);?>"/>
    <div class="row site-alert-admin">
        <div class="col-sm-6">
            <h3 for="title">Title:</h3>
            <input type="text" name="title" class="form-control" value="<?PHP print esc_attr($title);?>" required/>
            <h3 for="alert_message">Message:</h3>
            <?PHP wp_editor( wp_kses_post($alert_message), 'alerteditor', $settings = array('textarea_name' => 'alert_message') );?>
        </div>
        <div class="col-sm-6">
            <h3 style="float: left;">Options</h3>
            <div style="float: right;"><input type="submit" name="submit" value="Save" class="button"/></div>
            <hr style="clear: both;">
            <div class="row">
                <div class="col-sm-12">
                    <strong>Publish: </strong>
                    <label class='switch'>
                        <input name='publish-status' class='publish-status' type='checkbox' value='publish' data-id='<?PHP print esc_attr($post_id);?>' <?PHP print esc_attr($post_status_checked);?>>
                        <span class='slider round'></span>
                    </label>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-12 priority">
                    <div class="options-control"><strong>Priority Level</strong></div>
                    <div class="options">
                        <div><input type="radio" name="priority" value="1" data="low" <?PHP print $priority == 1 || !$priority ? "checked" : "";?>> Low</div>
                        <div><input type="radio" name="priority" value="2" data="medium" <?PHP print $priority == 2 ? "checked" : "";?>> Medium</div>
                        <div><input type="radio" name="priority" value="3" data="high" <?PHP print $priority == 3 ? "checked" : "";?>> High</div>
                        <div><input type="radio" name="priority" value="4" data="emergency" <?PHP print $priority == 4 ? "checked" : "";?>> Emergency</div>
                        <div>
                            <input type="radio" name="priority" value="5" data="custom" <?PHP print $priority == 5 ? "checked" : "";?>> Custom Colors
                            <div class="row priority-color-picker" <?PHP print $priority == 5 ? "style='display: block;'" : "";?>>
                                <div class="col-sm-12"><small>Border Color</small><br/><input class="picker" type="text" name='priority_color_border' affect="border-color" id='color-pickerA' value="<?php print esc_attr($priority_color_border);?>" /></div>
                                <div class="col-sm-12"><small>Background Color</small><br/><input class="picker" type="text" name='priority_color_bg' affect="background" id='color-pickerB' value="<?php print esc_attr($priority_color_bg);?>" /></div>
                                <div class="col-sm-12"><small>Text Color</small><br/><input class="picker" type="text" name='priority_color_text' affect="color" id='color-pickerC' value="<?php print esc_attr($priority_color_text);?>" /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-sm-12 icons">
                    <div class="options-control"><strong>Icon</strong></div>
                    <div class="options">
                        <div><input type="radio" name="icon" value="1" data="none" <?PHP print $icon == 1 || !$icon ? "checked" : "";?>> None</div>
                        <div><input type="radio" name="icon" value="2" data="info" <?PHP print $icon == 2 ? "checked" : "";?>> <span class="dashicons dashicons-info-outline"></span></div>
                        <div><input type="radio" name="icon" value="3" data="exclamation" <?PHP print $icon == 3 ? "checked" : "";?>> <span class="dashicons dashicons-warning"></span></div>
                        <div><input type="radio" name="icon" value="4" data="gear" <?PHP print $icon == 4 ? "checked" : "";?>> <span class="dashicons dashicons-admin-generic"></span></div>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-sm-12 behavior">
                    <div class="options-control"><strong for="options">Behavior</strong></div>
                    <div class="options">
                        <div>
                            <input type="checkbox" name="" checked disabled><span style='color: rgba(0,0,0,0.3)'>Show in Home Page</span>
                        </div>
                        <div>
                            <input type="checkbox" name="allpages" value="1" <?PHP print $option_all_pages == 1 ? "checked" : "";?>> Show in all pages
                        </div>
                        <?PHP if(is_network_admin() || is_super_admin()){?>
                        <div>
                            <?PHP
                                if($option_all_sites != "" || $option_all_sites){ $option_all_sites_checked = 'checked';}

                                $sites = get_sites([
                                    'orderby' => 'domain',
                                    'order' => 'ASC',
                                ]);
                            ?>
                            <input type="checkbox" name="allsites" class="toggle-sites" value="1" <?PHP print esc_attr($option_all_sites_checked);?>> Show across all network sites
                            <div class="show-in-all-sites">
                                <div class="site-exclude">
                                    <div><strong>Exclude Sites:</strong></div>
                                    <?PHP
                                        //get groups
                                        switch_to_blog(1);
                                        $groups = get_posts(array('post_type' => 'site_group', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft')));

                                        if(count($groups) > 0){
                                    ?>
                                    <div class="groups-wrapper">
                                        <small>Quick select by Group:</small>
                                        <?PHP
                                            foreach ($groups as $group) {
                                                $group_sites = get_post_meta($group->ID, 'sites', true);
                                                $selected = $group->ID == $exclude_by_group ? "selected" : "";
                                                print "<button type='button' class='button group small ".esc_attr($selected)."' data-id='".esc_attr($group->ID)."' data-sites='".esc_attr($group_sites)."'>".esc_attr($group->post_title)."</button>";
                                            }
                                        ?>
                                    </div>
                                        <input type="hidden" name="exclude_by_group" class="group-select" value="<?PHP print esc_attr($exclude_by_group);?>"/>
                                    <?PHP } restore_current_blog(); ?>
                                    <div id="sites-list">
                                        <input type="text" name="search-sites" class="search" id="search-sites" placeholder="quick search"/><button type="button" class="button" id="reset-selections">Reset selections</button>
                                        <ul class="list">
                                            <?PHP
                                            foreach ($sites as $site) {
                                                $site_name = explode(".", $site->domain)[0];
                                                $site_id = $site->blog_id;
                                                if(($action != "" && $action != "add") && in_array($site_id, $excluded_sites)){
                                                    $checked = 'checked';
                                                }else{
                                                    $checked = '';
                                                }
                                                print "<li><span class='name'><input type='checkbox' name='exclude-".esc_attr($site_name)."' data-name='".esc_attr($site_name)."' value='".esc_attr($site_id)."' ".esc_attr($checked)."/> $site_name </span></li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <?PHP } ?>
                        <div>
                            <input type="checkbox" name="isstatic" class="toggle-static" value="1" <?PHP print $option_is_static == 1 ? "checked" : "";?>> Embed in page
                            <div class="location-wrapper">
                                <div class="location">
                                    <strong>Location:</strong>
                                    <div><input type="radio" name="location" value="content" data="content" <?PHP print $location == "content" || !$location ? "checked" : "";?>> Body (results will vary depending on your theme)</div>
                                    <div><input type="radio" name="location" value="footer" data="footer" <?PHP print $location == "footer" ? "checked" : "";?>> Footer</div>
                                    <div><input type="radio" name="location" value="custom-class" data="custom-class" <?PHP print $location == "custom-class" ? "checked" : "";?>> Custom Element</div>
                                    <div class='custom-location-select'>
                                        <input type="text" name="location_custom" value="<?PHP print $location_custom;?>" class="form-control" placeholder="Insert tag name, .class, or #id"/>
                                        <p><em>Each theme may have different class name and ID value. You may have to inspect the element to place at the desired position. Be sure to include . and # for classes and IDs respectively.</em></p>
                                    </div>
                                    <p>
                                      <strong>Shortcode:</strong> <span style='color: darkred;'>[site_alert]</span><br/>
                                      Anchor the alert to the shortcode and replace the above locations.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <input type="checkbox" name="isstickyfooter" value="1" <?PHP print $option_is_sticky_footer == 1 ? "checked" : "";?>> Sticky Footer
                        </div>
                        <div>
                            <input type="checkbox" name="ispopup" value="1" <?PHP print $option_is_popup == 1 ? "checked" : "";?>> Pop-Up
                            <?PHP if($action == "edit"){?><a href="javascript: void(0)" class="reset-popup small" data-id="<?PHP print esc_attr($post_id);?>">[Reset suppressed window]</a><?PHP } ?>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="options-control"><strong>Publish Day/Range</strong> <span class="date-expire-status"></span></div>
                    <div class="options">
                        <div>Optional. Leave empty for infinite.</div><br/>
                        <span type="text" class="publish-cal" id="publish-cal"></span>
                        <input type="hidden" name="publish-date" class="publish-date" value="<?PHP print esc_attr($publish_date);?>"/>
                        <input type="hidden" name="unpublish-date" class="unpublish-date" value="<?PHP print esc_attr($unpublish_date);?>"/>
                        <p>
                          - For single day only, double-click on the day.
                          <br/>
                          - To select a range, select a date then select the next.
                        </p>
                        <a href="javascript: void(0)" class="clear-date"><small>Clear Date(s)</small></a>
                    </div>
                </div>
            </div>
            <hr/>
            <input type="submit" name="submit" value="Save" class="button"/>
        </div>
    </div>

</form>
</div>
<script src="<?PHP print esc_url(plugin_dir_url( __FILE__ ) . '../js/site-alert-admin.js');?>"></script>
