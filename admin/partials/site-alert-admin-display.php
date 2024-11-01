<?php

if(isset($_POST['select_site'])){
    $blog_id = intval($_POST['select_site']);
    switch_to_blog($blog_id);
}elseif(isset($_GET["id"])){
    $blog_id = intval($_GET["id"]);
    switch_to_blog($blog_id);
}else{
    $blog_id = get_current_blog_id();
}

$all_blogs = get_sites();

$nonce = wp_create_nonce('update_site_alert_nonce');
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class='wrap site-alert-admin' data-blog-id='<?PHP print $blog_id;?>'>
<h1 class="wp-heading-inline">Super Simple Site Alert</h1>
<?PHP if(is_network_admin()){ ?>
<form action='' method='POST'>
  <br/>
    <p>
        <label>Select Site: </label>
        <select name='select_site' onchange='this.form.submit()'>
        <?PHP
            foreach ($all_blogs as $key => $blog) {
                $site_id = $blog->blog_id;
                $domain = $blog->domain;
                $selected = $site_id == $blog_id ? 'selected' : '';

    print "<option value='".esc_attr($site_id)."' ".esc_attr($selected).">$domain</option>";

            };
        ?>
        </select>
    </p>
</form>
<strong><?PHP print get_bloginfo('name');?></strong>
<p><?PHP print get_bloginfo('url');?></p>
<?PHP
}
$alerts = get_posts(array('post_type' => 'site_alert', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft')));
?>
<a href="<?PHP print esc_url('admin.php?page=site-alert-manage');?>" class="page-title-action">Create an Alert</a>
<table class='site-alert-table'>
    <thead>
        <tr>
            <td>Id</td>
            <td>Title</td>
            <td>Message</td>
            <td>Start</td>
            <td>End</td>
            <td>Behavior</td>
            <td>Scope</td>
            <td class="center">Status</td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        <?PHP
            foreach ($alerts as $alert) {
                $post_id = $alert->ID;
                $title = $alert->post_title;
                $post_status = $alert->post_status;
                $post_status_checked = $post_status == 'publish' ? 'checked' : '';
                $message = sssa_truncate_html(strip_tags($alert->post_content), 50);

                $priority = get_post_meta($post_id, 'priority', true);
                $priority_color_bg = '';
                $priority_color_text = '';
                switch ($priority) {
                    case 1:
                        $priority_label = 'low';
                        break;
                    case 2:
                        $priority_label = 'medium';
                        break;
                    case 3:
                        $priority_label = 'high';
                        break;
                    case 4:
                        $priority_label = 'emergency';
                        break;
                    case 5:
                        $priority_label = 'custom';
                        $priority_color_bg = 'background-color: '.get_post_meta($post_id, 'priority_color_bg', true).';';
                        $priority_color_text = 'color:'.get_post_meta($post_id, 'priority_color_text', true).';';
                        break;
                    default:
                        $priority_label = 'low';
                        break;
                }
                $icon = get_post_meta($post_id, 'icon', true);
                $option_all_pages = get_post_meta($post_id, 'allpages', true);
                $option_all_sites = get_post_meta($post_id, 'allsites', true) == 1 ? 'network wide' : 'site';
                $excluded_sites = explode(',', get_post_meta($post_id, 'exclude', true));
                $behavior = array(get_post_meta($post_id, 'isstatic', true) == 1 ? "static" : "", get_post_meta($post_id, 'ispopup', true) == 1 ? "popup" : "");
                $behavior = trim(implode(", ", $behavior), ",");
                $publish_date = get_post_meta($post_id, 'publish-date', true);
                $publish_date_read = sssa_date_format_unix($publish_date, "F d, Y");
                $unpublish_date = get_post_meta($post_id, 'unpublish-date', true);
                $unpublish_date_read = sssa_date_format_unix($unpublish_date, "F d, Y");
                $url = get_bloginfo('url');
?>
                <tr class='small site-alert pl-<?PHP print esc_attr($priority_label);?>' style='font-size: inherit; <?PHP print esc_attr($priority_color_bg);?> <?PHP print esc_attr($priority_color_text);?>'>
                    <td><?PHP print $post_id;?></td>
                    <td><a href='<?PHP print esc_url("$url/wp-admin/admin.php?page=site-alert-manage&action=edit&post-id=$post_id");?>' style='<?PHP print esc_attr($priority_color_text);?>'><?PHP print $title;?></a></td>
                    <td><?PHP print $message;?></td>
                    <td data-sort='<?PHP print esc_attr($publish_date);?>'><?PHP print $publish_date_read;?></td>
                    <td data-sort='<?PHP print esc_attr($unpublish_date);?>'><?PHP print $unpublish_date_read;?></td>
                    <td><?PHP print $behavior;?></td>
                    <td><?PHP print $option_all_sites;?></td>
                    <td class='center'>
                        <label class='switch'>
                            <input class='update' type='checkbox' data-user-action='edit' data-blog-id='<?PHP print esc_attr($blog_id);?>' data-id='<?PHP print esc_attr($post_id);?>' data-nonce='<?PHP print $nonce;?>' <?PHP print esc_attr($post_status_checked);?>>
                            <span class='slider round'></span>
                        </label>
                    </td>
                    <td>
                        <i class='update dashicons dashicons-trash' data-user-action='delete' data-blog-id='<?PHP print esc_attr($blog_id);?>' data-id='<?PHP print esc_attr($post_id);?>' data-nonce='<?PHP print $nonce;?>'></i>
                    </td>
                </tr>
<?PHP
            }
?>
    </tbody>
</table>
</div>
<?PHP
    restore_current_blog();
?>
<script>
(function($){
    var table = $(".site-alert-table").DataTable({
        paging: false,
        order: [[0, "desc"]],
        pageLength: 500
    });

    toggleStatus();
    sssa_insertParam("id", $(".wrap").data("blog-id"));
    function toggleStatus()
    {
        $(".update").on("click", function(){
            var e = $(this);
            var blogId = e.data("blog-id");
            var userAction = e.data("user-action");
            var nonce = e.data("nonce");
            var postId = e.data("id");
            var data = {
                _ajax_nonce: nonce,
                blog_id: blogId,
                action: "update_site_alert",
                user_action: userAction,
                post_id : postId,
            }

            if(userAction == "edit"){
                var status = e.prop("checked") == true ? 'publish' : 'draft';
                data.status = status;
            }
            $.ajax({
                 type : "post",
                 url : "/wp-admin/admin-ajax.php",
                 data : data,
                 success: function(response) {
                    if(response == "success") {
                        switch (userAction) {
                            case "edit":

                                break;
                            case "delete":
                                var theRow = e.closest("tr");
                                theRow.fadeOut("fast", function(){
                                    table.row(theRow).remove().draw();
                                });
                                break;
                        }
                    }else if(response == "error") {
                        switch (userAction) {
                            case "edit":
                                alert("fail to change status");
                                break;
                            case "delete":
                                alert("fail to delete post");
                                break;
                        }

                    }else if(response == "error-nonce") {
                        alert("nonce error");
                    }
                 }
             });
        });
    }
})(jQuery);
</script>
