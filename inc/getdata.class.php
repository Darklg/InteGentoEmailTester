<?php
class inteGentoEmailTester {

    function getDefaultData() {
        return array(
            'salable' => 'yes',
            'addAllLink' => Mage::getUrl('*/shared/allcart', array(
                'code' => 'foo'
            )) ,
            'viewOnSiteLink' => Mage::getUrl('*/shared/index', array(
                'code' => 'foo'
            )) ,
            'customerName' => 'Jean-Michel Lorem',
            'customerEmail' => 'jeanmichel@magento.com',
            'name' => 'Jean-Michel Lorem',
            'product_url' => 'https://github.com/Darklg',
            'product_name' => 'Barre de faire',
            'product_image' => 'http://placehold.it/75x75',
            'message' => 'The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach. Everyone who has ever taken a shower has had an idea. It’s the person who gets out of the shower, dries off, and does something about it that makes a difference — Nolan Bushnell. ',
        );
    }

    function getShipment() {
        $shipment = new Varien_Object();
        $shipment->setData(array(
            'increment_id' => '100000022'
        ));
        return $shipment;
    }

    function getCreditMemo() {
        $creditmemo = new Varien_Object();
        $creditmemo->setData(array(
            'increment_id' => '100000022'
        ));

        $h = Mage::getResourceModel('sales/order_creditmemo_collection');
        $collection = $h->setPageSize(1)->setCurPage(1);
        foreach ($collection as $item) {
            $creditmemo = $item;
            break;
        }
        return $creditmemo;
    }

    function getData() {
        $data = new Varien_Object();
        $data->setData(array(
            'name' => 'Jean-Michel Lorem',
            'email' => 'foo@bar.com',
            'telephone' => '123-4567890',
            'comment' => 'This is a test'
        ));
        return $data;
    }

    function getSubscriber() {
        $collection = Mage::getModel('newsletter/subscriber')->getCollection()->setPageSize(1)->setOrder('subscriber_id', 'desc');
        foreach ($collection as $subscriber) {
            return $subscriber;
            break;
        }
        return false;
    }

    function getAlertGrid($customer, $nbProducts = 2) {
        $_stockProducts = array();
        $_blockName = 'productalert/email_stock';
        if ($tpl == 'catalog_productalert_email_price_template') {
            $_blockName = 'productalert/email_price';
        }
        $_stockBlock = Mage::helper('productalert')->createBlock($_blockName);
        $products = Mage::getModel('catalog/product')->getCollection()->setPageSize($nbProducts);
        foreach ($products as $prod) {
            $product = Mage::getModel('catalog/product')->load($prod->getId());
            $_stockProducts[$product->getId() ] = $product;
            if (count($_stockProducts) >= $nbProducts) {
                break;
            }
        }
        foreach ($_stockProducts as $product) {
            $product->setCustomerGroupId($customer->getGroupId());
            $_stockBlock->addProduct($product);
        }

        return $_stockBlock->toHtml();
    }

    function getWishlistItems() {

        // Get latest shared wishlist ID
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('wishlist/wishlist');
        $wishlist_id = $readConnection->fetchCol('SELECT wishlist_id FROM ' . $table . ' WHERE shared=1 ORDER BY wishlist_id DESC LIMIT 0,1');

        if (empty($wishlist_id)) {
            $wishlist_id = array(
                0
            );
        }

        // Register wishlist
        Mage::register('wishlist', Mage::getSingleton('wishlist/wishlist')->load($wishlist_id[0]));

        // Load wishlist vars
        return Mage::app()->getLayout()->createBlock('wishlist/share_email_items')->toHtml();
    }
}

$inteGentoEmailTester = new inteGentoEmailTester();
