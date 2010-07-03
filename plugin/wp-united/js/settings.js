/**
 * WP-United JavaScript for the Settings Panel
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

function setupSettingsPage() {
	$('#wputabs').tabs();	
	$('#phpbbpathchange').button();	
	$('#wputpladvancedstgs').button();	
	$('.wpuwhatis').button();	
}

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

function wpuCancelChange() {
	$('#phpbbpath').hide('slide');
	$('#phpbbpathchooser').show('slide');
	$('#txtchangepath').show();
	$('#txtselpath').hide();
	$('#wpucancelchange').hide();
	$('#wpusetup-submit').hide();			
	return false;
}



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
			
function tplAdv() {
	$('#wpusettingstpladv').toggle('slide');
	$('#wutpladvshow').toggle()
	$('#wutpladvhide').toggle();
	return false;
}

// disallow alpha chars in padding fields
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




// send settings
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
 * listen for ajax errors
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

function makeMsgSafe(msg) {
	msg = Base64.encode(msg)
	msg = msg.replace(/\+/ig, '[pls]');
	msg = msg.replace(/\=/ig, '[eq]');
	return escape(msg);
}

//disable WP-United
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
