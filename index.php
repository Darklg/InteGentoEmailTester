<?php

/**
 * Email tester v 0.11
 *
 * @author      Darklg <darklg.blog@gmail.com>
 * @copyright   Copyright (c) 2015 Darklg
 * @license     MIT
 */

$testerVersion = '0_11';
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
    'sales_email_creditmemo_template' => array(
        'order' => 1,
    ) ,
    'sales_email_order_comment_template' => array(
        'order' => 1,
    ) ,
    'sales_email_order_template' => array(
        'order' => 1,
    ) ,
    'sales_email_shipment_template' => array(
        'order' => 1,
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

/* Set template names
 -------------------------- */

foreach (Mage_Core_Model_Email_Template::getDefaultTemplatesAsOptionsArray() as $_option):
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

    echo '<!DOCTYPE HTML><html lang="en_EN"><head>';
    echo '<meta charset="UTF-8" /><title>Mail preview</title>';
    echo '</head><body><h1>Mail preview</h1>';

    if (isset($_GET['success'])) {
        echo '<p style="color:green">Mail has been successfully sent !</p>';
    }

    echo '<form action="" method="get">';
    echo '<p><label for="template">Template :</label><br />';
    echo '<select id="template" name="template">';
    foreach ($templates as $tpl_id => $template) {
        $tplName = $tpl_id;
        if (isset($template['name'])) {
            $tplName = $template['name'];
        }
        echo '<option value="' . $tpl_id . '"">' . $tplName . '</a></li>';
    }
    echo '</select></p>';
    echo '<p><label for="store">Store :</label><br />';
    echo '<select id="store" name="store">';
    $i = 0;
    $_lastGroup = '';
    foreach ($_stores as $storeId => $store) {
        $_groupName = $store->getGroup()->getName();
        if ($_groupName != $_lastGroup) {
            if ($i > 0) {
                echo '</optgroup>';
            }
            echo '<optgroup label="' . $_groupName . '">';
            $_lastGroup = $_groupName;
        }
        $_isCurrent = isset($_SESSION['integento__emailtester__store']) && $_SESSION['integento__emailtester__store'] == $storeId;
        echo '<option ' . ($_isCurrent ? 'selected="selected"' : '') . ' value="' . $storeId . '"">' . $store->getName() . '</a></li>';
        $i++;
    }
    echo '</optgroup>';
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

if ($tpl == 'sales_email_shipment_template' && is_object($datas['order'])) {
    $datas['shipment'] = $inteGentoEmailTester->getShipment();
}

/* Credit memo
 -------------------------- */

if ($tpl == 'sales_email_creditmemo_template' && is_object($datas['order'])) {
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

$mailTemplate = Mage::getModel('core/email_template')->load(3)->loadDefault($tpl);
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
else {
    header('Content-Type: text/html; charset=utf-8');
    echo $mailTemplate->getProcessedTemplate($datas);
}

