<?php

class shopDigitalkeysPluginSettingsAction extends waViewAction {

    protected $templates = array(
        'FrontendNav' => array('name' => 'Шаблон краткого списка', 'tpl_path' => 'plugins/stock/templates/FrontendNav.html'),
        'FrontendProduct' => array('name' => 'Шаблон в карточке товара', 'tpl_path' => 'plugins/stock/templates/FrontendProduct.html'),
        'FrontendCart' => array('name' => 'Шаблон в корзине', 'tpl_path' => 'plugins/stock/templates/FrontendCart.html'),
        'StockInfo' => array('name' => 'Шаблон "Информация об акции"', 'tpl_path' => 'plugins/stock/templates/StockInfo.html'),
    );
    protected $plugin_id = array('shop', 'digitalkeys');

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get($this->plugin_id);

        if (isset($settings['product_types'])) {
            $settings['product_types'] = json_decode($settings['product_types'], true);
        }

        $type_model = new shopTypeModel();
        $product_types = $type_model->getAll($type_model->getTableId(), true);

        $this->view->assign('settings', $settings);
        $this->view->assign('product_types', $product_types);
    }

}
