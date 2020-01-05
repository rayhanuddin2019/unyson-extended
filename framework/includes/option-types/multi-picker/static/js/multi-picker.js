(function($, attre) {

	attre.on('attr:options:init', function(data) {

		data.$elements
			.find(
				'.attr-option-type-multi-picker:not(.attr-option-initialized)'
			)
			.not(
				'.attr-option-type-multi-picker-dynamic'
			)
			.each(initSimpleMultiPicker)
			.addClass('attr-option-initialized');

		data.$elements
			.find(
				'.attr-option-type-multi-picker.attr-option-type-multi-picker-dynamic'
			)
			.not(
				'.attr-option-initialized'
			)
			.each(initDynamicMultiPicker)
			.addClass('attr-option-initialized');

	});

	attre.on('attr:options:teardown', function (data) {

		data.$elements
			.find(
				'.attr-option-type-multi-picker.attr-option-type-multi-picker-dynamic'
			).filter('.attr-option-initialized')
			.each(function () {
				if ($(this).data().attrPickerListener) {
					attr.options.off.change($(this).data().attrPickerListener);
				}
			})
	})

	function initDynamicMultiPicker () {
		var $container = $(this);

		$container.closest(
			'.attr-backend-option-type-multi-picker'
		).addClass('attr-option-type-multi-picker-dynamic-container');

		$container.addClass('attr-option-initialized');

		var optionDescriptor = attr.options.getOptionDescriptor($container[0]);

		var pickerDescriptor = attr.options.findOptionInSameContextFor(
			optionDescriptor.el,
			$container.attr('data-attr-dynamic-picker-path')
		);

		$container.find('> .choice-group').first().addClass('chosen');

		$container.data('attr-picker-listener', handleChange);

		attr.options.on.change($container.data().attrPickerListener);

		chooseGroupForOptionDescriptor(pickerDescriptor);

		function handleChange (optionDescriptor) {
			if (pickerDescriptor.el === optionDescriptor.el) {
				setTimeout(function () {
					chooseGroupForOptionDescriptor(optionDescriptor);
				}, 0);
			}
		}

		function chooseGroupForOptionDescriptor (optionDescriptor) {
			attr.options.getValueForEl(pickerDescriptor.el).then(function (value) {
				// TODO: implement interfaces for multiple compound option types
				if (pickerDescriptor.type === 'icon-v2') {
					chooseGroup(
						value.value.type === 'none' ? '' : value.value.type
					);
				} else {
					if (! _.isString(value.value)) {
						throw "Your picker returned a non-string value. In order for it to work with multi-pickers it should yield string values";
					}

					chooseGroup(value.value);
				}
			});

			function chooseGroup(groupId) {
				var $choicesGroups = $container.find('> .choice-group');

				var $choicesToReveal = $container.find(
					'.choice-group[data-choice-key="'+ groupId +'"]'
				);

				$choicesGroups.removeClass('chosen');
				$choicesToReveal.addClass('chosen');

				if ($choicesToReveal.length) {
					$container.addClass('has-choice');

					$container.closest(
						'.attr-backend-option-type-multi-picker'
					).addClass('attr-has-dynamic-choice');
				} else {
					$container.removeClass('has-choice');

					$container.closest(
						'.attr-backend-option-type-multi-picker'
					).removeClass('attr-has-dynamic-choice');
				}
			};
		}

	}

	function initSimpleMultiPicker() {
		var $this = $(this);

		var elements = {
			$pickerGroup: $this.find('> .picker-group'),
			$choicesGroups: $this.find('> .choice-group')
		};

		var chooseGroup = function(groupId) {
			var $choicesToReveal = elements.$choicesGroups.filter('.choice-group[data-choice-key="'+ groupId +'"]');

			/**
			 * The group options html was rendered in an attribute to make page load faster.
			 * Move the html from attribute in group and init options with js.
			 */
			if ($choicesToReveal.attr('data-options-template')) {
				$choicesToReveal.html(
					$choicesToReveal.attr('data-options-template')
				);

				$choicesToReveal.removeAttr('data-options-template');

				attrEvents.trigger('attr:options:init', {
					$elements: $choicesToReveal
				});
			}

			elements.$choicesGroups.removeClass('chosen');
			$choicesToReveal.addClass('chosen');

			if ($choicesToReveal.length) {
				$this.addClass('has-choice');
			} else {
				$this.removeClass('has-choice');
			}
		};


		var pickerType = elements.$pickerGroup.attr('class').match(/picker-type-(\S+)/)[1];

		var flows = {
			'switch': function() {
				elements.$pickerGroup.find(':checkbox').on('change', function() {
					var $this = $(this),
						checked = $(this).is(':checked'),
						value = JSON.parse($this.attr('data-switch-'+ (checked ? 'right' : 'left') +'-value-json'));

					chooseGroup(value);
				}).trigger('change');
			},
			'select': function() {
				elements.$pickerGroup.find('select').on('change', function() {
					chooseGroup(this.value);
				}).trigger('change');
			},
			'short-select': function() {
				this.select();
			},
			'radio': function() {
				elements.$pickerGroup.find(':radio').on('change', function() {
					chooseGroup(this.value);
				}).filter(':checked').trigger('change');
			},
			'image-picker': function() {
				elements.$pickerGroup.find('select').on('change', function() {
					chooseGroup(this.value);
				}).trigger('change');
			},
			'icon-v2': function () {
				var iconV2Selector = '.attr-option-type-icon-v2 > input';

				elements.$pickerGroup.find(iconV2Selector).on('change', function() {
					var type = JSON.parse(this.value)['type'];
					chooseGroup(type);
				}).trigger('change');
			}
		};

		if (! pickerType) {
			console.error('unknown multi-picker type:', pickerType);
		} else {
			if (flows[pickerType]) {
				flows[pickerType]();
			} else {
				var eventName = 'attr:option-type:multi-picker:init:'+ pickerType;

				if (attre.hasListeners(eventName)) {
					attre.trigger(eventName, {
						'$pickerGroup': elements.$pickerGroup,
						'chooseGroup': chooseGroup
					});
				} else {
					console.error('uninitialized multi-picker type:', pickerType);
				}
			}
		}
	};

	attr.options.register('multi-picker', {
		getValue: attr.options.get('multi').getValue
	})
})(jQuery, attrEvents);
