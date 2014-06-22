<?php

$model = new waModel();
try {
    $model->exec("DROP TABLE `shop_digital_keys`");
} catch (waDbException $e) {
    
}


