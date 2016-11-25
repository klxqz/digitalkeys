<?php

return array(
    'name' => 'Цифровые ключи',
    'description' => 'Продажа цифровых ключей и пин-кодов',
    'vendor' => '985310',
    'version' => '1.1.0',
    'img' => 'img/digitalkeys.png',
    'frontend' => true,
    'shop_settings' => true,
    'handlers' => array(
        'product_save' => 'productSave',
        'backend_product' => 'backendProduct',
        'order_action.pay' => 'orderActionPay',
    ),
);
