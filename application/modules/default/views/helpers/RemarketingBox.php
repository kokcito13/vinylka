<?php

class Zend_View_Helper_RemarketingBox
{
    public function RemarketingBox($product_id)
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->product = null;
        $remarketing = Application_Model_Kernel_Remarketing::getByIp($_SERVER['REMOTE_ADDR']);

        if ($product_id != 0) {
            if (!empty($remarketing)) {
                $product = $remarketing->getProduct();
                $view->product = $product;
            } else {
                $this->setNewProduct($product_id);
                $view->product = null;
            }
        } else {
            if (!is_null($remarketing)) {
                $view->product = $remarketing->getProduct();
            }
        }

        return $view->render('block/remarketing_box.phtml');
    }
}