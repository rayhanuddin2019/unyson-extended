(function ($) {

	attr.options.register('attr-undefined', {
		startListeningForChanges: defaultStartListeningForChanges,
		getValue: defaultGetValue
	});

	function defaultGetValue (optionDescriptor) {
		var resultPromise = $.Deferred();

		/**
		 * If we get a really undefined option type.
		 */
		if (optionDescriptor.type === 'attr-undefined') {
			resultPromise.resolve({
				value: '',
				optionDescriptor: optionDescriptor
			})

			return resultPromise;
		}

		// 1. find all inputs and ignore virtual contexts
		//    this really should include nested options and properly serialize
		//    them together
		//
		//    we should serialize those inputs into an object, based on their
		//    name attribute
		var formInstance = new FormSerializer($, optionDescriptor.el);

		var inputValues = formInstance.addPairs(
			findInputsFromAContextAndIgnoreVirtualScopes(
				optionDescriptor.el
			).serializeArray()
		).serialize();

		// 2. remove name_prefixes from those inputs
		//    optionsDescriptor.id === 'laptop'
		//    name="attr_options[nesting][laptop]"
		//
		//    This step should get
		//      inputValues['attr_options']['nesting']['laptop']

		inputValues = inputValues[
			Object.keys(inputValues)[0]
		];

		if (optionDescriptor.pathToTheTopContext.length > 0) {
			var IDs = optionDescriptor.pathToTheTopContext.map(
				attr.options.getOptionDescriptor
			);

			IDs.map(function (localDescriptor) {
				inputValues = inputValues[localDescriptor.id];
			});
		}

		var options = {};

		options[optionDescriptor.id] = JSON.parse(jQuery(optionDescriptor.el).attr(
			'data-attr-for-js'
		)).option;

		// 3. construct an AJAX request with correct options and input values
		$.ajax({
			type: 'POST',
			dataType: "json",
			url: ajaxurl,
			data: {
				action: 'attr_backend_options_get_values',
				name_prefix: 'attr_options',
				options: [
					options
				],
				attr_options: inputValues
			}
		})
			.then(function (response, status, request) {
				if (response.success && request.status === 200) {
					resultPromise.resolve(
						{
							value: response.data.values[optionDescriptor.id],
							optionDescriptor: optionDescriptor
						}
					);
				} else {
					resultPromise.reject();
				}
			})
			.fail(function () {
				// TODO: pass a reason
				resultPromise.reject();
			});

		return resultPromise;
	}

	// By default, for unknown option types do listening only once
	function defaultStartListeningForChanges (optionDescriptor) {
		if (optionDescriptor.el.classList.contains('attr-listening-started')) {
			return;
		}

		optionDescriptor.el.classList.add('attr-listening-started');

		listenToChangesForCurrentOptionAndPreserveScoping(
			optionDescriptor.el,
			_.throttle(function (e) {
				attr.options.trigger.changeForEl(e.target);
			}, 300)
		);

		if (optionDescriptor.hasNestedOptions) {
			attr.options.on.changeByContext(optionDescriptor.el, function (nestedDescriptor) {
				attr.options.trigger.changeForEl(optionDescriptor.el);
			});
		}
	}

	/**
	 * TODO
	 * rewrite that with:
	 *
	 * - Array.filter
	 * - Array.includes
	 * - addEventListener
	 * - querySelectorAll
	 */
	function listenToChangesForCurrentOptionAndPreserveScoping (el, callback) {
		jQuery(el).find(
			'input, select, textarea'
		).not(
			jQuery(el).find(
				'.attr-backend-option-descriptor input'
			).add(
				jQuery(el).find(
					'.attr-backend-option-descriptor select'
				)
			).add(
				jQuery(el).find(
					'.attr-backend-option-descriptor textarea'
				)
			).add(
				jQuery(el).find(
					'.attr-backend-options-virtual-context input'
				)
			).add(
				jQuery(el).find(
					'.attr-backend-options-virtual-context select'
				)
			).add(
				jQuery(el).find(
					'.attr-backend-options-virtual-context textarea'
				)
			)
		).on('change', callback);
	}

	function findInputsFromAContextAndIgnoreVirtualScopes (el) {
		return jQuery(el).find(
			'input, select, textarea'
		).not(
			jQuery(el).find(
				'.attr-backend-options-virtual-context input'
			).add(
				jQuery(el).find(
					'.attr-backend-options-virtual-context select'
				)
			).add(
				jQuery(el).find(
					'.attr-backend-options-virtual-context textarea'
				)
			)
		).not(
			jQuery(el).find(
				'.attr-filter-from-serialization input'
			).add(
				jQuery(el).find(
					'.attr-filter-from-serialization select'
				)
			).add(
				jQuery(el).find(
					'.attr-filter-from-serialization textarea'
				)
			)
		);
	}

	/**
	 * USAGE:
	 *
	 * var formInstance = new FormSerializer(jQuery, document.body);
	 *
	 * formInstance.addPairs(jQuery('input').serializeArray());
	 * formInstance.serialize();
	 */
	function FormSerializer(helper, $form) {
		var patterns = {
			push:     /^$/,
			fixed:    /^\d+$/,
			validate: /^[a-z][a-z0-9_-]*(?:\[(?:\d*|[a-z0-9_-]+)\])*$/i,
			key:      /[a-z0-9_-]+|(?=\[\])/gi,
			named:    /^[a-z0-9_-]+$/i
		};

		// private variables
		var data     = {},
			pushes   = {};

		// private API
		function build(base, key, value) {
			base[key] = value;
			return base;
		}

		function makeObject(root, value) {
			var keys = root.match(patterns.key), k;

			// nest, nest, ..., nest
			while ((k = keys.pop()) !== undefined) {
				// foo[]
				if (patterns.push.test(k)) {
					var idx = incrementPush(root.replace(/\[\]$/, ''));
					value = build([], idx, value);
				}

				// foo[n]
				else if (patterns.fixed.test(k)) {
					value = build([], k, value);
				}

				// foo; foo[bar]
				else if (patterns.named.test(k)) {
					value = build({}, k, value);
				}
			}

			return value;
		}

		function incrementPush(key) {
			if (pushes[key] === undefined) {
				pushes[key] = 0;
			}

			return pushes[key]++;
		}

		function encode(pair) {
			switch ($('[name="' + pair.name + '"]', $form).attr("type")) {
				case "checkbox":
					return pair.value === "on" ? true : pair.value;
				default:
					return pair.value;
			}
		}

		function addPair(pair) {
			if (! patterns.validate.test(pair.name)) return this;

			var obj = makeObject(pair.name, encode(pair));
			data = helper.extend(true, data, obj);

			return this;
		}

		function addPairs(pairs) {
			if (!helper.isArray(pairs)) {
				throw new Error("formSerializer.addPairs expects an Array");
			}
			for (var i=0, len=pairs.length; i<len; i++) {
				this.addPair(pairs[i]);
			}
			return this;
		}

		function serialize() {
			return data;
		}

		// public API
		this.addPair = addPair;
		this.addPairs = addPairs;
		this.serialize = serialize;
	};

})(jQuery);
