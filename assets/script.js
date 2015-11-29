function $__(id) {
    return document.getElementById(id);
}

(function() {
    'use strict';
    /* Form action */
    var form = $__('integento-form');
    if (form) {
        $__('button_details').addEventListener('click', function(e) {
            form.target = 'preview';
        }, false);
        $__('button_preview').addEventListener('click', function(e) {
            form.target = 'preview';
        }, false);
        $__('button_send').addEventListener('click', function(e) {
            form.target = '_self';
            if ($__('email').value == '') {
                e.preventDefault();
            }
        }, false);
    }
    if ($__('button_save')) {
        $__('button_save').addEventListener('click', function(e) {
            if (!confirm('Do you really want to save this email into the admin templates list ?')) {
                e.preventDefault();
            }
        }, false);
    }
    if ($__('button_delete')) {
        $__('button_delete').addEventListener('click', function(e) {
            if (!confirm('Do you really want to delete this email from the admin templates list ?')) {
                e.preventDefault();
            }
        }, false);
    }
}());