/**
 * WP-United JavaScript for the Settings Panel
 */
 
 
(function($) {
    $.QueryString = (function(a) {
        if (a == "") return {};
        var b = {};
        for (var i = 0; i < a.length; ++i)
        {
            var p=a[i].split('=');
            if (p.length != 2) continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(window.location.search.substr(1).split('&'))
})(jQuery);


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
	$('#wputabs').tabs({
		select: function(event, ui) {                   
			window.location.hash = ui.tab.hash;
		}
    });
	$('#phpbbpathchange').button();	
	$('#wputpladvancedstgs').button();	
	$('.wpuwhatis').button();	
	
	var selTab = $.QueryString['tab']; 
	if(selTab != undefined) {
		 $('#wputabs').tabs('select', '#' + selTab); 
	}

	
	
	
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
	if($('#wputplint').is(':checked')) {
		$('#wpusettingstpl').show();
		if($('#wputplrev').is(':checked')) {
			$('#wputemplate-w-in-p-opts').hide();
		} else {
			$('#wputemplate-p-in-w-opts').hide();
		}
	}
	
	$('input[name=rad_tpl]').change(function() {
		$('#wputemplate-p-in-w-opts').toggle();
		$('#wputemplate-w-in-p-opts').toggle();
	});

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
	$('#wpusetup-submit').hide();
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
	$('#wpusetup-submit').show();			
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
function wpu_transmit(type, formID, urlToRefresh) {
	$('#wpustatus').hide();
	window.scrollTo(0,0);
	$('#wputransmit').dialog({
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
			window.location = 'admin.php?page=' + type + '&msg=success' + '&tab=' + window.location.hash.replace('#', '');
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
		if(transmitMessage == '') {
			transmitMessage = blankPageMsg;
		}
		// prevent recursive ajax error:
		$(document).ajaxError(function() {
			// TODO: if server 500 error or disable, try direct delete method
			window.location = 'admin.php?page=wp-united-setup&msg=fail&msgerr=' + makeMsgSafe(transmitMessage);
		}); 
		$.post('index.php', disable, function(response) {
			// the connection has been disabled, redirect
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
	msg = msg.replace(/\+/ig, '%2B');
	msg = msg.replace(/\=/ig, '%3D');
	msg = msg.replace(/\//ig, '%2F');
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
	
	/**
	 * Delegates actions via a single event listener (to improve performance)
	 * Uses native JS rather than jQuery for the most part in order to keep event actions
	 * as speedy as possible.
	 */
	document.getElementById('wpumapscreen').onclick = function(event) {
		var el = event.target || event.srcElement;
		var elType = el.nodeName.toLowerCase();
		
		if(elType == 'a') {
			
			if(el.className.indexOf('wpuprofilelink') > -1) {
				$.colorbox({
					href: el.href,
					width: '88%', 
					height: '92%', 
					title: (mapProfileTitle == undefined) ? '' : mapProfileTitle,
					iframe: true
				});	
			}
			return false;
			
		}
		
		// now deal with buttons
			
		if((elType != 'span') || (el.className.indexOf('ui-button') == -1)) {
			return false;
		}
		el = el.parentNode;
		if(el.className.indexOf('ui-button-disabled') > -1) {
			return false;
		}

		if( (el.id == undefined) || (el.id == '') ) {
				
			if(el.className.indexOf('wpumapactionedit') > -1) {
				$.colorbox({
					href: el.href,
					width: '88%', 
					height: '92%', 
					title: (mapEditTitle == undefined) ? '' : mapEditTitle,
					iframe: true,
					onClosed: function() {
						wpuShowMapper(false);
					}
				});
				return false;
			}
			
			return false;
		}
		
		// only remaining possibility is a map action button
		wpuProcessMapActionButton(el.id);
		
		return false;
		
	};	
	

	$("#wpumapdisp select").bind('change', function() {
		wpuShowMapper(true);
	});
	wpuShowMapper(true);
}

var wpuEndPoint;
var wpuNeverEndPoint;
function wpuSetupPermsMapper() {
	
	$('#wputabs').tabs({
		select: function(event, ui) {                   
			window.location.hash = ui.tab.hash;
		},
		show: function(event, ui) {
			jsPlumb.repaintEverything();
		}
    });
	
	jsPlumb.importDefaults({
		DragOptions : { cursor: 'pointer', zIndex:2000 },
		PaintStyle : { strokeStyle:'#666' },
		EndpointStyle : { width:20, height:16, strokeStyle:'#666' },
		Container : $('#wpuplumbcanvas')
	});	
	
	wpuEndPoint = {
		endpoint:['Dot', { radius:15 }],
		paintStyle:{ fillStyle:'#000061' },
		scope:'wpuplumb',
		connectorStyle:{ strokeStyle:'#000061', lineWidth:6 },
		connector: ['Bezier', { curviness:63 } ],
		maxConnections:10,
	};
	wpuNeverEndPoint = {
		endpoint:['Rectangle', { width:15, height: 15 }],
		paintStyle:{ fillStyle:'#dd0000' },
		scope:'wpuplumbnever',
		connectorStyle:{ strokeStyle:'#dd0000', lineWidth:6 },
		connector: ['Bezier', { curviness:63 } ],
		maxConnections:10
	};	

	initPlumbing();	
	
}


function wpuApplyPerms() {
	
	var connections = jsPlumb.getConnections('wpuplumb');
	var nevers = jsPlumb.getConnections('wpuplumbnever');
	var results = [];
	for(var i=0;i<connections.length;i++) {
		results.push(connections[i].sourceId.split(/-/g)[1] + '=' + connections[i].targetId.split(/-/g)[1]);
	}
	var resultsNever = [];
	for(var i=0;i<nevers.length;i++) {
		resultsNever.push(nevers[i].sourceId.split(/-/g)[1] + '=' + nevers[i].targetId.split(/-/g)[1]);
	}	
	
	window.scrollTo(0,0);
	$('#wpu-reload').dialog({
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
	$('#wpu-desc').html('<strong>Processing permission mappings...</strong><br />Please wait...');
	
	//TODO: setup error handler here
	
	$.post('admin.php?page=wpu-user-mapper', 'wpusetperms=' + makeMsgSafe(results.join(',')) + '&wpusetnevers=' + makeMsgSafe(resultsNever.join(',')) + '&_ajax_nonce=' + firstMapActionNonce, function(response) { 
		if(response=='OK') {
			// the settings were applied
		}
				
		$('#wpu-reload').dialog('destroy');
		window.location.reload();
		
	});
	return false;
	;
	
	return false;
}

function wpuClearPerms() {
	window.scrollTo(0,0);
	$('#wpu-desc').html('<strong>Clearing changes</strong><br />Please wait...');
	$("#wpu-reload").dialog({
		modal: true,
		title: 'Resetting...',
		width: 360,
		height: 160,
		draggable: false,
		disabled: true,
		closeOnEscape: false,
		resizable: false
	});
	$('.ui-dialog-titlebar').hide();
	window.location.reload(1);
}



/**
 * Sends the filter fields to the back-end, processes the returned user mapper html, and
 * sets up all contained buttons/fields/etc.
 */
var selContainsCurrUser = false;
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
	
	
	$.post('admin.php?page=wpu-user-mapper', formData, function(response, status, xhr) {

		// Set up the page when a user mapper response has been received
		if($('#wpumapside').val() == 'phpbb') {
			leftSide = phpbbText;
			rightSide = wpText;
		} else {
			leftSide = wpText;
			rightSide = phpbbText; 
		}
		var pag = $(response).find('pagination').text();
		var bulk = $(response).find('bulk').text();
		$('#wpumappaginate1').html(pag);
		$('#wpumappaginate2').html(bulk + pag);
		// wrap content in an additional div to speed DOM insertion
		
		$('#wpuoffscreen').html($(response).find('mapcontent').text());

		
		setTimeout('setupMapButtons()', 200);
		setTimeout('makeMapVisible()', 1000);

		wpuMapClearAll();
		wpuSuggCache = {};
		wpuTypedMatches = new Array();
		
		// set up autocompletes
		$('#wpumaptable input.wpuusrtyped').each(function() {
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
		
		currAction = 0;

	});
}

function makeMapVisible() {
	$('#wpumapscreen').html('');
	$('#wpumapscreen').append($('#wpumaptable'));
	
}


function wpuProcessMapActionButton(btnID) {

		var actionDetails = btnID.split(/-/g);
		
		if(actionDetails.length < 2) {
			return false;
		}
		
		var intUsrID, intUsrName;
		var mapAction = actionDetails[1];
		var pkg = actionDetails[2];
		var altPkg = (pkg == 'wp') ? 'phpbb' : 'wp';
		var usrID = actionDetails[3];
		
		var usrName = $('#wpu' + pkg + 'login' + usrID).text();
		
		switch(mapAction) {
			case 'del':
				return wpuMapDel(usrID, pkg, usrName);
				break;
			
			case 'delboth':
				intUsrID = actionDetails[4];
				intUsrName = $('#wpu' + altPkg + 'login' + intUsrID).text();
				return wpuMapDelBoth(usrID, intUsrID, usrName, intUsrName);
				break;
				
			case 'create':
				return wpuMapCreate(usrID, altPkg, usrName);
				break;
				
			case 'break':
				intUsrID = actionDetails[4];
				intUsrName = $('#wpu' + altPkg + 'login' + intUsrID).text();
				return wpuMapBreak(usrID, intUsrID, usrName, intUsrName);
				break;
		}
		
		return false;	
	
}




/**
 * Progressively enhances links into buttons
 */

function setupMapButtons() {
	$('#wpumaptable a.wpumapactionbrk').button({ 
		icons: {primary:'ui-icon-scissors'},
		text: false
	});
	$('#wpumaptable a.wpumapactioncreate').button({ 
		icons: {primary: 'ui-icon-plusthick'},
		text: false
	});		
	$('#wpumaptable a.wpumapactiondel').button({ 
		icons: {primary:'ui-icon-trash'},
		text: false
	});
	$('#wpumaptable  a.wpumapactionlnk').button({ 
		icons: {primary:'ui-icon-link'},
		text: false
	});
	$('#wpumaptable a.wpumapactionlnktyped').button({ 
		icons: {primary:'ui-icon-link'},
		text: false,
		disabled: true
	});
	$('#wpumaptable a.wpumapactionedit').button({ 
		icons: {primary:'ui-icon-gear'},
		text: false
	});	

	//$('.wpubuttonset').buttonset();
	
}

/**
 * Process a bulk action
 */
function wpuMapBulkActions() {
	var bulkType = $('#wpuquicksel').val();
	
	switch(bulkType) {
		
		case 'del':
			$('#wpumaptable .wpuintegnot a.wpumapactiondel').each(function() {
				if(!$(this).button('widget').hasClass('ui-button-disabled')) {
					wpuProcessMapActionButton($(this).attr('id'));
				}
			});
		break;
		
		
		case 'create':
			$('#wpumaptable .wpuintegnot a.wpumapactioncreate').each(function() {
				if(!$(this).button('widget').hasClass('ui-button-disabled')) {
					wpuProcessMapActionButton($(this).attr('id'));
				}
			});		
		break;
		
		case 'break':
			$('#wpumaptable .wpuintegok a.wpumapactionbrk').each(function() {
				if(!$(this).button('widget').hasClass('ui-button-disabled')) {
					wpuProcessMapActionButton($(this).attr('id'));
				}
			});		
		break;		
		
		
	}
	
	
	
	return false;
	
}



/**
 * Sets up popup "Colourboxes" for phpBB ACP administration from the permissions tab
 */
function setupAcpPopups() {
	$('#wpumapscreen a.wpuacppopup, #wpumaptab-perms a.wpuacppopup').colorbox({
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
				resizable: false
			});
			$('.ui-dialog-titlebar').hide();
			window.location.reload(1);
		}
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
		$("#wpumapcontainer .vsplitbar").css('display', 'none');
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
			$("#wpumapcontainer .vsplitbar").css('display', 'block');
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
	
	
	var pckg = $('#wpumapside').val();
	if( ((pckg == 'wp') && ((userID == currWpUser) || (toUserID == currPhpbbUser))) ||
		 ((pckg == 'phpbb') && ((userID == currPhpbbUser) || (toUserID == currWpUser))) ) {
			 selContainsCurrUser = true;
	}
	
	wpuMapActions.push({
			'type': 'integrate',
			'userid': userID,
			'intuserid': toUserID,
			'desc': actionType + ' ' + actionDets,
			'package': pckg
		});
		$('#wpupanelactionlist').append(markup);

		$('#wpuuser' + userID).find('a.ui-button:not(.wpumapactionedit)').button('disable');	
		
		if($(el).attr('id').indexOf('wpumapfrom') > -1) {
			$('#' + $(el).attr('id').replace('wpumapfrom', 'wpumapsearch')).attr('disabled', 'disabled');
			$(el).unbind('click');
		}
	
	 return false;
}

/**
 * Generates a "break integration" action
 */
function wpuMapBreak(userID, intUserID, userName, intUserName) {

	showPanel();
	var actionType = actionBreak;
	var actionDets = actionBreakDets.replace('%1$s', '<em>' + userName + '</em>')
			.replace('%2$s', '<em>' + intUserName + '</em>');
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';

	var pckg = $('#wpumapside').val();
	if( ((pckg == 'wp') && ((userID == currWpUser) || (intUserID == currPhpbbUser))) ||
		 ((pckg == 'phpbb') && ((userID == currPhpbbUser) || (intUserID == currWpUser))) ) {
			 selContainsCurrUser = true;
	}



	wpuMapActions.push({
		'type': 'break',
		'userid': userID,
		'intuserid': intUserID,
		'desc': actionType + ' ' + actionDets,
		'package': pckg
	});
	$('#wpupanelactionlist').append(markup);

	$('#wpuuser' + userID).find('a.ui-button:not(.wpumapactionedit)').button('disable');
			
	return false;
}

/**
 * Generates a "delete from both sides" action
 */
function wpuMapDelBoth(userID, intUserID, userName, intUserName) {
	
	showPanel();
	var actionType = actionDelBoth;
	var actionDets = actionDelBothDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', leftSide)
		.replace('%3$s', '<em>' + intUserName + '</em>')
		.replace ('%4$s', rightSide);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';


	var pckg = $('#wpumapside').val();
	if( ((pckg == 'wp') && ((userID == currWpUser) || (intUserID == currPhpbbUser))) ||
		 ((pckg == 'phpbb') && ((userID == currPhpbbUser) || (intUserID == currWpUser))) ) {
			 selContainsCurrUser = true;
	}
	
	wpuMapActions.push({
		'type': 'delboth',
		'userid': userID,
		'intuserid': intUserID,
		'desc': actionType + ' ' + actionDets,
		'package': pckg
	});
	$('#wpupanelactionlist').append(markup);
	$('#wpuuser' + userID).find('a.ui-button:not(.wpumapactionedit)').button('disable');
	
	return false;
}

/**
 * Generates a "delete user" action
 */
function wpuMapDel(userID, pckg, userName) {
	
	var txtPackage = (pckg == 'phpbb') ? phpbbText : wpText;
	showPanel();
	var actionType = actionDel;
	var actionDets = actionDelDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', txtPackage);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';

	if( ((pckg == 'wp') && (userID == currWpUser)) || ((pckg == 'phpbb') && (userID == currPhpbbUser)) ) {
			 selContainsCurrUser = true;
	}


	wpuMapActions.push({
		'type': 'del',
		'userid': userID,
		'desc': actionType + ' ' + actionDets,
		'package': pckg
	});
	$('#wpupanelactionlist').append(markup);
	
	// disable delboth links and clicked delete link, leave the other one
	var altPckg =  (pckg == 'phpbb') ? 'wp' : 'phpbb';
	$('#wpuuser' + userID).find('a.ui-button:not(.wpumapactionedit)').button('disable');
	$('#wpuuser' + userID).find('div.wpu' + altPckg + 'user a.wpumapactiondel').button('enable');
	
	$('#wpuavatartyped' + userID).html('');
	$('#wpumapsearch-' + userID).attr('disabled', 'disabled');
	
	return false;
}


/**
 * Generates a "Create user" action
 */
function wpuMapCreate(userID, altPckg, userName) {
	
	var txtAltPackage = (altPckg == 'phpbb') ? phpbbText : wpText;
	showPanel();
	var actionType = actionCreate;
	var actionDets = actionCreateDets
		.replace('%1$s', '<em>' + userName + '</em>')
		.replace ('%2$s', txtAltPackage);
	var actionsIndex= wpuMapActions.length;
	var markup = '<li id="wpumapaction' + actionsIndex + '"><strong>' + actionType + '</strong> ' + actionDets + '</li>';
	
	
	if( ((altPckg == 'wp') && (userID == currPhpbbUser)) ||
		 ((altPckg == 'phpbb') && (userID == currWpUser)) ) {
			 selContainsCurrUser = true;
	}
	
	
	wpuMapActions.push({
		'type': 'createin',
		'userid': userID,
		'desc': actionType + ' ' + actionDets,
		'package': altPckg
	});
	$('#wpupanelactionlist').append(markup);
	
	$('#wpuuser' + userID).find('a.ui-button:not(.wpumapactionedit)').button('disable');
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
	$('#wpumapscreen').find(
		'a.wpumapactionbrk, ' + 
		'a.wpumapactiondel, ' +
		'a.wpumapactionlnk, ' +
		'a.wpumapactioncreate'
	).button('enable');
	$('#wpumapscreen a.wpumapactionlnktyped').button('disable');
	$('#wpumapscreen a.wpuusrtyped').val('');
	$('#wpumapscreen input.wpuusrtyped').removeAttr('disabled');
	$('#wpumapscreen div.wpuavatartyped').html('');
	return false;
}

/**
* Handle user mapper change page request
*/
function wpuMapPaginate(el) {
	var numStart = (el.href.indexOf('start=') > -1) ? el.href.split('start=')[1] : 0
	$('#wpufirstitem').val(numStart);
	wpuShowMapper(false);
	return false;
}

/**
 * Process the user mapper acitons
 */
var numActions;
var currAction = 0;
function wpuProcess() {
	window.scrollTo(0,0);
	$('#wpu-reload').dialog({
		modal: true,
		title: 'Applying actions...',
		width: 360,
		height: 220,
		draggable: false,
		disabled: true,
		closeOnEscape: false,
		resizable: false,
		show: 'puff',
		buttons: {
			'Cancel remaining actions': function() {
				wpuProcessFinished();
			}
		}
	});
	$('#wpuldgimg').show();
	numActions = wpuMapActions.length;
	
	wpuNextAction(firstMapActionNonce);
	
	return false;
}

/**
 * Get the next mapper action in the queue
 */
function wpuNextAction(nonce) {
	el = $('#wpupanelactionlist li:first');
	if(el.length) {
		wpuProcessNext(el,nonce);
	} else {
		wpuProcessFinished();
	}
}

/**
 * Process the next mapper action in the queue
 */	
function wpuProcessNext(el, nonce) {
	var mapAction, actionData, postString;
	
	var currDesc = '';
	var nextMapActionNonce = 0;
	
	currAction++;
	mapAction = parseInt(el.attr('id').replace('wpumapaction', ''));
	$(el).remove();
		
	currDesc = wpuMapActions[mapAction]['desc'];
	$('#wpu-desc').html('<strong>Processing action ' + currAction + ' of ' + numActions + '</strong><br />' + currDesc);
	
	$(document).ajaxError(function(e, xhr, settings, exception) {
		if(exception == undefined) {
			var exception = 'Server ' + xhr.status + ' error. Please check your server logs for more information.';
		}
		$('#wpu-desc').html(errMsg = 'An error occurred. The remaining actions have not been processed. Error: ' + exception);
	});
		
	// fashion POST data from wpuMapActions
	actionData = new Array();
	for(actionKey in wpuMapActions[mapAction]) {
		if(actionKey != 'desc') {
			actionData.push(actionKey + '=' + wpuMapActions[mapAction][actionKey]);
		}
	}
	postString = actionData.join('&');
	postString += '&wpumapaction=1&_ajax_nonce=' + nonce;
	
	$.post('admin.php?page=wpu-user-mapper', postString, function(response) {
		var actionStatus = $(response).find('status').text();
		var actionDetails = $(response).find('details').text();
		var nextNonce = $(response).find('nonce').text();
		
		if(actionStatus=='OK') {
			wpuNextAction(nextNonce);
			
		} else {
			// handle error
			$('#wpu-reload').dialog('destroy');
			$('#wpu-desc').html(errMsg = 'An error occurred on the server. The remaining actions have not been processed. Error: ' + actionDetails);
			$('#wpu-reload').dialog({
				modal: true,
				title: 'Error',
				width: 360,
				height: 220,
				draggable: false,
				resizable: false,
				show: 'puff',
				buttons: {
					'OK': function() {
						wpuProcessFinished();
					}
				}
			});
			$('#wpuldgimg').hide();
		}
			
	});				

	return false;
}


/**
 * Finish processing mapping actions
 * Reload the page if the current user was affected
 */		
function wpuProcessFinished() {
	$('#wpu-reload').dialog('destroy');
	if(selContainsCurrUser) {
		window.location.reload();
	} else {
		wpuShowMapper(true);
	}
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


