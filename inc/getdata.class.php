<?php
class inteGentoEmailTester {

    private $groups;
    private $templates;

    /* ----------------------------------------------------------
      Init
    ---------------------------------------------------------- */

    public function __construct() {
        @session_start();
        $this->config_file = INTEGENTOEMAILTESTER_PATH . 'inc/etc/config.xml';
        $this->baseDir = Mage::getBaseDir();
        $this->mailModel = Mage::getModel('core/email_template');
        $this->emailBasePath = Mage::getBaseDir('locale') . DS . '%s' . DS . 'template' . DS . 'email' . DS;
        $this->setStore(0);
        $this->loadTemplates();
        $this->setTemplates($this->templates);
        $this->setMessages();
    }

    public function setStore($store) {
        $this->store = $store;
        $this->storeName = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME, $store);
        $this->storeEmail = Mage::getStoreConfig('trans_email/ident_general/email', $store);
        $this->localeCode = Mage::getStoreConfig('general/locale/code', $store);
    }

    /* ----------------------------------------------------------
      Messages
    ---------------------------------------------------------- */

    public function addMessage($type, $message) {
        $_SESSION['integento__emailtester__messages'][$type][] = $message;
    }

    public function addMessageAndRedirect($type, $message) {
        $this->addMessage($type, $message);
        header("Location: index.php");
        die;
    }

    public function displayMessages() {

        $_tplHtml = '';
        $_html = '';

        foreach ($_SESSION['integento__emailtester__messages'] as $type => $message) {
            if (!empty($message)) {
                $_tplHtml .= '<p class="message-' . $type . '">' . implode('<br />', $message) . '</p>';
            }
            $_SESSION['integento__emailtester__messages'][$type] = array();
        }

        if (!empty($_tplHtml)) {
            $_html .= '<div id="messages" onclick="this.innerHTML=\'\';return false;">' . $_tplHtml . '</div>';
        }

        $this->emptyMessages();

        return $_html;
    }

    public function emptyMessages() {
        $_SESSION['integento__emailtester__messages'] = array(
            'error' => array(),
            'success' => array()
        );
    }

    public function setMessages() {
        if (!isset($_SESSION['integento__emailtester__messages'])) {
            $this->emptyMessages();
        }
    }

    /* ----------------------------------------------------------
      Templates
    ---------------------------------------------------------- */

    public function loadTemplates() {
        $this->groups = array();
        $this->templates = array();
        if (file_exists($this->config_file)) {
            $xml = simplexml_load_file($this->config_file);
            if (!isset($xml->templates,$xml->groups)) {
                return false;
            }
            $arr_templates = (array) $xml->templates;
            foreach ($arr_templates as $id => $template) {
                if ($id == 'comment') {
                    continue;
                }
                $templates[$id] = (array) $template;
            }
            $arr_groups = (array) $xml->groups;
            foreach ($arr_groups as $id => $group) {
                if ($id == 'comment' || !isset($group->name)) {
                    continue;
                }
                $groups[$id] = (string) $group->name;
            }
        }
        $this->templates = $templates;
        $this->groups = $groups;
    }

    public function setTemplates($templates) {

        $modules = Mage::getConfig()->getNode('modules')->children();
        $this->modulesArray = (array) $modules;
        foreach ($templates as $tpl_id => $tpl) {
            if (!isset($this->modulesArray['AW_Helpdesk3']) && isset($tpl['aw_hdu3'])) {
                unset($templates[$tpl_id]);
            }
            if (!isset($this->modulesArray['AW_Helpdeskultimate']) && isset($tpl['aw_hdu'])) {
                unset($templates[$tpl_id]);
            }
        }

        $base_tpl = Mage::getConfig()->getNode('global/template/email')->asArray();
        $default_templates = $this->mailModel->getDefaultTemplatesAsOptionsArray();
        foreach ($default_templates as $_option):
            $val = $_option['value'];
            if (empty($val)) {
                continue;
            }
            if (isset($templates[$val])) {
                if (!isset($templates[$val]['conf'])) {
                    $templates[$val]['conf'] = '';
                }
                $templates[$val]['name'] = $_option['label'];
                if (!isset($templates[$val]['templates'])) {
                    $templates[$val]['templates'] = array();
                }
                if (isset($base_tpl[$val]['file'])) {
                    $templates[$val]['templates'][] = $base_tpl[$val]['file'];
                }
            }
        endforeach;

        // $mid = array();
        // foreach ($templates as $key => $row) {
        //     $mid[$key] = $row['name'];
        // }
        // array_multisort($mid, SORT_ASC, $templates);

        $this->templates = $templates;
        return $templates;
    }

    /* ----------------------------------------------------------
      Get templates
    ---------------------------------------------------------- */

    public function getGroups() {
        return $this->groups;
    }

    public function getTemplates() {
        return $this->templates;
    }

    public function getStores() {
        $_stores = array();
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {
            $groups = $website->getGroups();
            foreach ($groups as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $_stores[$store->getId()] = $store;
                }
            }
        }
        $this->stores = $_stores;
        return $_stores;
    }

    /* ----------------------------------------------------------
      Get datas
    ---------------------------------------------------------- */

    public function getDefaultData($store, $tpl) {
        global $cachePrefixKey;
        $datas = array(
            'store' => $this->stores[$store],
            'salable' => 'yes',
            'addAllLink' => Mage::getUrl('*/shared/allcart', array(
                'code' => 'foo'
            )),
            'viewOnSiteLink' => Mage::getUrl('*/shared/index', array(
                'code' => 'foo'
            )),
            'userName' => 'Jean-Michel Lorem',
            'status' => 'Cancelled',
            'applicationName' => 'MyApplication',
            'customerName' => 'Jean-Michel Lorem',
            'customerEmail' => $this->storeEmail,
            'name' => 'Jean-Michel Lorem',
            'customer_first_name' => 'Jean-Pierre',
            'sender_name' => 'Jean-Pierre Ipsum',
            'product_url' => 'https://github.com/Darklg',
            'product_name' => 'Fake Product Name',
            'product_image' => 'http://placehold.it/75x75',
            'warnings' => '(EmailTester: Fake warnings) The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach. Everyone who has ever taken a shower has had an idea. It’s the person who gets out of the shower, dries off, and does something about it that makes a difference — Nolan Bushnell. ',
            'comment' => '(EmailTester: Fake comment) The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach. Everyone who has ever taken a shower has had an idea. It’s the person who gets out of the shower, dries off, and does something about it that makes a difference — Nolan Bushnell. ',
            'message' => '(EmailTester: Fake message) The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach. Everyone who has ever taken a shower has had an idea. It’s the person who gets out of the shower, dries off, and does something about it that makes a difference — Nolan Bushnell. '
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
            } else {

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
                Mage::app()->getCache()->save(serialize($datas), $cacheId);
            }
        }

        /* Shipment
         -------------------------- */

        if (isset($template['shipment'])) {
            $datas['shipment'] = $this->getShipment();
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
            } else {
                $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSort('entity_id', 'desc')->setPageSize(1);
                $datas['customer'] = Mage::getModel('customer/customer')->load($collection->getFirstItem()->getId());
                $datas['customer']->setData('name', '****');
                $datas['customer']->setData('password', '****');
                $datas['customer']->setData('rp_token', md5('coucou'));
                Mage::app()->getCache()->save(serialize($datas), $cacheId);
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

    public function getShipment() {
        $shipment = new Varien_Object();
        $shipment->setData(array(
            'increment_id' => '100000022'
        ));
        $h = Mage::getResourceModel('sales/order_shipment_collection');
        $collection = $h->setPageSize(1)->setCurPage(1);
        foreach ($collection as $item) {
            $shipment = $item;
            break;
        }
        return $shipment;
    }

    public function getInvoice() {
        $invoice = new Varien_Object();
        $invoice->setData(array(
            'increment_id' => '100000022'
        ));
        $h = Mage::getResourceModel('sales/order_invoice_collection');
        $collection = $h->setPageSize(1)->setCurPage(1);
        foreach ($collection as $item) {
            $invoice = $item;
            break;
        }
        return $invoice;
    }

    public function getCreditMemo() {
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

    public function getData() {
        $data = new Varien_Object();
        $data->setData(array(
            'name' => 'Jean-Michel Lorem',
            'email' => 'foo@bar.com',
            'telephone' => '123-4567890',
            'comment' => 'This is a test'
        ));
        return $data;
    }

    public function getSubscriber() {
        $collection = Mage::getModel('newsletter/subscriber')->getCollection()->setPageSize(1)->setOrder('subscriber_id', 'desc');
        foreach ($collection as $subscriber) {
            return $subscriber;
            break;
        }
        return false;
    }

    public function getAlertGrid($customer, $nbProducts = 2) {
        $_stockProducts = array();
        $_blockName = 'productalert/email_stock';
        if ($tpl == 'catalog_productalert_email_price_template') {
            $_blockName = 'productalert/email_price';
        }
        $_stockBlock = Mage::helper('productalert')->createBlock($_blockName);
        $products = Mage::getModel('catalog/product')->getCollection()->setPageSize($nbProducts);
        foreach ($products as $prod) {
            $product = Mage::getModel('catalog/product')->load($prod->getId());
            $_stockProducts[$product->getId()] = $product;
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

    public function getWishlistItems() {

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

    public function setMailTemplateAndUseDatas($tpl, $store, $datas) {
        $_request = Mage::app()->getRequest()->getParams();
        $this->mailTemplate = $this->mailModel->loadDefault($tpl, $this->localeCode);
        $this->mailTemplate->setDesignConfig(array(
            'area' => 'frontend',
            'store' => $store
        ));

        $_templateId = 0;
        if (isset($this->templates[$tpl]['conf'])) {
            $_templateId = Mage::getStoreConfig($this->templates[$tpl]['conf'], $store);
        }

        $_templateText = '';
        if (is_numeric($_templateId) && $_templateId > 0) {
            $_templateText = $this->getTemplateTextByCode($_templateId);
        }

        if (!empty($_templateText)) {
            $this->mailTemplate->setData('template_text', $_templateText);
        }

        if (isset($_request['email'], $_request['send']) && filter_var($_request['email'], FILTER_VALIDATE_EMAIL) !== false) {
            $_SESSION['integento__emailtester__email'] = $_request['email'];
            $this->sendTemplateByMail($_request['email'], $datas);
        } else if (isset($_request['get_template_details'])) {
            $this->getTemplateDetails($tpl, $datas, $_templateId);
        } else if (isset($_request['save_admin_tpl'])) {
            $this->saveTemplateInAdmin($tpl, $datas);
        } else if (isset($_request['delete_admin_tpl'])) {
            $this->deleteTemplateInAdmin($tpl);
        } else {
            echo $this->displayTemplate($datas);
            die;
        }
    }

    public function sendTemplateByMail($email, $datas) {
        $this->mailTemplate->setSenderName('Email Tester - Integento');
        $this->mailTemplate->setSenderEmail($email);
        $this->mailTemplate->send($email, 'Email Tester', $datas);
        $this->addMessageAndRedirect('success', 'Mail has been successfully sent !');
        return;
    }

    public function getTemplateDetails($tpl, $datas, $tplId = 0) {
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

        /* Admin template */
        $_isAdminTemplate = false;
        $_adminTemplate = array();
        if (is_numeric($tplId) && $tplId > 0) {
            $_adminTemplate = $this->getTemplateByCode($tplId);
        }

        /* Get template text */
        $_templateText = '';
        $_templateSrc = '';

        if (isset($_template['templates'], $_template['templates'][0])) {
            $_templateSrc = $this->getTemplateTextPath($_template['templates'][0]);
            $_templateText = Mage::app()->getTranslator()->getTemplateFile($_template['templates'][0], 'email', $this->localeCode);
        }

        // Get text from admin
        if (is_array($_adminTemplate) && isset($_adminTemplate['template_text'])) {
            $_templateText = $_adminTemplate['template_text'];
            $_templateSrc = 'Admin template';
            if (isset($_template['conf'])) {
                $_templateSrc .= ' (' . $_template['conf'] . ')';
            }
        }

        $_templateSrc = str_replace($this->baseDir, '', $_templateSrc);

        include 'details.php';

        return;
    }

    public function getTemplateTextPath($file) {
        $_filePath = sprintf($this->emailBasePath, $this->localeCode);
        if (!file_exists($_filePath . $file)) {
            $_filePath = sprintf($this->emailBasePath, Mage::app()->getLocale()->getDefaultLocale());
        }
        if (!file_exists($_filePath . $file)) {
            $_filePath = sprintf($this->emailBasePath, Mage_Core_Model_Locale::DEFAULT_LOCALE);
        }
        return $_filePath . $file;
    }

    public function saveTemplateInAdmin($tpl, $datas) {
        $_core = Mage::getSingleton('core/resource');
        $_read = $_core->getConnection('core_read');
        $_write = $_core->getConnection('core_write');
        $_templates = $this->getTemplates();
        $_tableName = $_core->getTableName('core_email_template');
        $_tpl = $this->mailTemplate->getData();
        $_tpl['template_code'] = '[' . $datas['store']->getWebsite()->getName() . ' : ' . $datas['store']->getName() . '] ';
        if ($this->mailTemplate->getData('template_code')) {
            $_tpl['template_code'] .= $this->mailTemplate->getData('template_code');
        } else {
            if (array_key_exists('name', $_templates[$tpl])) {
                $_tpl['template_code'] .= $_templates[$tpl]['name'];
            } else {
                $_tpl['template_code'] .= $this->mailTemplate->getData('template_subject');
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
        $_lastInsertId = $_write->lastInsertId();

        if (isset($_templates[$tpl]['conf']) && is_numeric($_lastInsertId)) {
            $this->saveConfig($_templates[$tpl]['conf'], intval($_lastInsertId));
        }

        $this->addMessageAndRedirect('success', sprintf('The template named <b>"%s"</b> has been successfully saved !', $_tpl['template_code']));
    }

    public function deleteTemplateInAdmin($tpl) {
        $_core = Mage::getSingleton('core/resource');
        $_read = $_core->getConnection('core_read');
        $_write = $_core->getConnection('core_write');
        $_templates = $this->getTemplates();
        $_tableName = $_core->getTableName('core_email_template');

        if (!isset($_templates[$tpl]['conf'])) {
            $this->addMessageAndRedirect('error', sprintf('The template <b>"%s"</b> do not have a config yet.', $tpl));
            return;
        }
        Mage::app()->getCacheInstance()->cleanType('config');

        $_templateId = Mage::getStoreConfig($_templates[$tpl]['conf'], $this->store);
        $_existingTemplatesCodes = $_read->fetchCol('SELECT template_id FROM ' . $_tableName);

        if (in_array($_templateId, $_existingTemplatesCodes)) {
            $this->saveConfig($_templates[$tpl]['conf'], $tpl);
            $_write->delete($_tableName, "template_id=" . $_templateId);
            $this->addMessageAndRedirect('success', sprintf('The template named <b>"%s"</b> has been successfully deleted !', $tpl));
        } else {
            $this->addMessageAndRedirect('error', sprintf('The template <b>"%s"</b> was not saved in database.', $tpl));
        }
    }

    public function saveConfig($name, $value) {
        Mage::getConfig()->saveConfig($name, $value, 'stores', $this->store)->cleanCache();
    }

    public function getTemplateByCode($id) {
        $_core = Mage::getSingleton('core/resource');
        $_read = $_core->getConnection('core_read');
        $_tableName = $_core->getTableName('core_email_template');
        return $_read->fetchRow('SELECT * FROM ' . $_tableName . ' WHERE template_id=' . $id);
    }

    public function getTemplateTextByCode($id) {
        $_row = $this->getTemplateByCode($id);
        $_templateText = '';
        if (is_array($_row) && isset($_row['template_text'])) {
            $_templateText = $_row['template_text'];
        }
        return $_templateText;
    }

    public function displayTemplate($datas) {
        header('Content-Type: text/html; charset=utf-8');
        $_template = $this->mailTemplate->getProcessedTemplate($datas, true);
        $_template = str_replace('InteGentoEmailTester', '', $_template);
        echo $_template;
    }
}
