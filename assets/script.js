(function() {
    'use strict';
    /* Form action */
    var form = document.getElementById('integento-form');
    document.getElementById('button_preview').addEventListener('click', function(e) {
        form.target = 'preview';
    }, false);
    document.getElementById('button_save').addEventListener('click', function(e) {
        form.target = '_self';
        if (!confirm('Do you really want to save this email into the admin templates list ?')) {
            e.preventDefault();
        }
    }, false);
    document.getElementById('button_send').addEventListener('click', function(e) {
        form.target = '_self';
        if (document.getElementById('email').value == '') {
            e.preventDefault();
        }
    }, false);
}());