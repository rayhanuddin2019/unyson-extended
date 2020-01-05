(function($, attre) {
	//jQuery.attrDatetimepicker.setLocale(jQuery('html').attr('lang').split('-').shift());

	var init = function() {
		var $container = $(this),
			$input = $container.find('.attr-option-type-text'),
			data = {
				options: $container.data('datetime-attr'),
				el: $input,
				container: $container
			};

		attre.trigger('attr:options:datetime-picker:before-init', data);

		$input.attrDatetimepicker(data.options)
			.on('change', function (e) {
				attr.options.trigger.changeForEl(
					jQuery(e.target).closest('[data-attr-option-type="datetime-picker"]'), {
						value: e.target.value
					}
				)
			});
	};

	attr.options.register('datetime-picker', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: $(optionDescriptor.el).find(
					'[data-attr-option-type="text"]'
				).find('> input').val(),
				optionDescriptor: optionDescriptor
			}
		}
	})

	attre.on('attr:options:init', function(data) {
		data.$elements
			.find('.attr-option-type-datetime-picker').each(init)
			.addClass('attr-option-initialized');
	});

})(jQuery, attrEvents);
