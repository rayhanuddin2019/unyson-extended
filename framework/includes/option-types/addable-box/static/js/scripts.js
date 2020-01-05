jQuery(document).ready(function ($) {
	var optionTypeClass = '.attr-option-type-addable-box';

	var methods = {
		/** Make full/prefixed event name from short name */
		makeEventName: function(shortName) {
			return 'attr:option-type:addable-box:'+ shortName;
		},
		/** Create object with useful data about box for event data */
		getBoxDataForEvent: function($box) {
			var data = {};

			data.$box       = $box;
			data.$controls  = $box.find('.attr-option-box-controls:first');
			data.$options   = $box.find('.attr-option-box-options:first');

			data.$box       = $box.find('.attr-postbox:first');
			data.$title     = data.$box.find('> .hndle:first');
			data.$titleText = data.$title.find('> span:first');

			return data;
		},
		/** Make boxes to be sortable */
		reInitSortable: function ($boxes) {
			try {
				$boxes.sortable('destroy');
			} catch (e) {
				// happens when sortable was not initialized before
			}

			if (!$boxes.first().closest(optionTypeClass).hasClass('is-sortable')) {
				return false;
			}

			var isMobile = $(document.body).hasClass('mobile');

			$boxes.sortable({
				items: '> .attr-option-box',
				handle: '.hndle:first',
				cursor: 'move',
				placeholder: 'sortable-placeholder',
				delay: ( isMobile ? 200 : 0 ),
				distance: 2,
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				axis: 'y',
				start: function(e, ui){
					// Update the height of the placeholder to match the moving item.
					{
						var height = ui.item.outerHeight();

						height -= 2; // Subtract 2 for borders

						ui.placeholder.height(height);
					}
				},
				update: function(){
					var optionType = $(this).closest(optionTypeClass);

					optionType.trigger('change'); // for customizer

					attr.options.trigger.changeForEl(optionType);
				}
			});
		},

		/** Init boxes controls */
		initControls: function ($boxes) {
			$boxes
				.find('.attr-option-box-controls:not(.initialized)')
				.on('click', '.attr-option-box-control', function(e){
					e.preventDefault();
					e.stopPropagation(); // prevent open/close of the box (when the link is in box title bar)

					var $control  = $(this);
					var controlId = $control.attr('data-control-id');

					switch (controlId) {
						case 'delete':
							var $option = $control.closest(optionTypeClass);

							$control.closest('.attr-option-box').remove();

							methods.checkLimit($option);
							methods.updateHasBoxesClass($option);

							attr.options.trigger.changeForEl($option);

							break;
						default:
							// custom control. trigger event for others to handle this
							$control.closest(optionTypeClass).trigger(
								methods.makeEventName('control:click'), {
									controlId: controlId,
									$control: $control,
									box: methods.getBoxDataForEvent($control.closest('.attr-option-box'))
								}
							);
					}
				})
				.addClass('initialized')
				.find('.attr-option-box-control').off('click'); // remove e.stopPropagation() added by /wp-admin/js/postbox.min.js
		},
		checkLimit: function($option) {
			var $button = $option.find('> .attr-option-boxes-controls .attr-option-boxes-add-button');
			var limit = attr.intval($button.attr('data-limit'));

			if (limit > 0 && $option.find('> .attr-option-boxes > .attr-option-box').length >= limit) {
				$button.addClass('attr-hidden');
			} else {
				$button.removeClass('attr-hidden');
			}
		},
		updateHasBoxesClass: function($option) {
			$option[
				$option.find('> .attr-option-boxes > .attr-option-box:first').length
				? 'addClass' : 'removeClass'
			]('has-boxes');
		}
	};

	/**
	 * Update box title using the 'template' option parameter and box option values
	 */
	var titleUpdater = {
		pendingClass: 'attr-option-type-addable-box-pending-title-update',
		isBusy: false,
		template: function(template, vars) {
			try {
				return _.template(
					$.trim(template),
					undefined,
					{
						evaluate: /\{\{([\s\S]+?)\}\}/g,
						interpolate: /\{\{=([\s\S]+?)\}\}/g,
						escape: /\{\{-([\s\S]+?)\}\}/g
					}
				)(vars);
			} catch (e) {
				return '[Template Error] '+ e.message;
			}
		},
		/**
		 * Update the given box title, or find a pending box
		 * @public
		 */
		update: function($box) {
			if (this.isBusy) {
				return;
			}

			if (typeof $box == 'undefined') {
				$box = $(optionTypeClass +' .'+ this.pendingClass +':first');
			}

			if (!$box.length) {
				return;
			}

			var data = JSON.parse(
				$box.closest(optionTypeClass).attr('data-for-js')
			);

			data.template = $.trim(data.template);

			if (!data.template.length) {
				delete data;
				return;
			}

			var $dataWrapper = $box.closest('.attr-option-box');

			var values = $dataWrapper.attr('data-values');

			if (values) {
				// box after refresh
				$dataWrapper.removeAttr('data-values');

				$box.removeClass(titleUpdater.pendingClass);

				var jsonParsedValues = JSON.parse(values) || {};

				$box.find('> .hndle span:not([class])').first().html(
					this.template(data.template, $.extend({}, {o: jsonParsedValues}, jsonParsedValues))
				);

				delete data;
				delete jsonParsedValues;
				this.update();
				return;
			}

			this.isBusy = true;

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: [
					'action=attr_backend_options_get_values',
					'options='+ encodeURIComponent(JSON.stringify(data.options)),
					'name_prefix='+ encodeURIComponent($dataWrapper.attr('data-name-prefix')),
					$box.find('> .inside > .attr-option-box-options').find('input, select, textarea').serialize()
				].join('&'),
				dataType: 'json'
			}).done(_.bind(function (response, status, xhr) {
				this.isBusy = false;
				$box.removeClass(titleUpdater.pendingClass);

				var template = '';

				if (response.success) {
					template = this.template(data.template, $.extend({}, {o: response.data.values}, response.data.values));
				} else {
					template = '[Ajax Error] '+ response.data.message
				}

				$box.find('> .hndle span:not([class])').first().html(template);

				delete data;

				this.update();
			}, this)).fail(_.bind(function (xhr, status, error) {
				this.isBusy = false;
				$box.removeClass(titleUpdater.pendingClass);

				$box.find('> .hndle span:not([class])').first().text('[Server Error] '+ status +': '+ error.message);

				delete data;

				this.update();
			}, this));
		}
	};

	attrEvents.on('attr:options:init', function (data) {
		var $elements = data.$elements.find(optionTypeClass +':not(.attr-option-initialized)');

		$elements.toArray().map(function (el) {
			attr.options.on.change(function (data) {
				if (! $(data.context).is(
					'[data-attr-option-type="addable-box"] .attr-option-boxes > .attr-option-box'
				)) {
					return;
				}

				// Listen to just its own virtual contexts
				if (! el.contains(data.context)) {
					return;
				}

				attr.options.trigger.changeForEl(el);
			});
		});

		/** Init Add button */
		$elements.on('click', '> .attr-option-boxes-controls > .attr-option-boxes-add-button', function(){
			var $button   = $(this);
			var $option   = $button.closest(optionTypeClass);
			var $boxes    = $option.find('.attr-option-boxes:first');
			var increment = parseInt($button.attr('data-increment'));

			var $newBox = $(
				$option.find('> .default-box-template').attr('data-template')
					.split( $button.attr('data-increment-placeholder') ).join( String(increment) )
			);

			$button.attr('data-increment', increment + 1);

			// animation
			{
				$newBox.addClass('attr-animation-zoom-in');

				setTimeout(function(){
					$newBox.removeClass('attr-animation-zoom-in');
				}, 300);
			}

			$boxes.append($newBox);

			// Re-render wp-editor
			if (
				window.attrWpEditorRefreshIds
				&&
				$newBox.find('.attr-option-type-wp-editor:first').length
			) {
				$newBox.find(
					'.attr-option-type-wp-editor textarea'
				).toArray().map(function (textarea) {
					attrWpEditorRefreshIds(
						$(textarea).attr('id'),
						$newBox
					);
				});
			}

			methods.initControls($newBox);

			if ($option.hasClass('is-sortable')) {
				methods.reInitSortable($boxes);
			}

			// remove focus form "Add" button to prevent pressing space/enter to add easy many boxes
			$newBox.find('input,select,textarea').first().focus();

			attrEvents.trigger('attr:options:init', {$elements: $newBox});

			var box = methods.getBoxDataForEvent($newBox);

			$option.trigger(methods.makeEventName('box:init'), {box: box});

			methods.checkLimit($option);
			methods.updateHasBoxesClass($option);

			attr.options.trigger.changeForEl($boxes);
		});

		// close postboxes and attach event listener
		$elements.find('> .attr-option-boxes > .attr-option-box > .attr-postbox').addClass('closed');

		$elements.on('attr:box:close', '> .attr-option-boxes > .attr-option-box > .attr-postbox', function(){
			// later a script will pick it by this class and will update the title via ajax
			$(this).addClass(titleUpdater.pendingClass);

			/*
			$(this).find('> .hndle span:not([class])').first().html(
				$('<img>').attr('src', attr.img.loadingSpinner)
			);
			*/

			titleUpdater.update($(this));
		});

		methods.initControls($elements);

		$elements.each(function(){
			methods.checkLimit($(this));
		});

		$elements.addClass('attr-option-initialized');

		setTimeout(function(){
			// executed later, after .sortable('destroy') from backend-options.js
			methods.reInitSortable($elements.find('.attr-option-boxes'));

			// execute box:init event for existing boxes
			$elements.each(function(){
				var $option = $(this);

				$option.find('> .attr-option-boxes > .attr-option-box').each(function(){
					$option.trigger(methods.makeEventName('box:init'), {
						box: methods.getBoxDataForEvent($(this))
					});
				})
			});
		}, 100);

		titleUpdater.update();
	});

	attr.options.register('addable-box', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			var promise = $.Deferred();

			attr.whenAll(
				$(optionDescriptor.el).find(
					'.attr-option-boxes'
				).first().find(
					'> .attr-option-box.attr-backend-options-virtual-context'
				).toArray().map(attr.options.getContextValue)
			).then(function (valuesAsArray) {
				promise.resolve({
					value: valuesAsArray.map(function (singleContextValue) {
						return singleContextValue.value;
					}),

					optionDescriptor: optionDescriptor
				})
			});

			return promise;
		}
	})
});
