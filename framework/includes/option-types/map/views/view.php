<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

$location_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['location'] ) ) ? $data['value']['location'] : '',
	'attr'  => array(
		'placeholder'   => __( 'Specify location', 'attr' ),
		'class'         => 'attr-option-map-inputs map-location',
	)
);
$location_data = array(
	'value'         => ( isset( $data['value']['location'] ) ) ? $data['value']['location'] : '',
	'id_prefix'     => $data['id_prefix'] . $id . '-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$location_html = attr()->backend->option_type('text')->render('location', $location_option, $location_data);

$venue_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['venue'] ) ) ? $data['value']['venue'] : '',
	'attr'  => array(
		'placeholder'   => __( 'Location Venue', 'attr' ),
		'class'   => 'attr-option-map-inputs map-venue',
	)
);
$venue_data = array(
	'value'         => ( isset( $data['value']['venue'] ) ) ? $data['value']['venue'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$venue_html = attr()->backend->option_type('text')->render('venue', $venue_option, $venue_data);

$address_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['address'] ) ) ? $data['value']['address'] : '',
	'attr'  => array(
		'placeholder'   => __( 'Address', 'attr' ),
		'class'   => 'attr-option-map-inputs map-address',
	)
);
$address_data = array(
	'value'         => ( isset( $data['value']['address'] ) ) ? $data['value']['address'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$address_html = attr()->backend->option_type('text')->render('address', $address_option, $address_data);

$city_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['city'] ) ) ? $data['value']['city'] : '',
	'attr'  => array(
		'placeholder'   => __( 'City', 'attr' ),
		'class'   => 'attr-option-map-inputs map-city',
	)
);
$city_data = array(
	'value'         => ( isset( $data['value']['city'] ) ) ? $data['value']['city'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$city_html = attr()->backend->option_type('text')->render('city', $city_option, $city_data);

$country_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['country'] ) ) ? $data['value']['country'] : '',
	'attr'  => array(
		'placeholder'   => __( 'Country', 'attr' ),
		'class'   => 'attr-option-map-inputs map-country',
	)
);
$country_data = array(
	'value'         => ( isset( $data['value']['country'] ) ) ? $data['value']['country'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$country_html = attr()->backend->option_type('text')->render('country', $country_option, $country_data);

$state_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['state'] ) ) ? $data['value']['state'] : '',
	'attr'  => array(
		'placeholder'   => __( 'State', 'attr' ),
		'class'   => 'attr-option-map-inputs map-state',
	)
);
$state_data = array(
	'value'         => ( isset( $data['value']['state'] ) ) ? $data['value']['state'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);

$state_html = attr()->backend->option_type('text')->render('state', $state_option, $state_data);

$zip_option = array(
	'type'  => 'text',
	'value' => ( isset( $data['value']['zip'] ) ) ? $data['value']['zip'] : '',
	'attr'  => array(
		'placeholder'   => __( 'Zip Code', 'attr' ),
		'class'   => 'attr-option-map-inputs map-zip',
	)
);
$zip_data = array(
	'value'         => ( isset( $data['value']['zip'] ) ) ? $data['value']['zip'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$zip_html = attr()->backend->option_type('text')->render('zip', $zip_option, $zip_data);

$coordinates_option = array(
	'type'  => 'hidden',
	'value' => ( isset( $data['value']['coordinates'] ) ) ? $data['value']['coordinates'] : '',
	'attr'  => array(
		'class'   => 'attr-option-map-inputs map-coordinates',
	)
);
$coordinates_data = array(
	'value'         => ( isset( $data['value']['coordinates'] ) ) ? $data['value']['coordinates'] : '',
	'id_prefix'     => $data['id_prefix'] . $id .'-',
	'name_prefix'   => $data['name_prefix'] .'['. $id .']',
);
$coordinates_html = attr()->backend->option_type('hidden')->render('coordinates', $coordinates_option, $coordinates_data);
?>
<?php
$div_attr = $option['attr'];
unset(
	$div_attr['name'],
	$div_attr['value']
);
?>
<div <?php echo attr_attr_to_html($div_attr) ?>>
	<div class="attr-option-maps-tab first">
		<?php echo $location_html ?>
		<a href="#" class="attr-option-maps-toggle attr-option-maps-expand"><?php _e('Cannot find the location?', 'attr') ?></a>
	</div>
	<div class="attr-option-maps-tab second attr-row">
		<div class="attr-col-sm-6">
			<div class="inner">
				<?php echo $venue_html ?>
				<?php echo $address_html ?>
				<?php echo $city_html ?>
				<?php echo $state_html ?>
				<?php echo $country_html ?>
				<?php echo $zip_html ?>
				<?php echo $coordinates_html ?>
				<a href="#" class="attr-option-maps-toggle attr-option-maps-close"><?php _e('Reset location', 'attr') ?></a>
			</div>
		</div>
		<div class="attr-col-sm-6 map-googlemap">
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>