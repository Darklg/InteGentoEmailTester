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
            'templates' => array(
                'app/locale/en_EN/template/email/product_price_alert.html',
            )
        ) ,
        'catalog_productalert_email_stock_template' => array(
            'alertGrid' => 1,
            'customer' => 1,
            'templates' => array(
                'app/locale/en_EN/template/email/product_stock_alert.html',
            )
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

        foreach (Mage::getModel('core/email_template')->getDefaultTemplatesAsOptionsArray() as $_option):
            if (isset($templates[$_option['value']])) {
                $templates[$_option['value']]['name'] = $_option['label'];
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

    function getDefaultData($store) {
        return array(
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
        echo '<!DOCTYPE HTML><html lang="en-EN"><head><meta charset="UTF-8" /><title>' . $tpl . '</title></head><body>';
        echo '<h1>' . $tpl . '</h1>';
        if (isset($this->templates[$tpl]['templates'])) {
            echo '<h2>Templates</h2>';
            echo '<ul>';
            foreach ($this->templates[$tpl]['templates'] as $value) {
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

$inteGentoEmailTester = new inteGentoEmailTester();
