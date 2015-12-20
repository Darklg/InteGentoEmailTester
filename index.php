<?php

/**
 * Email tester v 0.27
 *
 * @author      Darklg <darklg.blog@gmail.com>
 * @copyright   Copyright (c) 2015 Darklg
 * @license     MIT
 */

$testerVersion = '0_27';
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
$inteGentoEmailTester = new inteGentoEmailTester();

/* Get values
 -------------------------- */

$_templates = $inteGentoEmailTester->getTemplates();
$_groups = $inteGentoEmailTester->getGroups();
$_stores = $inteGentoEmailTester->getStores();

/* ----------------------------------------------------------
  Default page
---------------------------------------------------------- */

$tpl = $_GET['template'];
if (!isset($_GET['template']) || !array_key_exists($_GET['template'], $_templates)) {
    include 'inc/default.php';
    die;
}
$_SESSION['integento__emailtester__tpl'] = $tpl;


/* ----------------------------------------------------------
  Translate
---------------------------------------------------------- */

if (isset($_GET['store']) && array_key_exists($_GET['store'], $_stores)) {
    $_store = $_GET['store'];
}

$inteGentoEmailTester->setStore($_store);

$_SESSION['integento__emailtester__store'] = $_store;
$_locale = Mage::getStoreConfig('general/locale/code', $_store);
Mage::getSingleton('core/translate')->setLocale($_locale)->init('frontend', true);

/* ----------------------------------------------------------
  Load template
---------------------------------------------------------- */

$datas = $inteGentoEmailTester->getDefaultData($_store, $tpl);
$inteGentoEmailTester->setMailTemplateAndUseDatas($tpl, $_store, $datas);

