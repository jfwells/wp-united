/**
 * WP-United JavaScript for the Settings Panel
 */

/**
 * Creates a file tree for the user to select the phpBB location
 */
function createFileTree() {
	$('#phpbbpath').fileTree({ 
		root: '/',
		script: treeScript,
		multiFolder: false,
		loadMessage: "Loading..."
	}, function(file) { 
		var parts = file.split('/');
		if ((parts.length) > 1) {
			file = parts.pop();
		}
		if(file=='config.php') {
			var pth = parts.join('/') + '/'; 
			$("#phpbbpathshow").html(pth).css('color', 'green');
			$("#wpupathfield").val(pth);
			$('#phpbbpath').hide('slide');
			$('#txtchangepath').show();
			$('#txtselpath').hide();
			$('#wpucancelchange').hide();
			$('#phpbbpathchooser').show('slide');
			$('#wpusetup-submit').show();
			window.scrollTo(0,0);
		}
	});
}

/**
 * Initialises the settings page
 */
function setupSettingsPage() {
	$('#wputabs').tabs();	
	$('#phpbbpathchange').button();	
	$('#wputpladvancedstgs').button();	
	$('.wpuwhatis').button();	
}

/**
 * Sets the form fields up when a valid phpBB path is chosen in the filetree
 */
function setPath(type) {
	if(type=='setup') {
		$('#phpbbpath').hide();
		$('#phpbbpathchooser').button();
		$('#phpbbpathchooser').show();
		$('#txtchangepath').show();
		$('#txtselpath').hide();
	}
	$("#phpbbpathshow").html(phpbbPath).css('color', 'green');
	$("#wpupathfield").val(phpbbPath);
}

/**
 * Sets up the buttons for the help / what is this menu
 */
function setupHelpButtons() {
	$('.wpuwhatis').click(function() {
		$('#wpu-desc').text($(this).attr('title'));
		$("#wpu-dialog").dialog({
			modal: true,
			title: 'WP-United Help',
			buttons: {
				Close: function() {
					$(this).dialog('close');
				}
			}
		});
		return false;
	});	
}

/**
 * Sets the settings form dynamic elements to their initial states
 */
function settingsFormSetup() {
	if($('#wpuxpost').is(':checked')) $('#wpusettingsxpostxtra').show();
	if($('#wpuloginint').is(':checked')) $('#wpusettingsxpost').show();
	if($('#wputplint').is(':checked')) $('#wpusettingstpl').show();

	$('#wpuloginint').change(function() {
			$('#wpusettingsxpost').toggle("slide", "slow");
	});
	$('#wpuxpost').change(function() {
			$('#wpusettingsxpostxtra').toggle("slide", "slow");
	});
	
	setCSSMLevel(cssmVal);
	
	$('#wputplint').change(function() {
			$('#wpusettingstpl').toggle("slide", "slow");
			var slVal = ($(this).val()) ? 2 : 0;						
			setCSSMLevel(slVal);
			$("#wpucssmlvl").slider("value", slVal);
	});	

	$("#wpucssmlvl").slider({
		value: cssmVal,
		min: 0,
		max: 2,
		step: 1,
		change: function(event, ui) {
			setCSSMLevel(ui.value);
		}
	});	
	
}


/**
 * Re-displays the file tree when the user wants to change the phpBB path
 */
function wpuChangePath() {
	$('#phpbbpath').show('slide');
	$('#phpbbpathchooser').hide('slide');
	$('#txtchangepath').hide();
	$('#txtselpath').show();
	$('#wpucancelchange').show();
	$('#wpucancelchange').button();
	$('#wpusetup-submit').show();
	return false;
}

/**
 * Resets the fields and filetree when the user cancels changing the phpBB path
 */
function wpuCancelChange() {
	$('#phpbbpath').hide('slide');
	$('#phpbbpathchooser').show('slide');
	$('#txtchangepath').show();
	$('#txtselpath').hide();
	$('#wpucancelchange').hide();
	$('#wpusetup-submit').hide();			
	return false;
}


