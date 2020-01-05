<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @see ATTR_Settings_Form::_form_render()
 * @var ATTR_Settings_Form $form
 * @var array $values
 * @var string $input_name_reset
 * @var string $input_name_save
 * @var string $js_form_selector Form CSS selector safe to be used in js (js escaped)
 * @var bool $is_theme_settings Backwards compatibility with old Theme Settings hooks
 */
?>

<?php if ($form->get_is_side_tabs()): ?>
	<div class="attr-settings-form-header attr-row">
		<div class="attr-col-xs-12 attr-col-sm-6">
			<h2><?php echo $form->get_string('title'); ?></h2>
		</div>
		<div class="attr-col-xs-12 attr-col-sm-6">
			<div class="form-header-buttons">
				<?php
				/**
				 * Make sure firs submit button is Save button
				 * because the first button is "clicked" when you press enter in some input
				 * and the form is submitted.
				 * So to prevent form Reset on input Enter, make Save button first in html
				 */

				echo attr_html_tag('input', array(
					'type' => 'submit',
					'name' => $input_name_save,
					'class' => 'attr-hidden',
				));
				?>
				<?php
				echo implode(
					'<i class="submit-button-separator"></i>',
					apply_filters(
						$is_theme_settings
							? 'attr_settings_form_header_buttons'
							: 'attr:settings-form:'. $form->get_id() .':side-tabs:header-buttons',
						array(
							attr_html_tag('input', array(
								'type' => 'submit',
								'name' => $input_name_reset,
								'value' => $form->get_string('reset_button'),
								'class' => 'button-secondary button-large submit-button-reset attr-settings-form-reset-btn',
							)),
							attr_html_tag('input', array(
								'type' => 'submit',
								'name' => $input_name_save,
								'value' => $form->get_string('save_button'),
								'class' => 'button-primary button-large submit-button-save',
							))
						)
					)
				);
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			attrEvents.one('attr:options:init', function(data){
				$('<?php echo $js_form_selector ?>').on(
					'attr:settings-form:reset attr:settings-form:init-header',
					function(){
						$(this).find('.attr-settings-form-header:not(.initialized)').addClass('initialized');
					}
				).trigger('attr:settings-form:init-header');
			});
		});
	</script>
<?php endif; ?>

<?php echo attr()->backend->render_options($form->get_options(), $values); ?>

<div class="form-footer-buttons">
<!-- This div is required to follow after options in order to have special styles in case options will contain tabs (css adjacent selector + ) -->
<?php echo implode(
	$form->get_is_side_tabs() ? ' ' : ' &nbsp;&nbsp; ',
	apply_filters(
		$is_theme_settings
			? 'attr_settings_form_footer_buttons'
			: 'attr:settings-form:'. $form->get_id() .':side-tabs:footer-buttons',
		array(
			attr_html_tag('input', array(
				'type' => 'submit',
				'name' => $input_name_save,
				'value' => $form->get_string('save_button'),
				'class' => 'button-primary button-large',
			)),
			attr_html_tag('input', array(
				'type' => 'submit',
				'name' => $input_name_reset,
				'value' => $form->get_string('reset_button'),
				'class' => 'button-secondary button-large attr-settings-form-reset-btn',
			))
		)
	)
); ?>
</div>

<!-- reset warning -->
<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document.body ).on(
			'click.attr-settings-form-reset-warning',
			'<?php echo $js_form_selector ?> input[name="<?php echo esc_js( $input_name_reset ) ?>"]',
			function ( e ) {
				/**
				 * on confirm() the submit input looses focus
				 * attrForm.isAdminPage() must be able to select the input to send it in _POST
				 * so use alternative solution http://stackoverflow.com/a/5721762
				 */
				$( this ).closest( 'form' ).find( '[clicked]:submit' ).removeAttr( 'clicked' );
				$( this ).attr( 'clicked', '' );

				var resetWarning = '<?php echo esc_js( $form->get_string( 'reset_warning' ) ); ?>',
					data         = { 'reset_warning': resetWarning };

				if ( ! confirm( resetWarning ) ) {
					e.preventDefault();
					$( document.body ).trigger( 'attr:settings-form:cancel-reset', data );
					$( this ).removeAttr( 'clicked' );
				} else {
					$( document.body ).trigger( 'attr:settings-form:before-reset', data );
				}
			}
		);
	} );
</script>
<!-- end: reset warning -->

<script type="text/javascript">
	jQuery(function($){
		var $form = $('<?php echo $js_form_selector ?>:first'),
			timeoutId = 0;

		$form.on('change.attr_settings_form_delayed_change', function(){
			clearTimeout(timeoutId);
			/**
			 * Run on timeout to prevent too often trigger (and cpu load) when a bunch of changes will happen at once
			 */
			timeoutId = setTimeout(function () {
				$form.trigger('attr:settings-form:delayed-change');
			}, 333);
		});
	});
</script>

