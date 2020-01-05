<?php if ( ! defined( 'ATTR' ) ) {
	die( 'Forbidden' );
}
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var  $value
 */

{
	$wrapper_attr = $option['attr'];

	unset(
		$wrapper_attr['value'],
		$wrapper_attr['name']
	);
}

{
	$input_attr['value'] = $value;
	$input_attr['name']  = $option['attr']['name'];
}

?>
<div <?php echo attr_attr_to_html( $wrapper_attr ); ?>>
	<input class="attr-irs-range-slider" type="text" <?php echo attr_attr_to_html( $input_attr ); ?>/>
</div>