/**
 * Sets the CSS Magic / Template Vodoo options when the slider is moved
 */
function setCSSMLevel(level) {
	var lvl, desc;
	if(level == 0) {
		lvl = "Off";
		desc = "All automatic CSS integration is disabled";
	} else if(level == 1) {
		lvl = "Medium";
		desc = "CSS Magic is enabled, Template Voodoo is disabled: <ul><li>Styles are reset to stop outer styles applying to the inner part of the page.</li><li>Inner CSS is made more specific so it does affect the outer portion of the page.</li><li>Some HTML IDs and class names may be duplicated.</li></ul>";
	} else if(level == 2) {
		lvl = "Full";
		desc = "CSS Magic and Template Voodoo are enabled:<ul><li>Styles are reset to stop outer styles applying to the inner part of the page.</li><li>Inner CSS is made more specific so it does affect the outer portion of the page.</li><li>HTML IDs and class names that are duplicated in the inner and outer parts of the page are fixed.</li></ul>";							
	}
	$("#wpucssmlvlfield").val(level);
	$("#cssmlvltitle").html(lvl);
	$("#cssmlvldesc").html(desc);
	$("#cssmdesc").effect("highlight");
}
		
/**
 * Shows advanced template setings
 */	
function tplAdv() {
	$('#wpusettingstpladv').toggle('slide');
	$('#wutpladvshow').toggle()
	$('#wutpladvhide').toggle();
	return false;
}

/**
 * Prevents the user from typing alphanumeric characters in the padding fields
 */
function checkPadding(evt) {
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode( key );
	var regex = /[0-9]/;
	if( !regex.test(key) ) {
		theEvent.returnValue = false;
		if (theEvent.preventDefault) theEvent.preventDefault();
	}
}




/**
 * Sends the settings to phPBB
 */
function wpu_transmit(type, formID) {
	$('#wpustatus').hide();
	window.scrollTo(0,0);
	$("#wputransmit").dialog({
		modal: true,
		title: 'Connecting...',
		width: 360,
		height: 160,
		draggable: false,
		disabled: true,
		closeOnEscape: false,
		resizable: false,
		show: 'puff'
	});
	$('.ui-dialog-titlebar').hide();
	var formData;
	
	wpu_setup_errhandler();
	
	formData = $('#' + formID).serialize() +'&wpusettings-transmit=1&_ajax_nonce=' + transmitNonce;
	$.post('admin.php?page='+type, formData, function(response) { 
		if(response=='OK') {
			// the settings were applied
			window.location = 'admin.php?page=' + type + '&msg=success';
			return;
		}
		wpu_process_error(response);
	});
	return false;
}

/**
 * Listen for ajax errors
 */
function wpu_setup_errhandler() {
	$(document).ajaxError(function(e, xhr, settings, exception) {

		if(exception == undefined) {
			var exception = 'Server ' + xhr.status + ' error. Please check your server logs for more information.';
		}
		wpu_process_error(errMsg = settings.url + ' returned: ' + exception);
	
	});
	
}

/**
 * Processes various types of errors received during the ajax call
 * Messges prefixed with [ERROR] are handled errors
 * Other types are PHP errors, or server responses with unexpected content
 * Finally we also process non-300 rsponses from jQuery's ajaxError
 */
function wpu_process_error(transmitMessage) {
	// there was an uncatchable error, send a disable request
	if  (transmitMessage.indexOf('[ERROR]') == -1) {
		var disable = 'wpudisable=1&_ajax_nonce=' + disableNonce;
		$.post('index.php', disable, function(response) {
			// the connection has been disabled, redirect
			if(transmitMessage == '') {
				transmitMessage = blankPageMsg;
			}
			
			window.location = 'admin.php?page=wp-united-setup&msg=fail&msgerr=' + makeMsgSafe(transmitMessage);
		});
	} else {
		// we caught the error, redirect to setup page
		transmitMessage = transmitMessage.replace(/\[ERROR\]/g, '');
		window.location = 'admin.php?page=wp-united-setup&msg=fail&msgerr=' + makeMsgSafe(transmitMessage);
	}
}