<?php if ($form->get_is_ajax_submit()): ?>
<!-- ajax submit -->
<div id="attr-settings-form-ajax-save-extra-message"
     data-html="<?php echo attr_htmlspecialchars(apply_filters(
		$is_theme_settings
			? 'attr_settings_form_ajax_save_loading_extra_message'
			: 'attr:settings-form:'. $form->get_id() .':ajax-submit:extra-message',
		''
     )) ?>"></div>
<script type="text/javascript">
	jQuery(function ($) {
		function isReset($submitButton) {
			return $submitButton.length && $submitButton.attr('name') == '<?php echo esc_js($input_name_reset) ?>';
		}

		var formSelector = '<?php echo $js_form_selector ?>',
			loadingExtraMessage = $('#attr-settings-form-ajax-save-extra-message').attr('data-html'),
			loadingModalId = 'attr-options-ajax-save-loading';

		$(formSelector).addClass('prevent-all-tabs-init'); 

		attrForm.initAjaxSubmit({
			selector: formSelector,
			loading: function(elements, show) {
				if (show) {
					var title, description;

					if (isReset(elements.$submitButton)) {
						title = '<?php echo esc_js(__('Resetting', 'attr')) ?>';
						description =
							'<?php echo esc_js(__('We are currently resetting your settings.', 'attr')) ?>'+
							'<br/>'+
							'<?php echo esc_js(__('This may take a few moments.', 'attr')) ?>';
					} else {
						title = '<?php echo esc_js(__('Saving', 'attr')) ?>';
						description =
							'<?php echo esc_js(__('We are currently saving your settings.', 'attr')) ?>'+
							'<br/>'+
							'<?php echo esc_js(__('This may take a few moments.', 'attr')); ?>';
					}

					attr.soleModal.show(
						loadingModalId,
						'<h2 class="attr-text-muted">'+
							'<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAAAAACo4kLRAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAnRSTlMA/1uRIrUAAAACYktHRADdUu+NWwAAAAd0SU1FB+EKCgYABDchIukAAACZelRYdFJhdyBwcm9maWxlIHR5cGUgZ2lmOnhtcCBkYXRheG1wAAAImU2NMQ5CMQxD957iHyGNnaTlNkVtEQMSAwPHJx8WHMleXuxyu+/L+/E85niNzHJ85b1gRwvx6vCrWxCGBkdNF1WVaD5DODkx2H0Fgkmv5I2dxFQ9mRKiy3seMdBZCZ4lnUqjQYj/GRW3LPm95w42VvkACzYml9QYNjwAAACjSURBVBjTfZChDoNAEETfbZpgVqJPXHIO0w+hCZYPxJKUD6mpIzmBPrmmigoQcCSM2eRlszM7buWqxzbSd8Y0NgEAtwJ5WvYd39Y7TKOB7xkW0C6AQB4N6KuqB2zMIDDZ0cQmENJ2b8i/AYAl4db3pwj0fAlzmXJGsBIagl4/kitShFjCiNCUsEEI/sx8QKA9WWkLAnV3oNrVN9VtJZtyKrnUH9vgMmhlXVedAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE3LTEwLTEwVDA2OjAwOjA0LTA3OjAw4kWx4AAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxNy0xMC0xMFQwNjowMDowNC0wNzowMJMYCVwAAAAASUVORK5CYII=" alt="Loading" class="wp-spinner" /> '+
							title +
						'</h2>'+
						'<p class="attr-text-muted"><em>'+ description +'</em></p>'+ loadingExtraMessage,
						{
							autoHide: 60000,
							allowClose: false
						}
					);

					return 500; 
				} else {
				
				}
			},
			afterSubmitDelay: function (elements) {
				attrEvents.trigger('attr:options:init:tabs', {$elements: elements.$form});
			},
			onErrors: function( elements, data ) {
				var message = $.map( data.errors, function( mssg ) { return '<p class="attr-text-danger">' + mssg + '</p>' } ) + attr.soleModal.renderFlashMessages( data.flash_messages );

				attr.soleModal.hide( loadingModalId );

				attr.soleModal.show(
					'attr-options-ajax-save-error',
					'<p class="attr-text-danger">' + message + '</p>'
				);

			},
			onAjaxError: function(elements, data) {
				{
					var message = String(data.errorThrown);

					if (data.jqXHR.responseText && data.jqXHR.responseText.indexOf('Fatal error') > -1) {
						message = $(data.jqXHR.responseText).text().split(' in ').shift();
					}
				}

				attr.soleModal.hide(loadingModalId);
				attr.soleModal.show(
					'attr-options-ajax-save-error',
					'<p class="attr-text-danger">'+ message +'</p>'
				);
			},
			onSuccess: function(elements, ajaxData) {
				/**
				 * Display messages
				 */

				do {
					/**
					 * Don't display the "Settings successfully saved" message
					 * users will click often on the Save button, it's obvious it was saved if no error is shown.
					 */
					delete ajaxData.flash_messages.success.attr_settings_form_save;

					if (
						_.isEmpty(ajaxData.flash_messages.error)
						&&
						_.isEmpty(ajaxData.flash_messages.warning)
						&&
						_.isEmpty(ajaxData.flash_messages.info)
						&&
						_.isEmpty(ajaxData.flash_messages.success)
					) {
						// no messages to display
						break;
					}

					var noErrors = _.isEmpty(ajaxData.flash_messages.error) && _.isEmpty(ajaxData.flash_messages.warning);

					// remove success messages, do not make user wait
					ajaxData.flash_messages = _.omit(ajaxData.flash_messages, 'success');

					var modalHtml = attr.soleModal.renderFlashMessages(ajaxData.flash_messages);

					if (modalHtml.length) {
						attr.soleModal.show(
							'attr-options-ajax-save-success',
							'<div style="margin: 0 35px;">' + modalHtml + '</div>',
							{
								autoHide: noErrors
									? 1000 // hide fast the message if everything went fine
									: 10000,
								showCloseButton: false,
								hidePrevious: noErrors ? false : true // close and open popup when there are errors
							}
						);
					} else {
						attr.soleModal.hide('attr-options-ajax-save-success');
					}
				} while(false);

				/**
				 * Refresh form html on Reset
				 */
				if (isReset(elements.$submitButton)) {
					jQuery.ajax({
						type: "GET",
						dataType: 'text'
					}).done(function(html){
						attr.soleModal.hide(loadingModalId);

						var $form = jQuery(formSelector, html);
						html = undefined; // not needed anymore

						if (!$form.length) {
							alert('Can\'t find the form in the ajax response');
							return;
						}

						// waitSoleModalFadeOut -> formFadeOut -> formReplace -> formFadeIn
						setTimeout(function(){
							elements.$form.css('transition', 'opacity ease .3s');
							elements.$form.css('opacity', '0');
							elements.$form.trigger('attr:settings-form:before-html-reset');
							attrEvents.trigger('attr:options:teardown', {$elements: elements.$form});

							setTimeout(function() {
								var scrollTop = jQuery(window).scrollTop();

								// replace form html
								{
									elements.$form.css({
										'display': 'block',
										'height': elements.$form.height() +'px'
									});
									elements.$form.get(0).innerHTML = $form.get(0).innerHTML;
									$form = undefined; // not needed anymore
									elements.$form.css({
										'display': '',
										'height': ''
									});
								}

								attrEvents.trigger('attr:options:init', {$elements: elements.$form});

								jQuery(window).scrollTop(scrollTop);

								// fadeIn
								{
									elements.$form.css('opacity', '');
									setTimeout(function(){
										elements.$form.css('transition', '');
										elements.$form.css('visibility', '');
									}, 300);
								}

								elements.$form.trigger('attr:settings-form:reset');
							}, 300);
						}, 300);
					}).fail(function(jqXHR, textStatus, errorThrown){
						attr.soleModal.hide(loadingModalId);
						elements.$form.css({
							'opacity': '',
							'transition': '',
							'visibility': ''
						});
						console.error(jqXHR, textStatus, errorThrown);
						alert('Ajax error (more details in console)');
					});
				} else {
					attr.soleModal.hide(loadingModalId);
					elements.$form.trigger('attr:settings-form:saved');
				}
			}
		});
	});
