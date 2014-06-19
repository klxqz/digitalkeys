<?php

class shopDigitalkeysPluginSettingsAction extends waViewAction {

    protected $templates = array(
        'FrontendNav' => array('name' => 'Шаблон краткого списка', 'tpl_path' => 'plugins/stock/templates/FrontendNav.html'),
        'FrontendProduct' => array('name' => 'Шаблон в карточке товара', 'tpl_path' => 'plugins/stock/templates/FrontendProduct.html'),
        'FrontendCart' => array('name' => 'Шаблон в корзине', 'tpl_path' => 'plugins/stock/templates/FrontendCart.html'),
        'StockInfo' => array('name' => 'Шаблон "Информация об акции"', 'tpl_path' => 'plugins/stock/templates/StockInfo.html'),
        
    );

    public function execute() {
        //$plugin = wa()->getPlugin('stock');
        /*
        $settings = $plugin->getSettings();


        foreach ($this->templates as &$template) {
            $template['full_path'] = wa()->getDataPath($template['tpl_path'], false, 'shop', true);
            if (file_exists($template['full_path'])) {
                $template['change_tpl'] = true;
            } else {
                $template['full_path'] = wa()->getAppPath($template['tpl_path'], 'shop');
                $template['change_tpl'] = false;
            }
            $template['template'] = file_get_contents($template['full_path']);
        }

        $this->view->assign('settings', $settings);
        $this->view->assign('templates', $this->templates);*/
        //waSystem::popActivePlugin();
    }

}
