jQuery( document ).ready( function ( $ ) {
	setTimeout( function () {
		attrEvents.trigger( 'attr:options:init', {
			$elements: $( document.body )
		} );
	}, 30 );

	function updateContent( $content ) {
		if ( tinymce.get( 'content' ) ) {
			tinymce.get( 'content' ).setContent( $content );
		} else {
			$content.val( $content );
		}
	}

	$( '#post-preview' ).on( 'mousedown touchend', function () {

		var $content      = $( '#content' ),
			$contentValue = tinymce.get( 'content' ) ? tinymce.get( 'content' ).getContent() : $content.val(),
			$session      = '<!-- <attr_preview_session>' + new Date().getTime() + '</attr_preview_session> -->';

		if ( $contentValue.indexOf( '<!-- <attr_preview_session>' ) !== -1 ) {
			$contentValue = $contentValue.replace( /<!-- <attr_preview_session>(.*?)<\/attr_preview_session> -->/gi, $session );
		} else {
			$contentValue = $contentValue + $session;
		}

		updateContent( $contentValue );
		updateContent( $contentValue.replace( /<!-- <attr_preview_session>(.*?)<\/attr_preview_session> -->/gi, '' ) );
	} );
} );