</script>
<!-- end: ajax submit -->
<?php endif; ?>

<?php if (
	$form->get_is_side_tabs()
	&&
	apply_filters(
		$is_theme_settings
		? 'attr:settings-form:side-tabs:open-all-boxes'
		: 'attr:settings-form:'. $form->get_id() .':side-tabs:open-all-boxes',
		true
	)
): ?>
<!-- open all postboxes -->
<script type="text/javascript">
	jQuery(function ($) {
		var execTimeoutId = 0;

		attrEvents.on('attr:options:init', function(data){
			// use timeout to be executed after the script from backend-options.js
			clearTimeout(execTimeoutId);
			execTimeoutId = setTimeout(function(){
				// undo not first boxes auto close
				data.$elements.find(
					'<?php echo $js_form_selector ?> .attr-backend-postboxes > .attr-postbox:not(:first-child)'
				).removeClass('closed');
			}, 10);
		});
	});
</script>
<?php endif; ?>

<?php if (!empty($_GET['_focus_tab'])): ?>
<script type="text/javascript">
	jQuery(function($){
		attrEvents.one('attr:options:init', function(){
			setTimeout(function(){
				$('<?php echo $js_form_selector ?> a[href="#<?php echo esc_js($_GET['_focus_tab']); ?>"]')
					.trigger('click');
			}, 90);
		});
	});
</script>
<?php endif; ?>

<?php do_action(
	$is_theme_settings
		? 'attr_settings_form_footer'
		: 'attr:settings-form:'. $form->get_id() .':footer'
); ?>
