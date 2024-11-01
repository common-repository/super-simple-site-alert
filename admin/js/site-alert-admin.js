(function( $ ) {
'use strict';

/**
* All of the code for your admin-facing JavaScript source
* should reside in this file.
*
* Note: It has been assumed you will write jQuery code here, so the
* $ function reference has been prepared for usage within the scope
* of this function.
*
* This enables you to define handlers, for when the DOM is ready:
*
* $(function() {
*
* });
*
* When the window is loaded:
*
* $( window ).load(function() {
	*
	* });
	*
	* ...and/or other possibilities.
	*
	* Ideally, it is not considered best practise to attach more than a
	* single DOM-ready or window-load handler for a particular page.
	* Although scripts in the WordPress core, Plugins and Themes may be
	* practising this, we should strive to set a better example in our own work.
	*/
	$( window ).load(function() {
	//$(document).ready(function() {
		if(typeof tinymce != 'undefined'){
			tinymce.activeEditor.dom.styleSheetLoader.load(site_alert_path + '/public/css/site-alert-public.css');
			tinymce.activeEditor.dom.styleSheetLoader.load(site_alert_path + '/admin/js/bootstrap-5.1.3/css/bootstrap.min.css');
			tinymce.activeEditor.dom.addClass('tinymce', 'site-alert');
		}

		//list js
		//
		var options = {valueNames: ['name', 'desc']}
		var sites = new List('sites-list', options);

		optionControl();
		setPriority();
		setIcon();
		showLocation();
		showSites();
		selectGroup();
		selectSite();
		resetSelections();
		resetPopup();
		showStaticOption();
		setDatePicker();
	//});
	});
	function optionControl(){
		$(".options-control").on("click", function(){
			$(this).next('.options').slideToggle();
		});
	}

	function setPriority()
	{
		$(".priority input[type='radio']").each(function(){
			var e = $(this);
			var allClasses = 'pl-low pl-medium pl-high pl-emergency pl-custom';
			if(e.prop('checked')){
				tinymce.activeEditor.dom.removeClass('tinymce', allClasses);
				tinymce.activeEditor.dom.addClass('tinymce', 'pl-' + e.attr("data"));
			}

			e.on("click", function(){
				var val = e.val();
				tinymce.activeEditor.dom.setAttrib('tinymce', 'style', '');
				tinymce.activeEditor.dom.removeClass('tinymce', allClasses);
				tinymce.activeEditor.dom.addClass('tinymce', 'pl-' + e.attr("data"));

				if(e.attr("data") === "custom"){
					setPriorityCustomColor();
					$(".priority-color-picker").slideDown();
				}else{
					$(".priority-color-picker").slideUp();
				}
			});
		});

		//when the color is changed
		$('#color-pickerA, #color-pickerB, #color-pickerC').wpColorPicker({
			change: function(event, ui){
				var color = ui.color.toString();
				var affect = $(event.target).attr("affect");
				tinymce.activeEditor.dom.setStyle('tinymce', affect, color);
			}
		});

		setPriorityCustomColor();
	}

	function setPriorityCustomColor()
	{
		//if custom color, set color to textarea
		if($(".priority input[type='radio']:checked").val() === "5") {
			$('.picker').each(function(){
				var e = $(this);
				var color = e.val();
				var affect = e.attr("affect");
				tinymce.activeEditor.dom.setStyle('tinymce', affect, color);
			});
		}
	}

	function setIcon()
	{
		$(".icons input[type='radio']").each(function(){
			var e = $(this);
			if(e.prop('checked')){
				tinymce.activeEditor.dom.removeClass('tinymce', 'alert-icon-info alert-icon-exclamation alert-icon-gear');
				tinymce.activeEditor.dom.addClass('tinymce', 'alert-icon-' + e.attr("data"));
			}

			e.on('click', function(){
				var val = e.val();
				tinymce.activeEditor.dom.removeClass('tinymce', 'alert-icon-info alert-icon-exclamation alert-icon-gear');
				if(parseInt(val) != 1){
					tinymce.activeEditor.dom.addClass('tinymce', 'alert-icon-' + e.attr("data"));
				}
			});
		});
	}

	function showLocation()
	{
		var locationWrapper = $(".location .custom-location-select");
		var radios = $(".location input[type='radio']");
		var radioChecked = $(".location input[type='radio']:checked");
		showHide(radioChecked);

		radios.on("click", function(){
			showHide($(this));
		});

		function showHide(e){
			let val = e.val();
			if(val === "custom-class"){
				locationWrapper.slideDown();
				locationWrapper.find("input[type='text']").prop("required", true);
			}else{
				locationWrapper.slideUp();
				locationWrapper.find("input[type='text']").prop("required", false);
			}
		}
	}

	function showSites()
	{
		if($(".toggle-sites").prop("checked")){
			$(".site-exclude").show();
		}
		$(".toggle-sites").on("click", function(){
			var e = $(this);
			if(e.prop("checked")){
				$(".site-exclude").slideDown("fast");
			}else{
				$(".site-exclude").slideUp("fast");
			}
		});
	}

	function selectGroup()
	{
		var list = $('#sites-list .list');
		$('.group').each(function(){
			var e = $(this);
			e.on('click', function(){
				$('.group').removeClass('selected');
				e.addClass('selected');
				$('.group-select').val(e.data('id'));
				var sites = e.data('sites');
				if(sites.length > 0 || sites != 'group-reset'){
					list.find('input[type="checkbox"]').prop('checked', false);
					sites = sites.split(',');
					$.each(sites, function(i, val){
						list.find('input[data-name="' + val + '"]').prop('checked', true);
					});
				}else if(sites == 'group-reset'){
					$('.group').removeClass('selected');
				}
			});
		});
	}

	function selectSite()
	{
		var checkbox = $('#sites-list .list li input:checkbox');
		var groupSelect = $('.group-select');
		checkbox.on("click", function(){
			var e = $(this);
			$('.group').removeClass('selected');
			groupSelect.val('');
		});
	}

	function resetSelections()
	{
		var e = $('#reset-selections');
		e.on('click', function(){
			var conf = "Confirm reset?";
			if(confirm(conf)){
				$('.group').removeClass('selected');
				$('.group-select').val('');
				$('#sites-list .list input:checkbox').prop('checked', false);
			}
		});
	}

	function resetPopup()
	{
		var btn = $(".reset-popup");
		var storage = window.localStorage;
		if(btn.length > 0){
			btn.on("click", function(){
				var post_id = $(this).data("id");
				var storageId = sssa_getHost() + "___" + post_id;
				storage.removeItem(storageId);
				$(this).after($("<i/>", {class: "dashicons dashicons-yes"}));
			});
		}
	}

	function showStaticOption()
	{
		if($(".toggle-static").prop("checked")){
			$(".location-wrapper").show();
		}
		$(".toggle-static").on("click", function(){
			var e = $(this);
			if(e.prop("checked")){
				$(".location-wrapper").slideDown("fast");
			}else{
				$(".location-wrapper").slideUp("fast");
			}
		});
	}

	function setDatePicker()
	{
		//publish/unpublish dates
		var datepickerStdOptions = {
			language: "en",
			dateFormat: "MM dd, yyyy",
			dateTimeSeparator: " - ",
			inline: true,
			range: true,
			timepicker: false,
			minutesStep: 30,
			toggleSelected: false,
			zIndex: 9999
		};

		var tempPublishDate = '';
		var tempPublishDateDom = $("input.publish-date");
		var tempUnPublishDate = '';
		var tempUnPublishDateDom = $("input.unpublish-date");

		var publishDateOptions = {
			onSelect: function(formattedDate, date, e){
				if(date.length == 1){
					tempPublishDate = date[0];
					tempPublishDateDom.val(tempPublishDate);
					tempUnPublishDate = '';
					tempUnPublishDateDom.val(tempUnPublishDate);
					calcExpDate(tempPublishDate);
				}else if(date.length == 2){
					tempPublishDate = date[0];
					tempPublishDateDom.val(tempPublishDate);
					tempUnPublishDate = date[1];
					tempUnPublishDateDom.val(tempUnPublishDate);
					calcExpDate(tempPublishDate, tempUnPublishDate);
				}
			},
		};


		var publishDateOptions = $.extend({}, datepickerStdOptions, publishDateOptions);
		var unPublishDateOptions = $.extend({}, datepickerStdOptions, unPublishDateOptions);

		var publishCal = $("#publish-cal").datepicker(publishDateOptions).data('datepicker');

		var selectedDate = [tempPublishDateDom.val(), tempUnPublishDateDom.val()];
		var iniStartDate = selectedDate[0] ? sssa_timestampToNumberedDateTimeUniversal(selectedDate[0], "Y,m,d,H,m,s").split(",") : new Date();
		var iniEndDate = selectedDate[1] ? sssa_timestampToNumberedDateTimeUniversal(selectedDate[1], "Y,m,d,H,m,s").split(",") : new Date();
		selectedDate = selectedDate[1] ? [new Date(iniStartDate[0], iniStartDate[1]-1, iniStartDate[2], iniStartDate[3], iniStartDate[4], iniStartDate[5]), new Date(iniEndDate[0], iniEndDate[1]-1, iniEndDate[2], iniEndDate[3], iniEndDate[4], iniEndDate[5])] : new Date(iniStartDate[0], iniStartDate[1]-1, iniStartDate[2], iniStartDate[3], iniStartDate[4], iniStartDate[5]);
		if(selectedDate != "Invalid Date"){publishCal.selectDate(selectedDate);}

		$(".clear-date").on("click", function(){
			publishCal.clear();
			tempPublishDateDom.val('');
			tempUnPublishDateDom.val('');
			$('.date-expire-status').empty();
		});

		function calcExpDate(pub, unpub) {
			var nowTimeStamp = Date.now() / 1000; //change from milliseconds to seconds
			var statusDom = $('.date-expire-status');
			if(typeof unpub == 'undefined') {
				if(pub < nowTimeStamp) {
					statusDom.empty();
				}else{
					statusDom.html('(Scheduled)').css('color', '#2271b1');
				}
			}else{
				if(pub < nowTimeStamp && unpub < nowTimeStamp) {
					statusDom.html('(Expired)').css('color', 'red');
				}else if(pub < nowTimeStamp && unpub > nowTimeStamp) {
					statusDom.empty();
				}else{
					statusDom.html('(Scheduled)').css('color', '#2271b1');
				}
			}
		}
	}
})( jQuery );
