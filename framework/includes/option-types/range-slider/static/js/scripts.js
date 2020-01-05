(function ($, attrEvents) {
	var defaults = {
		grid: true
	};

	attrEvents.on('attr:options:init', function (data) {
		data.$elements.find('.attr-option-type-range-slider:not(.initialized)').each(function () {
			var options = JSON.parse($(this).attr('data-attr-irs-options'));
			$(this).find('.attr-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));

			$(this).find('.attr-irs-range-slider').on('change', _.throttle(function (e) {
				attr.options.trigger.changeForEl(e.target, {
					value: getValueForEl(e.target)
				})
			}, 300));
		}).addClass('initialized');
	});

	attr.options.register('range-slider', {
		startListeningForChanges: $.noop,
		getValue: function (optionDescriptor) {
			return {
				value: getValueForEl(
					$(optionDescriptor.el).find('[type="text"]')[0]
				),

				optionDescriptor: optionDescriptor
			}
		}
	});

	function getValueForEl (el) {
		var rangeArray = el.value.split(';');

		return {
			from: rangeArray[0],
			to: rangeArray[1]
		}
	}

})(jQuery, attrEvents);
