<?php

//Override billing fields in my account page
add_filter("woocommerce_billing_fields" , "custom_override_billing_fields");
add_filter("woocommerce_default_address_fields", "order_address_fields");

function custom_override_billing_fields( $fields ) {
	
	//Only on Edit Address pages
	if (is_wc_endpoint_url( 'edit-address' )) {
	
		$fields['billing_eu_vat_number'] = array(
			'label'     => __('BTW nummer', 'custom-account-fields'),
			'placeholder'   => 'BE',
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true
		);
	}

	return $fields;
}

function order_address_fields($fields) {

    $order = array(
        "first_name",
        "last_name",
        "eu_vat_number",
        "company",
        "country",
        "address_1",
        "address_2",
        "postcode",
        "city"
    );
    foreach($order as $field) {
        $ordered_fields[$field] = (array_key_exists($field, $fields) ? $fields[$field] : '');
    }
    $fields = $ordered_fields;
    
    return $fields;
}

?>