(function ($, _, attrEvents, window) {
	var popup = function () {
		var $this = $(this),
			$defaultItem = $this.find('.item.default'),
			nodes = {
				$optionWrapper: $this,
				$itemsWrapper: $this.find('.items-wrapper'),
				$disabledItem: $defaultItem.clone().removeClass('default').addClass('disabled'),
				getDefaultItem: function () {
					return $defaultItem.clone().removeClass('default');
				}
			},
			data = JSON.parse(nodes.$optionWrapper.attr('data-for-js')),
			utils = {
				modal: new attr.OptionsModal({
					title: data.title,
					options: data.options,
					size : data.size
				}),
				editItem: function (item, values) {
					var $input = item.find('input'),
						val = $input.val();

					$input.val( values = JSON.stringify( values ) );

					if (val != values) {
						$this.trigger('attr:option-type:popup:change');
						$input.trigger('change');
					}
				}
			};

		nodes.$itemsWrapper.on('click', '.item > .button', function (e) {
			e.preventDefault();

			var values = {},
				$item = $(this).closest('.item'),
				$input = $item.find('input');

			if ($input.length && $input.val().length ) {
				values = JSON.parse($input.val());
			}

			utils.modal.set('edit', true);
			utils.modal.set('values', values, {silent: true});
			utils.modal.set('itemRef', $item);
			utils.modal.open();
		});

		utils.modal.on({
			'change:values': function (modal, values) {
				utils.editItem(utils.modal.get('itemRef'), values);

                attr.options.trigger.changeForEl(utils.modal.get('itemRef'), {
					value: values
				});

				attrEvents.trigger('attr:option-type:popup:change', {
					element: $this,
					values: values
				});
			},
			'open': function () {
				$this.trigger('attr:option-type:popup:open');

				if (data['custom-events']['open']) {
					attrEvents.trigger('attr:option-type:popup:custom:' + data['custom-events']['open'], {
						element: $this,
						modal: utils.modal
					});
				}
			},
			'close': function () {
				$this.trigger('attr:option-type:popup:close');

				if (data['custom-events']['close']) {
					attrEvents.trigger('attr:option-type:popup:custom:' + data['custom-events']['close'], {
						element: $this,
						modal: utils.modal
					});
				}
			},
			'render': function () {
				$this.trigger('attr:option-type:popup:render');

				if (data['custom-events']['render']) {
					attrEvents.trigger('attr:option-type:popup:custom:' + data['custom-events']['render'], {
						element: $this,
						modal: utils.modal
					});
				}
			}
		});
	};

	attrEvents.on('attr:options:init', function (data) {
		data.$elements
			.find('.attr-option-type-popup:not(.attr-option-initialized)').each(popup)
			.addClass('attr-option-initialized');
	});

	attr.options.register('popup', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: JSON.parse(
					$(optionDescriptor.el).find('[type="hidden"]').val() || '""'
				),

				optionDescriptor: optionDescriptor
			}
		}
	});
})(jQuery, _, attrEvents, window);
