/**
 * Included on pages where backend options are rendered
 */

var attrBackendOptions = {
	
	openTab: function(tabId) { console.warn('deprecated'); }
};

jQuery(document).ready(function($){
	var localized = _attr_backend_options_localized;

	/**
	 * Functions
	 */
	{
		
		function addPostboxToggles($boxes) {
			
			$boxes.find('h2, h3, .handlediv').off('click.postboxes');

			var eventNamespace = '.attr-backend-postboxes';

			
			$boxes
				.off('click'+ eventNamespace) 
				.on('click'+ eventNamespace, '> .hndle, > .handlediv', function(e){
					var $box = $(this).closest('.attr-postbox');

					if ($box.parent().is('.attr-backend-postboxes') && !$box.siblings().length) {
						
						$box.removeClass('closed');
					} else {
						$box.toggleClass('closed');
					}

					var isClosed = $box.hasClass('closed');

					$box.trigger('attr:box:'+ (isClosed ? 'close' : 'open'));
					$box.trigger('attr:box:toggle-closed', {isClosed: isClosed});
				});
		}

		/** Remove box header if title is empty */
		function hideBoxEmptyTitles($boxes) {
			$boxes.find('> .hndle > span').each(function(){
				var $this = $(this);

				if (!$.trim($this.html()).length) {
					$this.closest('.postbox').addClass('attr-postbox-without-name');
				}
			});
		}
	}

	/** Init tabs */
	(function(){
		var htmlAttrName = 'data-attr-tab-html',
			initTab = function($tab) {
				var html;

				if (html = $tab.attr(htmlAttrName)) {
					attrEvents.trigger('attr:options:init', {
						$elements: $tab.removeAttr(htmlAttrName).html(html),
						/**
						 * Sometimes we want to perform some action just when
						 * lazy tabs are rendered. It's important in those cases
						 * to distinguish regular attr:options:init events from
						 * the ones that will render tabs. Passing by this little
						 * detail may break some widgets because attr:options:init
						 * event may be fired even when tabs are not yet rendered.
						 *
						 * That's how you can be sure that you'll run a piece
						 * of code just when tabs will be arround 100%.
						 *
						 * attrEvents.on('attr:options:init', function (data) {
						 *   if (! data.lazyTabsUpdated) {
						 *     return;
						 *   }
						 *
						 *   // Do your business
						 * });
						 *
						 */
						lazyTabsUpdated: true
					});
				}
			},
			initAllTabs = function ($el) {
				var selector = '.attr-options-tab[' + htmlAttrName + ']', $tabs;

				
				$el.each(function(){
					if ($(this).is(selector)) {
						initTab($(this));
					}
				});

				// initialized tabs can contain tabs, so init recursive until nothing is found
				while (($tabs = $el.find(selector)).length) {
					$tabs.each(function(){ initTab($(this)); });
				}
			};

		attrEvents.on('attr:options:init:tabs', function (data) {
			initAllTabs(data.$elements);
		});

		attrEvents.on('attr:options:init', function (data) {
			var $tabs = data.$elements.find('.attr-options-tabs-wrapper:not(.initialized)');

			if (localized.lazy_tabs) {
				$tabs.tabs({
					create: function (event, ui) {
						initTab(ui.panel);
					},
					activate: function (event, ui) {
						initTab(ui.newPanel);
						ui.newPanel.closest('.attr-options-tabs-contents')[0].scrollTop = 0
					}
				});

				$tabs
					.closest('form')
					.off('submit.attr-tabs')
					.on('submit.attr-tabs', function () {
						if (!$(this).hasClass('prevent-all-tabs-init')) {
							// All options needs to be present in html to be sent in POST on submit
							initAllTabs($(this));
						}
					});
			} else {
				$tabs.tabs({
					activate: function (event, ui) {
						ui.newPanel.closest('.attr-options-tabs-contents')[0].scrollTop = 0
					}
				});
			}

			$tabs.each(function () {
				var $this = $(this);

				if (!$this.parent().closest('.attr-options-tabs-wrapper').length) {
					// add special class to first level tabs
					$this.addClass('attr-options-tabs-first-level');
				}
			});

			$tabs.addClass('initialized');
		});
	})();

	/** Init boxes */
	attrEvents.on('attr:options:init', function (data) {
		var $boxes = data.$elements.find('.attr-postbox:not(.initialized)');

		hideBoxEmptyTitles(
			$boxes.filter('.attr-backend-postboxes > .attr-postbox')
		);

		addPostboxToggles($boxes);

		/**
		 * leave open only first boxes
		 */
		$boxes
			.filter('.attr-backend-postboxes > .attr-postbox:not(.attr-postbox-without-name):not(:first-child):not(.prevent-auto-close)')
			.addClass('closed');

		$boxes.addClass('initialized');

		// trigger on box custom event for others to do something after box initialized
		$boxes.trigger('attr-options-box:initialized');
	});

	/** Init options */
	attrEvents.on('attr:options:init', function (data) {
		data.$elements.find('.attr-backend-option:not(.initialized)')
			// do nothing, just a the initialized class to make the fadeIn css animation effect
			.addClass('initialized');
	});

	/** Fixes */
	attrEvents.on('attr:options:init', function (data) {
		{
			var eventNamespace = '.attr-backend-postboxes';

			data.$elements.find('.postbox:not(.attr-postbox) .attr-option')
				.closest('.postbox:not(.attr-postbox)')

				/**
				 * Add special class to first level postboxes that contains framework options (on post edit page)
				 */
				.addClass('postbox-with-attr-options')

				/**
				 * Prevent event to be propagated to first level WordPress sortable (on edit post page)
				 * If not prevented, boxes within options can be dragged out of parent box to first level boxes
				 */
				.off('mousedown'+ eventNamespace) // remove already attached (happens when this script is executed multiple times on the same elements)
				.on('mousedown'+ eventNamespace, '.attr-postbox > .hndle, .attr-postbox > .handlediv', function(e){
					e.stopPropagation();
				});
		}

		/**
		 * disable sortable (drag/drop) for postboxes created by framework options
		 * (have no sense, the order is not saved like for first level boxes on edit post page)
		 */
		{
			var $sortables = data.$elements
				.find('.postbox:not(.attr-postbox) .attr-postbox, .attr-options-tabs-wrapper .attr-postbox')
				.closest('.attr-backend-postboxes')
				.not('.attr-sortable-disabled');

			$sortables.each(function(){
				try {
					$(this).sortable('destroy');
				} catch (e) {
					// happens when not initialized
				}
			});

			$sortables.addClass('attr-sortable-disabled');
		}

		/** hide bottom border from last option inside box */
		{
			data.$elements.find('.postbox-with-attr-options > .inside, .attr-postbox > .inside')
				.append('<div class="attr-backend-options-last-border-hider"></div>');
		}

		hideBoxEmptyTitles(
			data.$elements.find('.postbox-with-attr-options')
		);
	});

	/**
	 * Help tips (i)
	 */
	(function(){
		attrEvents.on('attr:options:init', function (data) {
			var $helps = data.$elements.find('.attr-option-help:not(.initialized)');

			attr.qtip($helps);

			$helps.addClass('initialized');
		});
	})();

	$('#side-sortables').addClass('attr-force-xs');
});
