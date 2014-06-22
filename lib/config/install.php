<?php
$plugin_id = array('shop', 'digitalkeys');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'stock', '1');
