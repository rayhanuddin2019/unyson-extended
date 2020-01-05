<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

{
	if (!isset($option['label'])) {
		$option['label'] = attr()->backend->option_type($option['type'])->_default_label(
			$id, $option
		);
	}

	if (!isset($option['desc'])) {
		$option['desc'] = '';
	}
}

{
	$help = false;

	if (!empty($option['help'])) {
		$help = array(
			'icon'  => 'info',
			'html'  => '{undefined}',
		);

		if (is_array($option['help'])) {
			$help = array_merge($help, $option['help']);
		} else {
			$help['html'] = $option['help'];
		}

		switch ($help['icon']) {
			case 'info':
				$help['class'] = 'dashicons dashicons-info';
				break;
			case 'video':
				$help['class'] = 'dashicons dashicons-video-alt3';
				break;
			default:
				$help['class'] = 'dashicons dashicons-smiley';
		}
	}
}

{
	try {
		$responsive_classes = ATTR_Cache::get(
			$cache_key = 'attr:backend-option-view:responsive-classes'
		);
	} catch (ATTR_Cache_Not_Found_Exception $e) {
		ATTR_Cache::set(
			$cache_key,
			$responsive_classes = apply_filters('attr:backend-option-view:design-default:responsive-classes', array(
				'label' => 'attr-col-xs-12 attr-col-sm-3 attr-col-lg-2',
				'input' => 'attr-col-xs-12 attr-col-sm-9 attr-col-lg-10',
			))
		);
	}

	$classes = array(
		'option' => array(
			'attr-backend-option',
			'attr-backend-option-design-default',
			'attr-backend-option-type-'. $option['type'],
			'attr-row',
			'attr-clearfix',
		),
		'label' => array(
			'attr-backend-option-label',
			'responsive' => $responsive_classes['label'],
		),
		'input' => array(
			'attr-backend-option-input',
			'attr-backend-option-input-type-'. $option['type'],
			'attr-clearfix',
			'responsive' => $responsive_classes['input'],
		),
		'desc' => array(
			'attr-backend-option-desc',
			'responsive' => 'attr-col-xs-12 attr-col-sm-offset-3 attr-col-sm-9 attr-col-lg-offset-2 attr-col-lg-10',
		),
	);

	/** Additional classes for option div */
	{
		if ($help) {
			$classes['option'][] = 'with-help';
		}

		if ($option['label'] === false) {
			$classes['label']['hidden'] = 'attr-hidden';
			unset($classes['label']['responsive']);

			$classes['input']['responsive'] = 'attr-col-xs-12';
			$classes['desc']['responsive']  = 'attr-col-xs-12';
		}

		$hide_bottom_border = attr_akg( 'hide-bottom-border', $option, false );
		if( $hide_bottom_border ) {
			$classes['option'][] = 'attr-bottom-border-hidden';
		}
	}

	/** Additional classes for input div */
	{
		$width_type = attr()->backend->option_type($option['type'])->_get_backend_width_type();

		if (!in_array($width_type, array('auto', 'fixed', 'full'))) {
			$width_type = 'auto';
		}

		$classes['input']['width-type'] = 'width-type-'. $width_type;
	}

	foreach ($classes as $key => $_classes) {
		$classes[$key] = implode(' ', $_classes);
	}
	unset($key, $_classes);
}

try {
	$desc_under_label = ATTR_Cache::get(
		$cache_key = 'attr:backend-option-view:desc-under-label'
	);
} catch (ATTR_Cache_Not_Found_Exception $e) {
	ATTR_Cache::set(
		$cache_key,
	
		$desc_under_label = apply_filters('attr:backend-option-view:design-default:desc-under-label', false)
	);
}

?>
<div class="<?php echo esc_attr($classes['option']) ?>" id="attr-backend-option-<?php echo esc_attr($data['id_prefix'] . $id) ?>">
	<?php if ($option['label'] !== false): ?>
		<div class="<?php echo esc_attr($classes['label']) ?>">
			<div class="attr-inner attr-clearfix">
				<label for="<?php echo esc_attr($data['id_prefix']) . esc_attr($id) ?>"><?php echo attr_htmlspecialchars($option['label']) ?></label>
				<?php if ($help): ?><div class="attr-option-help attr-option-help-in-label attr-visible-xs-block <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
				<?php if ($option['desc'] && $desc_under_label): ?><div class="attr-clear"></div><p><em class="attr-text-muted"><?php echo ($option['desc'] ? $option['desc'] : '') ?></em></p><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr($classes['input']) ?>">
		<div class="attr-inner attr-pull-<?php echo is_rtl() ? 'right' : 'left'; ?> attr-clearfix">
			<?php if ($help): ?><div class="attr-option-help attr-option-help-in-input attr-pull-right attr-hidden-xs <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
			<div class="attr-inner-option attr-clearfix">
				<?php echo attr()->backend->option_type($option['type'])->render($id, $option, $data) ?>
			</div>
		</div>
	</div>
	<?php if ($option['desc'] && !$desc_under_label): ?>
		<div class="<?php echo esc_attr($classes['desc']) ?>">
			<div class="attr-inner"><?php echo ($option['desc'] ? $option['desc'] : '') ?></div>
		</div>
	<?php endif; ?>
</div>
