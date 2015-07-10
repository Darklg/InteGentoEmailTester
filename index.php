<?php

/**
 * Email tester v 0.9
 *
 * @author      Darklg <darklg.blog@gmail.com>
 * @copyright   Copyright (c) 2015 Darklg
 * @license     MIT
 */

$testerVersion = '0_9';
$cachePrefixKey = 'integento__emailtester__' . $testerVersion . '__';

$templates = array(
    'catalog_productalert_email_price_template' => array(
        'alertGrid' => 1,
        'customer' => 1,
    ) ,
    'catalog_productalert_email_stock_template' => array(
        'alertGrid' => 1,
        'customer' => 1,
    ) ,
    'contacts_email_email_template' => 'contacts_email_email_template',
    'customer_create_account_email_template' => array(
        'customer' => 1
    ) ,
    'customer_password_forgot_email_template' => array(
        'customer' => 1
    ) ,
    'newsletter_subscription_confirm_email_template' => 'newsletter_subscription_confirm_email_template',
    'newsletter_subscription_success_email_template' => 'newsletter_subscription_success_email_template',
    'newsletter_subscription_un_email_template' => 'newsletter_subscription_un_email_template',
    'sales_email_creditmemo_template' => array(
        'order' => 1
    ) ,
    'sales_email_order_comment_template' => array(
        'order' => 1
    ) ,
    'sales_email_order_template' => array(
        'order' => 1
    ) ,
    'sales_email_shipment_template' => array(
        'order' => 1
    ) ,
    'sendfriend_email_template' => 'sendfriend_email_template',
    'wishlist_email_email_template' => array(
        'customer' => 1
    ) ,
);

/* ----------------------------------------------------------
  Load Magento
---------------------------------------------------------- */

/* Search magento file
 -------------------------- */

require_once ('inc/getdata.class.php');

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

/* Load stores
 -------------------------- */

$_stores = array();
$websites = Mage::app()->getWebsites();
foreach ($websites as $website) {
    $groups = $website->getGroups();
    foreach ($groups as $group) {
        $stores = $group->getStores();
        foreach ($stores as $store) {
            $_stores[$store->getId() ] = $store;
        }
    }
}

/* ----------------------------------------------------------
  Default page
---------------------------------------------------------- */

$tpl = $_GET['template'];
if (!isset($_GET['template']) || !array_key_exists($_GET['template'], $templates)) {

    echo '<!DOCTYPE HTML><html lang="en_EN"><head>';
    echo '<meta charset="UTF-8" /><title>Mail preview</title>';
    echo '</head><body><h1>Mail preview</h1>';

    if(isset($_GET['success'])){
        echo '<p style="color:green">Mail has been successfully sent !</p>';
    }

    echo '<form action="" method="get">';
    echo '<p><label for="template">Template :</label><br />';
    echo '<select id="template" name="template">';
    foreach ($templates as $tpl_id => $template) {
        echo '<option value="' . $tpl_id . '"">' . $tpl_id . '</a></li>';
    }
    echo '</select></p>';
    echo '<p><label for="store">Store :</label><br />';
    echo '<select id="store" name="store">';
    foreach ($_stores as $storeId => $store) {
        echo '<option value="' . $storeId . '"">' . $store->getName() . '</a></li>';
    }
    echo '</select></p>';
    echo '<p id="box-email"><label for="email">Email</label><br />';
    echo '<input type="email" id="email" name="email" value="" /></p>';
    echo '<button type="submit" name="submit">Preview</button>';
    echo '<button type="submit" name="send" autocomplete="email">Send by email</button>';
    echo '</form>';
    echo '</body></html>';
    die;
}

/* ----------------------------------------------------------
  Templates vars
---------------------------------------------------------- */

if (isset($_GET['store']) && array_key_exists($_GET['store'], $_stores)) {
    $_store = $_GET['store'];
}

$_locale = Mage::getStoreConfig('general/locale/code', $_store);
Mage::getSingleton('core/translate')->setLocale($_locale)->init('frontend', true);

$datas = $inteGentoEmailTester->getDefaultData();

$datas['store'] = $_stores[$_store];

/* New order template
 -------------------------- */

if (isset($templates[$tpl]['order'])) {

    $cacheId = $cachePrefixKey . 'sales_email_order_template';
    if (false !== ($data = Mage::app()->getCache()->load($cacheId))) {
        $_datas = unserialize($data);
        $datas['order'] = $_datas['order'];
        $datas['billing'] = $_datas['billing'];
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
        $datas['payment_html'] = $paymentBlock->toHtml();
        Mage::app()->getCache()->save(serialize($datas) , $cacheId);
    }
}

/* Shipment
 -------------------------- */

if ($tpl == 'sales_email_shipment_template' && is_object($datas['order'])) {
    $datas['shipment'] = $inteGentoEmailTester->getShipment();
}

/* Credit memo
 -------------------------- */

if ($tpl == 'sales_email_creditmemo_template' && is_object($datas['order'])) {
    $datas['creditmemo'] = $inteGentoEmailTester->getCreditMemo();
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
        $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->addAttributeToSort('entity_id', 'desc')->setPageSize(1);
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

/* ----------------------------------------------------------
  Display template
---------------------------------------------------------- */

if (isset($_GET['email'], $_GET['send']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) !== false) {
    $email_template = Mage::getModel('core/email_template')->loadDefault($tpl);
    $email_template->setSenderName(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME));
    $email_template->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
    $email_template->send($_GET['email'], 'Email Tester', $datas);
    header("Location: index.php?success=1");
    return;
}
else {
    header('Content-Type: text/html; charset=utf-8');
    echo Mage::getModel('core/email_template')->load(3)->loadDefault($tpl)->getProcessedTemplate($datas);
}