/**
 * Sanitizes returned html so we can send it back as a request var
 */
function makeMsgSafe(msg) {
	msg = Base64.encode(msg)
	msg = msg.replace(/\+/ig, '[pls]');
	msg = msg.replace(/\=/ig, '[eq]');
	return escape(msg);
}

/**
 * The user wants to disable WP-United
 */
function wpu_manual_disable(type) {
	$("#wputransmit").dialog({
		modal: true,
		title: 'Connecting...',
		width: 360,
		height: 160,
		draggable: false,
		disabled: true,
		closeOnEscape: false,
		resizable: false,
		show: 'puff'
	});
	$('.ui-dialog-titlebar').hide();
	var disable = 'wpudisableman=1&_ajax_nonce=' + disableNonce;
	$.post('admin.php?page='+type, disable, function(response) {
		// the connection has been disabled, redirect
		window.location = 'admin.php?page='+type;
	});
	
	return false;
	
}

/**
 **********************************************************
 * User mapper 
 **********************************************************
 */

var leftSide, rightSide;
var wpuMapActions = new Array();
var wpuTypedMatches = new Array();
var wpuSuggCache;
var panelOpen = false;
var panelHidden = false;

/**
 * Initialises the user mapper page
 */
function setupUserMapperPage() {
	$('#wputabs').tabs();
	$('.wpuprocess').button({
		icons: {
			primary: 'ui-icon-transferthick-e-w'
		}
	});
	$('.wpuclear').button({
		icons: {
			primary: 'ui-icon-cancel'
		}
	});		
	setupAcpPopups();

	$("#wpumapdisp select").bind('change', function() {
		wpuShowMapper(true);
	});
	wpuShowMapper(true);
}


/**
 * Sends the filter fields to the back-end, processes the returned user mapper html, and
 * sets up all contained buttons/fields/etc.
 */
