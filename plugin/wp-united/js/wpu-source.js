/**
 * 
 * This is the source file for WP-United JavaScript
 * Currently there isn't a lot of JS used by WP-United, but it is likely to increase in the future
 * By default, this file is ignored -- the minified wpu-min.js is called instead when needed.
 * This file is just here for your convenience should you want to modify WP-United.
 * If you want to have this file load during development, just rename it to wpu-min.js.
 */

function wpuSmlAdd(){
	var tas, ta, tb; 
	ta=null;
	
	text = this.firstChild.getAttribute('alt');
	
	// We try to detect the comment textarea
	tas = document.getElementsByTagName('textarea');
	
	if(tas.length>1) {
		for(var i=0; i<tas.length; i++) {
			if(tas[i].id == 'comment' ) {
				ta=tas[i];
			} else if((tas[i].name == 'comment') && (ta==null)) {
				ta=tas[i];
			} else if((tas[i].className == 'comment') && (ta == null)) {
				ta=tas[i];
			}
		}
		if(ta == null) {
			for(i=0;i<tas.length;i++) {
				try{
					if(tas[i].gotFocus)ta=tas[i];
				} catch(e){ 
				}
			}
		}
		if(ta==null) {
			ta=tas[0];
		}
	
	} else if(tas.length==1) {
		ta=tas[0];
	}
	
	if(ta==null) {
		alert(replace('%s', text, wpuLang['wpu_smiley_error']));
	}

	// We now have a text area
	if (document.selection){ // for IE
		ta.focus();
		sel=document.selection.createRange();
		sel.text= ' ' + text + ' ';
	} else if (ta.selectionStart || ta.selectionStart == 0) { // for decent browsers
		 ta.value = ta.value.substring(0, ta.selectionStart) + ' ' + text + ' ' + ta.value.substring(ta.selectionEnd, ta.value.length);
	} else { // fall back to just dumping the smiley at the end
		 ta.value+= ' '+text+' ';
	}
	return false;
}

	// show / hide additional smilies
function wpuSmlMore() {
	document.getElementById('wpu-smiley-more').style.display='inline';
	var toggle = document.getElementById('wpu-smiley-toggle');
	toggle.setAttribute("onclick", "return wpuSmlLess()");
	toggle.firstChild.nodeValue ="\u00AB\u00A0" + wpuLang['wpu_less_smilies'];
	return false;
	}
    
function wpuSmlLess() {
	document.getElementById('wpu-smiley-more').style.display='none';
	var toggle = document.getElementById('wpu-smiley-toggle');
	toggle.setAttribute("onclick", "return wpuSmlMore();");
	toggle.firstChild.nodeValue = wpuLang['wpu_more_smilies'] + "\u00A0\u00BB";
	return false;
}

// apply smilies if they exist
if(document.getElementById("wpusmls")) {
	var smlCont = document.getElementById("wpusmls");
	var smls = smlCont.getElementsByTagName('a');
	for(var wpuI=0; wpuI<smls.length;wpuI++) {
		if(smls[wpuI].id != 'wpu-smiley-toggle') { 
			smls[wpuI].onclick = wpuSmlAdd;
		}
	}
}