<?php

class shopDigitalkeysPlugin extends shopPlugin {

    public function backendProduct($product) {
        if ($this->getSettings('status') || 1) {
            $view = wa()->getView();
            $view->assign('product', $product);
            $html = $view->fetch('plugins/digitalkeys/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function orderActionCreate($params) {
        if ($this->getSettings('status')) {
            $session = wa()->getStorage();
            $session->remove('digitalkeysplugin');
        }
    }



}