function wpuShowMapper(repaginate) {
	
	if(repaginate == true) {
		$('#wpufirstitem').val(0);
	}
	
	$('#wpumapscreen').html('<div class="wpuloading"><p>Loading</p><img src="' + imgLdg + '" /></div>');
	var formData = $('#wpumapdisp').serialize() + '&wpumapload=1&_ajax_nonce=' + mapNonce;
	
	// set up ajax error handler
	$(document).ajaxError(function(e, xhr, settings, exception) {
		if(exception == undefined) {
			var exception = 'Server ' + xhr.status + ' error. Please check your server logs for more information.';
		}
		$('#wpumapscreen').html(errMsg = settings.url + ' returned: ' + exception);
	});
	
	
	$.post('admin.php?page=wpu-user-mapper', formData, function(response) {
		
		// Set up the page when a user mapper response has been received
		if($('#wpumapside').val() == 'phpbb') {
			leftSide = phpbbText;
			rightSide = wpText;
		} else {
			leftSide = wpText;
			rightSide = phpbbText; 
		}

		$('#wpumapscreen').html($(response).find('mapcontent').text());
		$('.wpumappaginate').html($(response).find('pagination').text());
	
		setupUserEditPopups();
		
		// set up buttons
		$('.wpumapactionbrk').button({ 
			icons: {primary:'ui-icon-scissors'},
			text: false
		});
		$('.wpumapactioncreate').button({ 
			icons: {primary: 'ui-icon-plusthick'},
			text: false
		});		
		$('.wpumapactiondel').button({ 
			icons: {primary:'ui-icon-trash'},
			text: false
		});
		$('.wpumapactionlnk').button({ 
			icons: {primary:'ui-icon-link'},
			text: false
		});
		$('.wpumapactionlnktyped').button({ 
			icons: {primary:'ui-icon-link'},
			text: false,
			disabled: true
		});
		$('.wpumapactionedit').button({ 
			icons: {primary:'ui-icon-gear'},
			text: false
		});	
		
		//$('.wpubuttonset').buttonset();
		wpuMapClearAll();
		wpuSuggCache = {};
		wpuTypedMatches = new Array();
		
		// set up autocompletes
		$('.wpuusrtyped').each(function() {
			$(this).autocomplete({
				minLength: 2,
				source: function(request, response) {
					var findIn = ($('#wpumapside').val() == 'phpbb')  ? 'wp' : 'phpbb';
					if ( request.term in wpuSuggCache ) {
						response(wpuSuggCache[request.term]);
						return;
					}
					$.ajax({
						url: 'admin.php?page=wpu-user-mapper',
						dataType: 'json',
						data: 'term=' + request.term + '&_ajax_nonce=' + autofillNonce + '&pkg=' + findIn,
						success: function(recv) {
							wpuSuggCache[request.term] = recv;
							response(recv);
						}
					});
				},
				select: function(event, ui) {
					var buttonID = $(this).attr('id').replace('wpumapsearch', 'wpumapfrom');
					var userID = $(this).attr('id').split(/-/ig)[1];
					var userName = $('#wpuuser' + userID + ' .wpuprofilelink').text();
					
					if(ui.item.statuscode == 1) {
						
						$(this).val(ui.item.label);
						
						var details = {
							'username': userName,
							'touserid': ui.item.value,
							'tousername': ui.item.label,
							'toemail': ui.item.desc
						}
						wpuTypedMatches[userID] = details;
						
						$('#wpuavatartyped' + userID).html(ui.item.avatar);
						
						$('#' + buttonID).bind('click', function() {
							return wpuMapIntegrateTyped(this);
						});
						$('#' + buttonID).button('enable');
					} else {
						$('#' + buttonID).unbind('click');
						$('#' + buttonID).button('disable');
						$('#wpuavatartyped' + userID).html('');
					}
					return false;
				},
				focus: function(event, ui) {
					if(ui.item.statuscode == 1) {
						$(this).val(ui.item.label);
					}
					return false;
				}
			})
			.data('autocomplete')._renderItem = function(ul, item) {
				var statusColor = (item.statuscode == 0) ? 'red' : 'green';
				return $('<li></li>')
					.data('item.autocomplete', item )
					.append( '<a><small><strong>' + item.label + '</strong><br />' + item.desc + '<br /><em style="color: ' + statusColor + '">' + item.status + '</em></small></a>')
					.appendTo( ul );
			};
		});

	});
}

/**
 * Sets up popup "Colourboxes" for phpBB ACP administration from the permissions tab
 */
function setupAcpPopups() {
	$('.wpuacppopup').colorbox({
		width: '88%', 
		height: '92%', 
		title: (acpPopupTitle == undefined) ? '' : acpPopupTitle,
		iframe: true,
		onClosed: function() {
			window.scrollTo(0,0);
			$('#wpu-desc').html('<strong>Reloading setings from phpBB</strong><br />Please wait...');
			$("#wpu-reload").dialog({
				modal: true,
				title: 'Reloading settings from phpBB...',
				width: 360,
				height: 160,
				draggable: false,
				disabled: true,
				closeOnEscape: false,
				resizable: false,
			});
			$('.ui-dialog-titlebar').hide();
			window.location.reload(1);
		}
	});
}

/**
 * Sets up popup "Colourboxes" for phpBB ACP administration from the user mapper
 */
function setupUserEditPopups() {
	$('.wpumapactionedit').colorbox({
		width: '88%', 
		height: '92%', 
		title: (mapEditTitle == undefined) ? '' : mapEditTitle,
		iframe: true,
		onClosed: function() {
			wpuShowMapper(false);
		}
	});
	$('.wpuprofilelink').colorbox({
		width: '88%', 
		height: '92%', 
		title: (mapProfileTitle == undefined) ? '' : mapProfileTitle,
		iframe: true
	});	
}


/**
 * Displays the actions panel whenever an action is added
 */
