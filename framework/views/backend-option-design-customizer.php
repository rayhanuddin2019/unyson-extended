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
	$classes = array(
		'option' => array(
			'attr-backend-option',
			'attr-backend-option-design-customizer',
			'attr-backend-option-type-'. $option['type'],
			'attr-row',
			'attr-clearfix',
		),
		'label' => array(
			'attr-backend-option-label',
			'responsive' => 'attr-col-xs-12',
		),
		'input' => array(
			'attr-backend-option-input',
			'attr-backend-option-input-type-'. $option['type'],
			'attr-clearfix',
			'responsive' => 'attr-col-xs-12',
		),
		'desc' => array(
			'attr-backend-option-desc',
			'responsive' => 'attr-col-xs-12',
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

?>
<div class="<?php echo esc_attr($classes['option']) ?>" id="attr-backend-option-<?php echo esc_attr($data['id_prefix'] . $id) ?>">
	<?php if ($option['label'] !== false): ?>
		<div class="<?php echo esc_attr($classes['label']) ?>">
			<div class="attr-inner attr-clearfix">
				<label for="<?php echo esc_attr($data['id_prefix']) . esc_attr($id) ?>"><span class="customize-control-title"><?php echo attr_htmlspecialchars($option['label']) ?></span></label>
				<?php if ($help): ?><div class="attr-option-help attr-option-help-in-label <?php echo esc_attr($help['class']) ?>" title="<?php echo esc_attr($help['html']) ?>"></div><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($option['desc']): ?>
		<div class="<?php echo esc_attr($classes['desc']) ?>">
			<div class="attr-inner"><span class="description customize-control-description"><?php echo ($option['desc'] ? $option['desc'] : '') ?></span></div>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr($classes['input']) ?>">
		<div class="attr-inner attr-pull-<?php echo is_rtl() ? 'right' : 'left'; ?> attr-clearfix">
			<div class="attr-inner-option">
			    
				<?php   
				// get all input fields
				echo attr()->backend->option_type($option['type'])->render($id, $option, $data);
				 ?>
			</div>
		</div>
	</div>
</div>
