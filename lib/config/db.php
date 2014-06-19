<?php
return array(
    'shop_digital_keys' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'sku_id' => array('int', 11, 'null' => 0),
        'key' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('id'),
            'sku_id' => 'sku_id'
        ),
    ),
);