function showPanel() {
	if(!panelOpen) {
		$('#wpumapcontainer').splitter({
			type: 'v',
			sizeRight: 225
		});
		$('#wpumapscreen').css('overflow-y', 'auto');
		$('#wpumappanel').show('slide', {
			direction: 'right'
		});
		$('#wpumappanel h3').prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>');
		$('#wpumappanel h3 .ui-icon').click(function() {
			togglePanel($(this));
		});
		
		panelOpen = true;
	}
	panelHidden = true;
	togglePanel($('#wpumappanel h3 .ui-icon'));
}

/**
* This doesn't really close the panel as the splitter doesn't have a destroy method
* instead we just set its width to zero
*/
function closePanel() {
	if(panelOpen) {
		$("#wpumapcontainer").trigger("resize", [ $("#wpumapcontainer").width() ]);
		$(".vsplitbar").css('display', 'none');
		panelHidden = true;
	}
}
/**
 * Toggles the actions panel between fully open and "almost hidden"
 */
function togglePanel(el) {
	if(!panelHidden) {
		el
			.removeClass('ui-icon-triangle-1-e')
			.addClass('ui-icon-triangle-1-w');
		 $("#wpumapcontainer").trigger("resize", [ $("#wpumapcontainer").width() - 20 ]);
		panelHidden = true;
	} else {
		el
			.removeClass('ui-icon-triangle-1-w')
			.addClass('ui-icon-triangle-1-e')
			$(".vsplitbar").css('display', 'block');
		$("#wpumapcontainer").trigger("resize", [ $("#wpumapcontainer").width() - 225 ]);
		panelHidden = false;
	}
}

/**
 * Converts an autocompleted user selection to the "integrate to this user" action
 */
function wpuMapIntegrateTyped(el) {
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
		
	var userID = $(el).attr('id').split(/-/ig)[1];
	
	if(userID in wpuTypedMatches) {
		return wpuMapIntegrate(el, userID, wpuTypedMatches[userID].touserid, wpuTypedMatches[userID].username, wpuTypedMatches[userID].tousername, '', wpuTypedMatches[userID].toemail);
	}
	return false;
}

/**
 * Generates an "integrate to this user" action.
 * Called directly if a suggestion is chosen, or called via wpuMapIntegrateTypes
 * if they used the autocomplete
 */
function wpuMapIntegrate(el, userID, toUserID, userName, toUserName, userEmail, toUserEmail) {
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
	showPanel();
	var actionType = actionIntegrate;
	var actionDets = actionIntegrateDets.replace('%1$s', leftSide)
		.replace ('%2$s','<em>' + userName + '</em>')
		.replace('%3$s', rightSide)
		.replace ('%4$s', '<em>' + toUserName + '</em>');
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	wpuMapActions.push({
			'type': 'integrate',
			'userid': userID,
			'intuserid': toUserID,
			'markup': markup
		});
		$('#wpupanelactionlist').append(markup);

		$('#wpuuser' + userID).find(
			'.wpumapactionbrk, .wpumapactiondel, .wpumapactionlnk, .wpumapactionlnktyped, .wpumapactioncreate'
		).button('disable');		
		
		if($(el).attr('id').indexOf('wpumapfrom') > -1) {
			$('#' + $(el).attr('id').replace('wpumapfrom', 'wpumapsearch')).attr('disabled', 'disabled');
			$(el).unbind('click');
		}
	
	 return false;
}

/**
 * Generates a "break integration" action
 */
function wpuMapBreak(el, userID, intUserID, userName, intUserName, userEmail, intUserEmail) {
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
	showPanel();
	var actionType = actionBreak;
	var actionDets = actionBreakDets.replace('%1$s', '<em>' + userName + '</em>')
			.replace('%2$s', '<em>' + intUserName + '</em>');
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	wpuMapActions.push({
		'type': 'break',
		'userid': userID,
		'intuserid': intUserID,
		'markup': markup
	});
	$('#wpupanelactionlist').append(markup);

	$('#wpuuser' + userID).find(
		'.wpumapactionbrk, .wpumapactiondel, .wpumapactionlnk, .wpumapactionlnktyped .wpumapactioncreate'
	).button("disable");
			
	return false;
}

