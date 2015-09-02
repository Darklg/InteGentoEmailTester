<?php

/**
 * Email tester v 0.17.1
 *
 * @author      Darklg <darklg.blog@gmail.com>
 * @copyright   Copyright (c) 2015 Darklg
 * @license     MIT
 */

$testerVersion = '0_17_1';
$cachePrefixKey = 'integento__emailtester__' . $testerVersion . '__';

/* ----------------------------------------------------------
  Load Magento
---------------------------------------------------------- */

@session_start();

/* Search magento file
 -------------------------- */

$_mageAppName = 'app/Mage.php';
$_fileExists = false;
for ($i = 0;$i < 5;$i++) {
    $_testFileName = str_repeat("../", $i) . $_mageAppName;
    if (file_exists($_testFileName)) {
        $_fileExists = true;
        require_once $_testFileName;
    }
}

if (!$_fileExists) {
    echo '<p>Error : Magento could not be found.</p>';
    die;
}

/* Init Magento
 -------------------------- */

Mage::app();

/* Set system
 -------------------------- */

require_once ('inc/getdata.class.php');

/* Get values
 -------------------------- */

$templates = $inteGentoEmailTester->getTemplates();
$_stores = $inteGentoEmailTester->getStores();

/* ----------------------------------------------------------
  Default page
---------------------------------------------------------- */

$tpl = $_GET['template'];
if (!isset($_GET['template']) || !array_key_exists($_GET['template'], $templates)) {
    include 'inc/default.php';
    die;
}

/* ----------------------------------------------------------
  Templates vars
---------------------------------------------------------- */

if (isset($_GET['store']) && array_key_exists($_GET['store'], $_stores)) {
    $_store = $_GET['store'];
}

$_SESSION['integento__emailtester__store'] = $_store;

$_locale = Mage::getStoreConfig('general/locale/code', $_store);
Mage::getSingleton('core/translate')->setLocale($_locale)->init('frontend', true);
$datas = $inteGentoEmailTester->getDefaultData($_store);

/* New order template
 -------------------------- */

if (isset($templates[$tpl]['order'])) {

    $cacheId = $cachePrefixKey . 'sales_email_order_template';
    if (false !== ($data = Mage::app()->getCache()->load($cacheId))) {
        $_datas = unserialize($data);
        $datas['order'] = $_datas['order'];
        $datas['billing'] = $_datas['billing'];
        $datas['billingAddress'] = $_datas['billingAddress'];
        $datas['shippingAddress'] = $_datas['shippingAddress'];
        $datas['payment_html'] = $_datas['payment_html'];
    }
    else {

        /* Load latest order */
        $orders = Mage::getModel('sales/order')->getCollection()->setOrder('created_at', 'DESC')->setPageSize(1)->setCurPage(1);
        $order = Mage::getModel('sales/order')->load($orders->getFirstItem()->getEntityId());
        $storeId = $order->getStore()->getId();
        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment());
        $paymentBlock->getMethod()->setStore($storeId);

        $datas['order'] = $order;
        $datas['billing'] = $order->getBillingAddress();
        $datas['billingAddress'] = $order->getBillingAddress();
        $datas['shippingAddress'] = $order->getShippingAddress();
        $datas['payment_html'] = $paymentBlock->toHtml();
        Mage::app()->getCache()->save(serialize($datas) , $cacheId);
    }
}

/* Shipment
 -------------------------- */

if (isset($templates[$tpl]['shipment'])) {
    $datas['shipment'] = $inteGentoEmailTester->getInvoice();
}

/* Invoice
 -------------------------- */
if (isset($templates[$tpl]['invoice'])) {
    $datas['invoice'] = $inteGentoEmailTester->getInvoice();
}

/* Credit memo
 -------------------------- */

if (isset($templates[$tpl]['creditmemo'])) {
    $datas['creditmemo'] = $inteGentoEmailTester->getCreditMemo();
}

/* Payment failed
 -------------------------- */

if ($tpl == 'checkout_payment_failed_template' && is_object($datas['order'])) {
    $datas['reason'] = 'oops';
    $datas['checkoutType'] = 'onepage';
    $datas['dateAndTime'] = Mage::app()->getLocale()->date();
    $datas['customer'] = $datas['customerName'];
    $datas['total'] = '€ 100';
    $datas['shippingMethod'] = 'ups_worldwide_example';
    $datas['paymentMethod'] = 'visa_worldwide_example';
    $datas['items'] = 'Apple Watch x 5  €10000<br />Apple Watch x 10  €20000';
}

/* Contact template
 -------------------------- */

if ($tpl == 'contacts_email_email_template') {
    $datas['data'] = $inteGentoEmailTester->getData();
}

/* New account & Forgot password
 -------------------------- */

if (isset($templates[$tpl]['customer'])) {

    $cacheId = $cachePrefixKey . 'customer_data';
    if (false !== ($data = Mage::app()->getCache()->load($cacheId))) {
        $_datas = unserialize($data);
        $datas['customer'] = $_datas['customer'];
        $datas['customerName'] = $_datas['customer']->getName();
    }
    else {
        $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSort('entity_id', 'desc')->setPageSize(1);
        $datas['customer'] = $collection->getFirstItem();
        $datas['customer']->setData('name', '****');
        $datas['customer']->setData('password', '****');
        $datas['customer']->setData('rp_token', md5('coucou'));
        Mage::app()->getCache()->save(serialize($datas) , $cacheId);
    }
}

/* Stock & price alert
 -------------------------- */

if (isset($templates[$tpl]['alertGrid'])) {
    $datas['alertGrid'] = $inteGentoEmailTester->getAlertGrid($datas['customer']);
}

/* Subscription confirmation
 -------------------------- */

if ($tpl == 'newsletter_subscription_confirm_email_template') {
    $datas['subscriber'] = $inteGentoEmailTester->getSubscriber();
}

/* Wishlist
 -------------------------- */

if ($tpl == 'wishlist_email_email_template') {
    $datas['items'] = $inteGentoEmailTester->getWishlistItems();
    $datas['message'] = 'Please buy this';
}

/* AW Help Desk 3
-------------------------- */

if (isset($templates[$tpl]['aw_hdu3'])) {
    $datas['is_agent_changed'] = true;
    $datas['is_department_changed'] = true;
    $datas['is_status_changed'] = true;
    $datas['agent_name'] = 'Jean-Michel Support';
    $datas['department_name'] = 'Main Department';
    $datas['ticket_status'] = 'Waiting for reply';
    $datas['ticket_uid'] = 'OBO-46271';
    $datas['ticket_subject'] = 'The world needs dreamers and the world needs doers.';
}

/* ----------------------------------------------------------
  Use template
---------------------------------------------------------- */

$inteGentoEmailTester->setMailTemplateAndUseDatas($tpl, $_store, $datas);

