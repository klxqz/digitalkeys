<?php

return array(
    'shop_digital_keys' => array(
        'sku_id' => array('int', 11, 'null' => 0),
        'key' => array('varchar', 255),
        ':keys' => array(
            'sku_id' => 'sku_id',
            'full_key' => array('sku_id', 'key', 'unique' => 1),
        ),
    ),
);
