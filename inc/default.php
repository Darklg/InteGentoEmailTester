<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Mail preview</title>
<link rel="stylesheet" type="text/css" href="assets/style.css" />
</head>
<body>
<div class="side">
<h1>Mail preview</h1>

<?php echo $inteGentoEmailTester->displayMessages(); ?>

<form id="integento-form" action="index.php" method="get">
<?php
/* Templates */
echo '<p><label for="template">Template :</label>';
echo '<select id="template" name="template">';
$i = 0;
$_lastGroup = '';
foreach ($_templates as $tpl_id => $template) {
    $_groupName = $template['group'];
    if(isset($_groups[$_groupName])){
        $_groupName = $_groups[$_groupName];
    }
    if ($_groupName != $_lastGroup) {
        if ($i > 0) {
            echo '</optgroup>';
        }
        echo '<optgroup label="' . $_groupName . '">';
        $_lastGroup = $_groupName;
    }
    $tplName = $tpl_id;
    if (isset($template['name'])) {
        $tplName = $template['name'];
    }
    $_isCurrent = isset($_SESSION['integento__emailtester__tpl']) && $_SESSION['integento__emailtester__tpl'] == $tpl_id;
    echo '<option ' . ($_isCurrent ? 'selected="selected"' : '') . ' value="' . $tpl_id . '">' . $tplName . '</option>';
    $i++;
}
echo '</optgroup></select></p>';

/* Stores */
echo '<p><label for="store">Store :</label>';
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
    echo '<option ' . ($_isCurrent ? 'selected="selected"' : '') . ' value="' . $storeId . '">' . $store->getName() . '</option>';
    $i++;
}
echo '</optgroup>';
echo '</select></p>';

/* Email */
echo '<p id="box-email"><label for="email">Email</label><input type="email" id="email" name="email" value="'.(isset($_SESSION['integento__emailtester__email']) ? $_SESSION['integento__emailtester__email'] : '').'" /></p>';

?>
<button type="submit" id="button_open" name="submit">Open</button>
<button type="submit" id="button_preview" name="submit">Preview</button>
<button type="submit" id="button_details" name="get_template_details">Details</button>
<button type="submit" id="button_send" name="send">Send by email</button>
</form>
</div>
<div class="preview"><iframe name="preview" style="border:0;"></iframe></div>
<script src="assets/script.js"></script>
</body></html>
