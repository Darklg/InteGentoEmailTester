<?php
class inteGentoEmailTester {

    private $templates = array(
        'aw_hdu3_to_customer_new_ticket_email' => array(
            'aw_hdu3' => 1
        ) ,
        'aw_hdu3_to_customer_new_ticket_by_admin_email' => array(
            'aw_hdu3' => 1
        ) ,
        'aw_hdu3_to_customer_new_reply_email' => array(
            'aw_hdu3' => 1
        ) ,
        'aw_hdu3_to_customer_ticket_changed' => array(
            'aw_hdu3' => 1
        ) ,
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
        'sendfriend_email_template' => array() ,
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
      Init
    ---------------------------------------------------------- */

    function __construct() {
        @session_start();

        $this->mailModel = Mage::getModel('core/email_template');
        $this->storeName = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
        $this->storeEmail = Mage::getStoreConfig('trans_email/ident_general/email');

        $this->setTemplates($this->templates);

        $this->setMessages();
    }

    /* ----------------------------------------------------------
      Messages
    ---------------------------------------------------------- */

    function addMessage($type, $message) {
        $_SESSION['integento__emailtester__messages'][$type][] = $message;
    }

    function addMessageAndRedirect($type, $message) {
        $this->addMessage($type, $message);
        header("Location: index.php");
        die;
    }

    function displayMessages() {

        $_tplHtml = '';
        $_html = '';

        foreach ($_SESSION['integento__emailtester__messages'] as $type => $message) {
            if (!empty($message)) {
                $_tplHtml.= '<p class="message-' . $type . '">' . implode('<br />', $message) . '</p>';
            }
            $_SESSION['integento__emailtester__messages'][$type] = array();
        }

        if (!empty($_tplHtml)) {
            $_html.= '<div id="messages" onclick="this.innerHTML=\'\';return false;">' . $_tplHtml . '</div>';
        }

        $this->emptyMessages();

        return $_html;
    }

    function emptyMessages() {
        $_SESSION['integento__emailtester__messages'] = array(
            'error' => array() ,
            'success' => array()
        );
    }

    function setMessages() {
        if (!isset($_SESSION['integento__emailtester__messages'])) {
            $this->emptyMessages();
        }
    }

    /* ----------------------------------------------------------
      Set class
    ---------------------------------------------------------- */

    function setTemplates($templates) {

        $modules = Mage::getConfig()->getNode('modules')->children();
        $this->modulesArray = (array)$modules;

        foreach ($templates as $tpl_id => $tpl) {
            if (!isset($this->modulesArray['AW_Helpdesk3']) && isset($tpl['aw_hdu3'])) {
                unset($templates[$tpl_id]);
            }
        }

        $base_tpl = Mage::getConfig()->getNode('global/template/email')->asArray();
        $default_templates = Mage::getModel('core/email_template')->getDefaultTemplatesAsOptionsArray();
        foreach ($default_templates as $_option):
            $val = $_option['value'];
            if (empty($val)) {
                continue;
            }
            if (isset($templates[$val])) {
                $templates[$val]['name'] = $_option['label'];
                if (!isset($templates[$val]['templates'])) {
                    $templates[$val]['templates'] = array();
                }
                if (isset($base_tpl[$val]['file'])) {
                    $templates[$val]['templates'][] = $base_tpl[$val]['file'];
                }
            }
        endforeach;

        $mid = array();
        foreach ($templates as $key => $row) {
            $mid[$key] = $row['name'];
        }

        array_multisort($mid, SORT_ASC, $templates);

        $this->templates = $templates;
        return $templates;
    }

    /* ----------------------------------------------------------
      Get templates
    ---------------------------------------------------------- */

    function getTemplates() {
        return $this->templates;
    }

    function getStores() {
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
        $this->stores = $_stores;
        return $_stores;
    }

    /* ----------------------------------------------------------
      Get datas
    ---------------------------------------------------------- */

    function getDefaultData($store, $tpl) {
        $datas = array(
            'store' => $this->stores[$store],
            'salable' => 'yes',
            'addAllLink' => Mage::getUrl('*/shared/allcart', array(
                'code' => 'foo'
            )) ,
            'viewOnSiteLink' => Mage::getUrl('*/shared/index', array(
                'code' => 'foo'
            )) ,
            'customerName' => 'Jean-Michel Lorem',
            'customerEmail' => $this->storeEmail,
            'name' => 'Jean-Michel Lorem',
            'customer_first_name' => 'Jean-Pierre',
            'sender_name' => 'Jean-Pierre Ipsum',
            'product_url' => 'https://github.com/Darklg',
            'product_name' => 'Barre de faire',
            'product_image' => 'http://placehold.it/75x75',
            'message' => 'The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach. Everyone who has ever taken a shower has had an idea. It’s the person who gets out of the shower, dries off, and does something about it that makes a difference — Nolan Bushnell. ',
        );

        $template = $this->templates[$tpl];

        /* New order template
         -------------------------- */

        if (isset($template['order'])) {

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

        if (isset($template['shipment'])) {
            $datas['shipment'] = $this->getInvoice();
        }

        /* Invoice
         -------------------------- */
        if (isset($template['invoice'])) {
            $datas['invoice'] = $this->getInvoice();
        }

        /* Credit memo
         -------------------------- */

        if (isset($template['creditmemo'])) {
            $datas['creditmemo'] = $this->getCreditMemo();
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
            $datas['data'] = $this->getData();
        }

        /* New account & Forgot password
         -------------------------- */

        if (isset($template['customer'])) {

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

        if (isset($template['alertGrid'])) {
            $datas['alertGrid'] = $this->getAlertGrid($datas['customer']);
        }

        /* Subscription confirmation
         -------------------------- */

        if ($tpl == 'newsletter_subscription_confirm_email_template') {
            $datas['subscriber'] = $this->getSubscriber();
        }

        /* Wishlist
         -------------------------- */

        if ($tpl == 'wishlist_email_email_template') {
            $datas['items'] = $this->getWishlistItems();
            $datas['message'] = 'Please buy this';
        }

        /* AW Help Desk 3
         -------------------------- */

        if (isset($template['aw_hdu3'])) {
            $datas['is_agent_changed'] = true;
            $datas['is_department_changed'] = true;
            $datas['is_status_changed'] = true;
            $datas['agent_name'] = 'Jean-Michel Support';
            $datas['department_name'] = 'Main Department';
            $datas['ticket_status'] = 'Waiting for reply';
            $datas['ticket_uid'] = 'OBO-46271';
            $datas['ticket_subject'] = 'The world needs dreamers and the world needs doers.';
        }
        return $datas;
    }

    function getShipment() {
        $shipment = new Varien_Object();
        $shipment->setData(array(
            'increment_id' => '100000022'
        ));
        return $shipment;
    }

    function getInvoice() {
        $invoice = new Varien_Object();
        $invoice->setData(array(
            'increment_id' => '100000022'
        ));
        return $invoice;
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

    /* ----------------------------------------------------------
      Use template data
    ---------------------------------------------------------- */

    function setMailTemplateAndUseDatas($tpl, $store, $datas) {
        $this->mailTemplate = $this->mailModel->load(3)->loadDefault($tpl);

        $this->mailTemplate->setDesignConfig(array(
            'area' => 'frontend',
            'store' => $store
        ));
        if (isset($_GET['email'], $_GET['send']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) !== false) {
            $_SESSION['integento__emailtester__email'] = $_GET['email'];
            $this->sendTemplateByMail($_GET['email'], $datas);
        }
        else if (isset($_GET['get_template_details'])) {
            $this->getTemplateDetails($tpl, $datas);
        }
        else if (isset($_GET['save_admin_tpl'])) {
            $this->saveTemplateInAdmin($tpl, $datas);
        }
        else {
            echo $this->displayTemplate($datas);
            die;
        }
    }

    function sendTemplateByMail($email, $datas) {
        $this->mailTemplate->setSenderName($this->storeName);
        $this->mailTemplate->setSenderEmail($this->storeEmail);
        $this->mailTemplate->send($email, 'Email Tester', $datas);
        $this->addMessageAndRedirect('success', 'Mail has been successfully sent !');
        return;
    }

    function getTemplateDetails($tpl, $datas) {
        if (!isset($this->templates[$tpl])) {
            return;
        }

        $_template = $this->templates[$tpl];

        /* Fake a template inclusion */
        ob_start();
        echo $this->displayTemplate($datas);
        ob_get_clean();

        /* Retrieve included files */
        $included_files = array();
        if (isset($_template['templates'])) {
            $all_included_files = get_included_files();
            foreach ($all_included_files as $file) {
                /* Keep only .phtml files */
                if (strrchr($file, '.phtml') == '.phtml') {
                    $file_details = explode('app/', $file);
                    $included_files[] = 'app/' . end($file_details);
                }
            }
        }

        /* Display page */
        echo '<!DOCTYPE HTML><html lang="en-EN"><head>';
        echo '<meta charset="UTF-8" />';
        echo '<title>' . $tpl . '</title>';
        echo '<link rel="stylesheet" type="text/css" href="assets/style.css" />';
        echo '</head><body>';
        echo '<h1>Template : ' . $_template['name'] . '</h1>';
        if (isset($_template['templates'])) {
            echo '<h2>Template file</h2>';
            echo '<ul>';
            foreach ($_template['templates'] as $value) {
                echo '<li>' . $value . '</li>';
            }
            echo '</ul>';
        }
        if (!empty($included_files)) {
            echo '<h2>Included files</h2>';
            echo '<ul>';
            foreach ($included_files as $value) {
                echo '<li>' . $value . '</li>';
            }
            echo '</ul>';
        }

        echo '</body></html>';
        return;
    }
    function saveTemplateInAdmin($tpl, $datas) {

        $_core = Mage::getSingleton('core/resource');
        $_read = $_core->getConnection('core_read');
        $_write = $_core->getConnection('core_write');
        $_templates = $this->getTemplates();
        $_tableName = $_core->getTableName('core_email_template');
        $_tpl = $this->mailTemplate->getData();
        $_tpl['template_code'] = '[' . $datas['store']->getName() . '] ';
        if ($this->mailTemplate->getData('template_code')) {
            $_tpl['template_code'].= $this->mailTemplate->getData('template_code');
        }
        else {
            if (array_key_exists('name', $_templates[$tpl])) {
                $_tpl['template_code'].= $_templates[$tpl]['name'];
            }
            else {
                $_tpl['template_code'].= $this->mailTemplate->getData('template_subject');
            }
        }

        $_tpl['template_text'] = trim($this->mailTemplate->getData('template_text'));
        $_tpl['template_type'] = $this->mailTemplate->getData('template_type');
        $_tpl['template_subject'] = $this->mailTemplate->getData('template_subject');

        $_existingTemplatesCodes = $_read->fetchCol('SELECT template_code FROM ' . $_tableName);

        if (in_array($_tpl['template_code'], $_existingTemplatesCodes)) {
            $this->addMessageAndRedirect('error', sprintf('The template named <b>"%s"</b> already exists !', $_tpl['template_code']));
        }

        $_write->insert($_tableName, $_tpl);

        $this->addMessageAndRedirect('success', sprintf('The template named <b>"%s"</b> has been successfully saved !', $_tpl['template_code']));
    }

    function displayTemplate($datas) {
        header('Content-Type: text/html; charset=utf-8');
        return $this->mailTemplate->getProcessedTemplate($datas);
    }
}

