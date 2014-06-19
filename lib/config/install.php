<?php
$plugin_id = array('shop', 'stock');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'page_title', 'Акции');
$app_settings_model->set($plugin_id, 'default_output', '1');
$app_settings_model->set($plugin_id, 'count', '5');
$app_settings_model->set($plugin_id, 'frontend_product', '1');
$app_settings_model->set($plugin_id, 'frontend_product_output', 'block');
$app_settings_model->set($plugin_id, 'frontend_cart', '1');
