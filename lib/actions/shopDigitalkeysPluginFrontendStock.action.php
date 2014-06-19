<?php

class shopStockPluginFrontendStockAction extends shopFrontendAction {

    public function execute() {
        $plugin = wa()->getPlugin('stock');
        $collection = new shopStockProductsCollection();
        if($collection->stockFilter()) {
            $this->setCollection($collection);
        }
        $page_title = $plugin->getSettings('page_title');
        wa()->getResponse()->setTitle($page_title);
        $this->view->assign('title', $page_title);
        $this->view->assign('frontend_search', wa()->event('frontend_search'));
        $this->setThemeTemplate('search.html');
        waSystem::popActivePlugin();
    }

}
