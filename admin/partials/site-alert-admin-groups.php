<?PHP

$create_group_nonce = wp_create_nonce('create_group_nonce');
$update_group_prop_nonce = wp_create_nonce('update_group_prop_nonce');
$update_group_group_select_site_nonce = wp_create_nonce('update_group_group_select_site_nonce');
$poll_group_nonce = wp_create_nonce('poll_group_nonce');

//get groups
$posts = get_posts(array('post_type' => 'site_group', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft')));
$c = 0;
foreach ($posts as $post) {
    $sites = get_post_meta($post->ID, 'sites', true);
    $posts[$c]->sites = $sites;
    $c++;
}
$groups = json_encode($posts);
print "<script>
var groups = $groups,
create_group_nonce = '$create_group_nonce',
update_group_prop_nonce = '$update_group_prop_nonce',
update_group_group_select_site_nonce = '$update_group_group_select_site_nonce',
poll_group_nonce = '$poll_group_nonce',
selectedBlockId = '',
selectedBlock = '';
</script>";
?>
<div class='wrap site-alert-admin'>
    <h1><i class="dashicons dashicons-admin-generic page-icon"></i> Site Groups</h1>
    <p>
        Site Groups allow pre-define collective of sites to be used under Add/Edit -> Behavior -> Show across all network sites -> Exclude. Instead of having to select sites one by one
        to exclude per each Alert, selecting a group will automatically select all of the associated sites.
    </p>
    <p>
      <a href="javascript: void(0);" onclick="javascript: jQuery('#instr').slideToggle()">Show/Hide Instructions</a>
      <ol id="instr" style="display: none; font-size: 14px;">
        <li>Create a new group or select an existing group. Example: Elementary Schools, High Schools, etc..</li>
        <li>Click the available sites on the right column to assign/unassign it to the selected group.</li>
        <li>Associated sites is be hightlighted.</li>
      </ol>
    </p>
    <hr/>
    <div class="row site-alert-groups">
        <div class="col-sm-6 groups-wrapper">
            <div class="manage">
                <h3>Groups</h3>
                <input type="text" class="create-group-title" placeholder="Name of new group" maxlength="50"/><button class="button create-group">Submit</button>
                <ul class="groups"></ul>
            </div>
            <div class="droparea"></div>
        </div>
        <div class="col-sm-6 sites-wrapper">
            <h3>Available Sites</h3>
            <?PHP
                $sites = get_sites([
                    'orderby' => 'domain',
                    'order' => 'ASC',
                ]);
            ?>
            <div id="sites-list">
                <input type="text" name="search-sites" class="search" id="search-sites" placeholder="quick search"/>
                <ul class="list">
                    <?PHP
                        foreach ($sites as $site) {
                            $site_id = $site->id;
                            $site_name = explode(".", $site->domain)[0];
                            print "<li data-site='".esc_attr($site_name)."'><span class='name'>$site_name</span></li>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
(function($){
    $(document).ready(function() {
        var mouseDown = false;
        $(document).mousedown(function() {
            mouseDown = true;
        }).mouseup(function() {
            mouseDown = false;
        });
        var assignedSites = [];

        initGroups();
        createGroup();
        initAvailSites();

        function initGroups() {
            $.each(groups, function(i, e){
                var sitesCount = e.sites !== '' ? e.sites.split(',').length : 0;
                var block = buildGroupBlock(e.ID, e.post_title, sitesCount);
                $('.groups').prepend(block);
            });
        }

        function buildGroupBlock(id, title, sitesCount) {
            var _li = $('<li/>', {class: 'group', id: id});
            var _wrap = $('<div/>', {class: '_wrap'});
            var _title = $('<input/>', {type: 'text', class: 'group-title'}).val(title);
            var _row = $('<div/>', {class: 'row'});
            var _colLeft = $('<div/>', {class: 'col-sm-6'});
            var _colRight = $('<div/>', {class: 'col-sm-6', style: 'text-align: right'});
            var _counter = $('<small/>', {class: 'counter'}).append('Assigned: ', $('<span/>').append(sitesCount + ' site(s)'));
            var _delete = $('<button/>', {class: 'delete'}).append($('<i/>', {class: 'dashicons dashicons-trash'}));
            _row.append(_colLeft.append(_counter), _colRight.append(_delete));
            var block = _li.append(_wrap.append(_title, _row));

            //call to actions
            _title.on('change', function(){
                var e = $(this);
                update_action(e, 'update_title');
            });

            _delete.on('click', function(){
                var e = $(this);
                var confirm = window.confirm('Confirm DELETE ' + title + '? This action cannot be undone.');
                if(confirm == true){
                    update_action(e, 'delete');
                }
            });

            block.on('click', function(){
                poll_sites();
            });

            function update_action(e, event_action){
                //handles update group title or removal
                var data = {
                    _ajax_nonce: update_group_prop_nonce,
                    id: id,
                    val: e.val(),
                    event_action: event_action,
                    action: "update_group_prop",
                }
                $.ajax({
                    type : "post",
                    url : "/wp-admin/admin-ajax.php",
                    data : data,
                    success: function(response) {
                        if(response == "success") {
                            if(event_action == 'update_title'){
                                e.addClass('success');
                            }else if(event_action == 'delete'){
                                block.fadeOut('fast', function(){ block.remove();});
                            }
                        }else if(response == "error") {
                            e.addClass('required');
                        }else if(response == "error-nonce") {
                            alert("nonce error");
                        }
                    }
                });
            }

            function poll_sites() {
                //when group is clicked, poll and hightlight the sites assigned
                if(!block.hasClass('selected')){
                    selectedBlockId = id;
                    selectedBlock = block;
                    block.addClass('selected');
                    block.siblings('.group').removeClass('selected');
                    var data = {
                        _ajax_nonce: poll_group_nonce,
                        id: id,
                        action: 'poll_group',
                    }
                    $.ajax({
                        type : 'post',
                        url : '/wp-admin/admin-ajax.php',
                        data : data,
                        success: function(response) {
                            if(response !== 'error') {
                                var arr = response.split(',');
                                var sitesList = $('#sites-list');
                                sitesList.find('li').removeClass('ui-selected');
                                $.each(arr, function(i, val){
                                    var obj = sitesList.find("[data-site='" + val + "']").addClass('ui-selected');
                                    assignedSites.push(val);
                                });
                            }else if(response == 'error-nonce') {
                                alert('nonce error');
                            }else if(response == 'error'){

                            }
                        }
                    });
                }
            }

            //return
            return block;
        }

        function createGroup(){
            var btn = $('.create-group');
            var manageBlock = $('.groups');
            btn.on('click', function(){
                var e = $(this);
                var groupTitleObj = $('.create-group-title');
                var groupTitle = groupTitleObj.val();
                var data = {
                    _ajax_nonce: create_group_nonce,
                    title: groupTitle,
                    action: "create_group",
                }
                if(groupTitle == "" || typeof groupTitle == 'undefined'){
                    groupTitleObj.addClass('required');
                    return false;
                }else{
                    $.ajax({
                        type : "post",
                        url : "/wp-admin/admin-ajax.php",
                        data : data,
                        success: function(response) {
                            if(response != "error") {
                                var obj = JSON.parse(response);
                                var block = buildGroupBlock(obj.post_id, obj.post_title);
                                manageBlock.prepend(block);
                                groupTitleObj.val('');
                            }else if(response == "error") {

                            }else if(response == "error-nonce") {
                                alert("nonce error");
                            }
                        }
                    });
                }
            });
        }

        function initAvailSites() {
            var sitesList = $('#sites-list');
            sitesList.selectable({
                filter: 'li',
                selected: function (event, ui) {
                    //allow toggle of the selected states
                    if ($(ui.selected).hasClass('click-selected')) {
                        $(ui.selected).removeClass('ui-selected click-selected');
                    } else {
                        $(ui.selected).addClass('click-selected');
                    }
                },
                unselecting: function (event, ui) {
                    //when mouse is down and selection box has touched a site, don't deselect when the selection box is moved away from site
                    if(mouseDown && $(ui.unselecting).hasClass('ui-unselecting')){$(ui.unselecting).addClass('ui-selected')};
                },
                unselected: function (event, ui) {
                    $(ui.unselected).removeClass('click-selected');
                },
                start: function(event, ui){
                    //always allow multiple selection without needing to hold ctrl/cmd key
                    event.originalEvent.ctrlKey = true;
                },
                stop: function(event, ui){
                    if(selectedBlockId == '' || typeof selectedBlockId == 'undefined'){
                        sitesList.find('li.ui-selected').removeClass('ui-selected');
                    }else{
                        //gather all selected sites and instert into the final array
                        sitesList.find('li.ui-selected').each(function(){
                            var siteName = $(this).data('site');
                            //insert into site name only if it doesn't exist in final array
                            if(!assignedSites.includes(siteName)){
                                assignedSites.push(siteName);
                            }
                        });
                        //gather all unselected sites and remove from the final array
                        sitesList.find('li').not('.ui-selected').each(function(){
                            var siteName = $(this).data('site');
                            //if site name is in the array then proceed to remove it
                            if(assignedSites.includes(siteName)){
                                assignedSites = sssa_removeFromArray(assignedSites, siteName);
                            }
                        });

                        //remove duplicates just incase
                        assignedSites = [...new Set(assignedSites)];

                        //implode array into string
                        selectedSites = assignedSites.join(',');
                        selectedSites = sssa_trim(selectedSites, ',');
                        //pass to ajax to process
                        ajaxUpdate(selectedSites);
                    }
                }
            });

            function ajaxUpdate(sites) {
                var data = {
                    _ajax_nonce: update_group_group_select_site_nonce,
                    id: selectedBlockId,
                    sites: sites,
                    action: "update_group_select_site",
                }
                $.ajax({
                    type : "post",
                    url : "/wp-admin/admin-ajax.php",
                    data : data,
                    success: function(response) {
                        if(response != "error") {
                            selectedBlock.find('.counter span').html(response);
                        }else if(response == "error") {

                        }else if(response == "error-nonce") {
                            alert("nonce error");
                        }
                    }
                });
            }
        }
    });

})(jQuery);
</script>
