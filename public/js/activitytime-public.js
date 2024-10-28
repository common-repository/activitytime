(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

	$(function () {
		//$('.actt-pro').css('background', 'red');
        $('.actt-pro, .actt-pro a, .actt-pro button, .actt-pro input').unbind();

        $('.actt-pro, .actt-pro a, .actt-pro button, .actt-pro input').on('click', actt_gopremium);
        
        $('.actt-pro, .actt-pro a, .actt-pro button, .actt-pro input').on('focus', function(){
            $(this).trigger('blur'); 
            return false;
        });
	});


})( jQuery );



function actt_gopremium(){
	console.log(actt_script_parameters);
	jQuery.confirm({
		boxWidth: '400px',
		useBootstrap: false,
		title: actt_script_parameters.text.activation_popup_title,
		content: actt_script_parameters.text.activation_popup_content,
		buttons: {
			cancel: function () {
				return true;
			},
			somethingElse: {
				text: 'Purchase Now',
				btnClass: 'btn-blue',
				keys: ['enter', 'shift'],
				action: function(){

					window.location = actt_script_parameters.actt_activation_link;

					return false;
				}
			}
		}
	});

	return false;
}