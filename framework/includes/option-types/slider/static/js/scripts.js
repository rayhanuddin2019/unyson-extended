(function ($, attrEvents) {
	var defaults = {
		grid: true
	};

	attrEvents.on('attr:options:init', function (data) {
		data.$elements.find('.attr-option-type-slider:not(.initialized)').each(function () {
			var options = JSON.parse($(this).attr('data-attr-irs-options'));
			var slider = $(this).find('.attr-irs-range-slider').ionRangeSlider(_.defaults(options, defaults));
		}).addClass('initialized');
	});

})(jQuery, attrEvents);