/**
 * Generates a "delete from both sides" action
 */
function wpuMapDelBoth(el, userID, intUserID, userName, intUserName, userEmail, intUserEmail) {
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
	
	showPanel();
	var actionType = actionDelBoth;
	var actionDets = actionDelBothDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', leftSide)
		.replace('%3$s', '<em>' + intUserName + '</em>')
		.replace ('%4$s', rightSide);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	wpuMapActions.push({
		'type': 'delboth',
		'userid': userID,
		'intuserid': intUserID,
		'markup': markup
	});
	$('#wpupanelactionlist').append(markup);
	$('#wpuuser' + userID).find(
		'.wpumapactionbrk, .wpumapactiondel, .wpumapactionlnk, .wpumapactionlnktyped .wpumapactioncreate'
	).button("disable");
	
	return false;
}

/**
 * Generates a "delete user" action
 */
function wpuMapDel(el, userID, pckg, userName, userEmail) {
	
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
	
	
	var txtPackage = (pckg == 'phpbb') ? phpbbText : wpText;
	showPanel();
	var actionType = actionDel;
	var actionDets = actionDelDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', txtPackage);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	wpuMapActions.push({
		'type': 'del',
		'userid': userID,
		'markup': markup,
		'package': pckg
	});
	$('#wpupanelactionlist').append(markup);
	
	// disable delboth links and clicked delete link, leave the other one
	$('#wpuuser' + userID).find(
		'.wpumapactionbrk, ' + 
		'.wpu' + pckg + 'user .wpumapactiondel, ' +
		'.wpuintegok .wpumapactiondel, ' +
		'.wpuintegnot .wpumapactiondel, ' +
		'.wpumapactionlnk, ' + 
		'.wpumapactioncreate, ' +
		'.wpumapactionlnktyped, '
	).button('disable');
	$('#wpuavatartyped' + userID).html('');
	$('#wpumapsearch-' + userID).attr('disabled', 'disabled');
	
	return false;
}


/**
 * Generates a "Create user" action
 */
function wpuMapCreate(el, userID, altPckg, userName, userEmail) {
	
	if($(el).button("widget").hasClass('ui-state-disabled')) {
		return false;
	}
	
	var txtAltPackage = (altPckg == 'phpbb') ? phpbbText : wpText;
	showPanel();
	var actionType = actionCreate;
	var actionDets = actionCreateDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', txtAltPackage);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	wpuMapActions.push({
		'type': 'createin',
		'userid': userID,
		'markup': markup,
		'package': altPckg
	});
	$('#wpupanelactionlist').append(markup);
	
	// disable delboth links and clicked delete link, leave the other one
	$('#wpuuser' + userID).find(
		'.wpumapactionbrk, ' + 
		'.wpumapactiondel, ' +
		'.wpumapactionlnk, ' +
		'.wpumapactioncreate, ' +
		'.wpumapactionlnktyped'
	).button('disable');
	$('#wpuavatartyped' + userID).html('');
	$('#wpumapsearch-' + userID).attr('disabled', 'disabled');
	
	return false;
}	


/**
 * Clears all actions from the actions panel and closes it, and resets all button states
 */
function wpuMapClearAll() {
	wpuMapActions = new Array();
	$('#wpupanelactionlist').html('');
	closePanel();
	$('.wpumapactionbrk, .wpumapactiondel, .wpumapactionlnk, .wpumapactioncreate').button('enable');
	$('.wpumapactionlnktyped').button('disable');
	$('.wpuusrtyped').val('');
	$('.wpuusrtyped').removeAttr('disabled');
	$('.wpuavatartyped').html('');
	return false;
}




/**
 * Base64 encode/decode for passing messages
 */
var Base64 = {
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}
