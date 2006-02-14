/**
 * add onload events
 * pulled from http://www.tek-tips.com/faqs.cfm?fid=4862
 */

function addOnloadEvent(fnc){
	if ( typeof window.addEventListener != "undefined" )
		window.addEventListener( "load", fnc, false );
	else if ( typeof window.attachEvent != "undefined" ) {
		window.attachEvent( "onload", fnc );
	}
	else {
		if ( window.onload != null ) {
			var oldOnload = window.onload;
			window.onload = function ( e ) {
				oldOnload( e );
				window[fnc]();
			};
		}
		else {
			window.onload = fnc;
		}
	}
} // end addOnloadEvent();
