<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var string $type
 * @var string $html
 */

{
	$classes = array(
		'option' => array(
			'form-field',
			'attr-backend-container',
			'attr-backend-container-type-'. $type
		),
		'content' => array(
			'attr-backend-container-content',
		),
	);

	foreach ($classes as $key => $_classes) {
		$classes[$key] = implode(' ', $_classes);
	}
	unset($key, $_classes);
}

?>
<tr class="<?php echo esc_attr($classes['option']) ?>">
	<td colspan="2" class="<?php echo esc_attr($classes['content']) ?>"><?php echo $html ?></td>
</tr>