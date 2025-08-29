 <!-- Simplicity is an acquired taste. - Katharine Gerould -->
 <script src="https://cdn.tiny.cloud/1/oqnsgm3v43ghstol8pvvolv8z0g8kjyp40u9x21zwq8hruhj/tinymce/7/tinymce.min.js"
     referrerpolicy="origin"></script>
 <script>
     tinymce.init({
         selector: 'textarea#description', // Replace this CSS selector to match the placeholder element for TinyMCE
         plugins: 'code table lists link image',
         toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | link unlink | image  | code | table',
         image_title: true,
         automatic_uploads: false, // No uploads, just URL-based insertion
         file_picker_types: 'image',
         images_upload_handler: function(blobInfo, success, failure) {
             failure("Upload disabled. Please use an image URL."); // Optional safeguard
         },
         setup: function(editor) {
             editor.on('change', function() {
                 editor.save(); // Same as tinymce.triggerSave(), but for this specific instance
             });
         }
     });
 </script>
