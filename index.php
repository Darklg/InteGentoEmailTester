<?php

/**
 * Email tester v 0.14
 *
 * @author      Darklg <darklg.blog@gmail.com>
 * @copyright   Copyright (c) 2015 Darklg
 * @license     MIT
 */

$testerVersion = '0_14';
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
    'checkout_payment_failed_template' => array(
        'order' => 1,
    ) ,
    'contacts_email_email_template' => array() ,
    'customer_create_account_email_template' => array(
        'customer' => 1,
    ) ,
    'customer_password_forgot_email_template' => array(
        'customer' => 1,
    ) ,
    'newsletter_subscription_confirm_email_template' => array() ,
    'newsletter_subscription_success_email_template' => array() ,
    'newsletter_subscription_un_email_template' => array() ,
    'sales_email_creditmemo_comment_template' => array(
        'order' => 1,
        'creditmemo' => 1,
    ) ,
    'sales_email_creditmemo_template' => array(
        'order' => 1,
        'creditmemo' => 1,
    ) ,
    'sales_email_order_comment_template' => array(
        'order' => 1,
    ) ,
    'sales_email_order_template' => array(
        'order' => 1,
    ) ,
    'sales_email_shipment_comment_template' => array(
        'order' => 1,
        'shipment' => 1,
    ) ,
    'sales_email_shipment_template' => array(
        'order' => 1,
        'shipment' => 1,
    ) ,
    'sales_email_invoice_comment_template' => array(
        'order' => 1,
        'invoice' => 1,
    ) ,
    'sales_email_invoice_template' => array(
        'order' => 1,
        'invoice' => 1,
    ) ,
    'wishlist_email_email_template' => array(
        'customer' => 1,
    ) ,
);

/* ----------------------------------------------------------
  Load Magento
---------------------------------------------------------- */

@session_start();

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

$mailModel = Mage::getModel('core/email_template');

/* Set template names
 -------------------------- */

foreach ($mailModel->getDefaultTemplatesAsOptionsArray() as $_option):
    if (isset($templates[$_option['value']])) {
        $templates[$_option['value']]['name'] = $_option['label'];
    }
endforeach;

$mid = array();
foreach ($templates as $key => $row) {
    $mid[$key] = $row['name'];
}

array_multisort($mid, SORT_ASC, $templates);

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
        $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToxfxf('*')->addAttributeToSort('entity_id', 'desc')->setPageSize(1);
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

$mailTemplate = $mailModel->load(3)->loadDefault($tpl);
$mailTemplate->setDesignConfig(array(
    'area' => 'frontend',
    'store' => $_store
));

if (isset($_GET['email'], $_GET['send']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) !== false) {
    $mailTemplate->setSenderName(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME));
    $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
    $mailTemplate->send($_GET['email'], 'Email Tester', $datas);
    header("Location: index.php?success=1");
    return;
}
else if (isset($_GET['save_admin_tpl'])) {
    $_core = Mage::getSingleton('core/resource');
    $_write = $_core->getConnection('core_write');
    $tpl = $mailTemplate->getData();
    $tpl['template_code'] = '[' . $datas['store']->getName() . '] ';
    if ($mailTemplate->getData('template_code')) {
        $tpl['template_code'].= $mailTemplate->getData('template_code');
    }
    else {
        $tpl['template_code'].= $mailTemplate->getData('template_subject');
    }

    $tpl['template_text'] = $mailTemplate->getData('template_text');
    $tpl['template_type'] = $mailTemplate->getData('template_type');
    $tpl['template_subject'] = $mailTemplate->getData('template_subject');

    try {
        $_write->insert($_core->getTableName('core_email_template') , $tpl);
    }
    catch(Exception $e) {
        echo '<pre>';
        var_dump($e->getMessage());
        echo '</pre>';
        die;
    }
    header("Location: index.php?success=2");
    return;
}
else {
    header('Content-Type: text/html; charset=utf-8');
    echo $mailTemplate->getProcessedTemplate($datas);
}

