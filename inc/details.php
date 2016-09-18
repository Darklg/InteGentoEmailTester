<!DOCTYPE HTML><html lang="en-EN"><head>
<meta charset="UTF-8" />
<title><?php echo $tpl; ?></title>
<link rel="stylesheet" type="text/css" href="assets/style.css" />
</head><body>
<h1>Template : <?php echo $_template['name']; ?></h1>

<?php

/* ----------------------------------------------------------
  Details on admin template
---------------------------------------------------------- */

if (is_array($_adminTemplate) && isset($_adminTemplate['template_id'])) {
    $_isAdminTemplate = true;
    echo '<h2>Admin template</h2>';
    echo '<ul>';
    echo '<li><strong>ID</strong>: ' . $_adminTemplate['template_id'] . '</li>';
    if (isset($_template['conf'])) {
        echo '<li><strong>Config</strong>: <span contenteditable>' . $_template['conf'] . '</span></li>';
    }
    echo '<li><strong>Code</strong>: <span contenteditable>' . $_adminTemplate['template_code'] . '</span></li>';
    echo '<li><strong>Subject</strong>: <span contenteditable>' . $_adminTemplate['template_subject'] . '</span></li>';
    if ($_adminTemplate['added_at']) {
        echo '<li><strong>Added</strong>: ' . $_adminTemplate['added_at'] . '</li>';
    }
    if ($_adminTemplate['modified_at']) {
        echo '<li><strong>Modified</strong>: ' . $_adminTemplate['modified_at'] . '</li>';
    }
    echo '</ul>';
}

/* ----------------------------------------------------------
  Template content
---------------------------------------------------------- */

if (!empty($_templateText)) {
    echo '<h2>Template Content</h2>';
    echo '<div>';
    echo '<textarea cols="30" rows="10" style="width:100%;height:100px;font-family:monospace;" onfocus="this.select()">' . htmlentities($_templateText) . '</textarea>';
    echo '<div><strong>Source:</strong> <span contenteditable>' . $_templateSrc . '</span></div>';
    echo '</div>';

    // Form
    echo '<form action="" target="_parent" method="get"><div>
    <input type="hidden" name="template" value="' . $tpl . '" />
    <input type="hidden" name="store" value="' . $this->store . '" />';
    if ($_isAdminTemplate) {
        echo '<button type="submit" id="button_delete" name="delete_admin_tpl">Delete this admin Template</button>';
    }
    else {
        echo '<button type="submit" id="button_save" name="save_admin_tpl">Save as admin Template</button>';
        if(empty($_template['conf'])){
            echo '<div><small>You will need to associate manually this template in the Magento admin.</small></div>';
        }
    }
    echo '</div></form>';
}

/* ----------------------------------------------------------
  Template files
---------------------------------------------------------- */

if (isset($_template['templates'])) {
    echo '<h2>Template file</h2>';
    echo '<ul>';
    foreach ($_template['templates'] as $value) {
        echo '<li contenteditable>' . $value . '</li>';
    }
    echo '</ul>';
}

/* ----------------------------------------------------------
  Included files
---------------------------------------------------------- */

if (!empty($included_files)) {
    echo '<h2>Included files</h2>';
    echo '<ul>';
    foreach ($included_files as $value) {
        echo '<li contenteditable>' . $value . '</li>';
    }
    echo '</ul>';
}
?>
<script src="assets/script.js"></script>
</body></html>
