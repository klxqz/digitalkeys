<?php

class shopDigitalkeysPluginSettingsAction extends waViewAction {

    protected $tpl_path = 'plugins/digitalkeys/templates/printform/SendDigitalKey.html';
    protected $plugin_id = array('shop', 'digitalkeys');

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get($this->plugin_id);

        if (isset($settings['product_types'])) {
            $settings['product_types'] = json_decode($settings['product_types'], true);
        }

        $type_model = new shopTypeModel();
        $product_types = $type_model->getAll($type_model->getTableId(), true);

        $change_tpl = false;
        $template_path = wa()->getDataPath($this->tpl_path, false, 'shop', true);
        if (file_exists($template_path)) {
            $change_tpl = true;
        } else {
            $template_path = wa()->getAppPath($this->tpl_path, 'shop');
        }
        $template = file_get_contents($template_path);


        $this->view->assign(
                array(
                    'settings' => $settings,
                    'product_types' => $product_types,
                    'template' => $template,
                    'change_tpl' => $change_tpl
                )
        );
    }

}
