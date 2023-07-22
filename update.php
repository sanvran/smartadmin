<?php
// start session
session_start();
// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;
// if session not set go to login page
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}
// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
    session_destroy();
    header("location:index.php");
}
// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;
?>
<?php include "header.php"; ?>
<html>

<head>
    <meta charset="UTF-8">
    <script src="dist/js/jquery.min.js"></script>
    <title>Update | <?= $settings['app_name'] ?> - Dashboard</title>

    <style type="text/css">
        .dropzone {
            min-height: 150px;
            border: 3px dashed rgb(10 71 114 / 84%);
            background: white;
            padding: 20px 20px;
        }
    </style>
</head>

<body>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Update /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <form method="POST" enctype="multipart/form-data" id="update_form">
                        <div id="dropzone" class="dropzone"></div>
                    </form>
                    <div class="" style="margin-top: 10px;">
                        <input type="submit" id="upload-files-btn" value="Upload" class="btn btn-primary" name="btnAdd" style="float: left;" />
						<?php if(file_exists("update")){?>
						<a href="/update" id="go-to-update" class="btn btn-warning" name="update" style="margin-left:10px ;float: left;">Go to update page</a>
						<?php } ?>
                    </div>
                </div>
            </div>
            <hr />
        </section>
    </div><!-- /.content-wrapper -->
    <div class="separator"> </div>

    <?php include "footer.php"; ?>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzone", {
            paramName: "zip_file",
            url: 'public/update-file.php',
            autoProcessQueue: false,
            parallelUploads: 10,
            autoDiscover: false,
            addRemoveLinks: true,
            dictResponseError: 'Error',
            uploadMultiple: true,
            timeout: 1800000,
            acceptedFiles: '.zip',
            dictDefaultMessage: '<p class="text-dark"><b>Select Files <br> or <br> Drag & Drop Images here</b></p>',
        });
        myDropzone.on("addedfile", function(file) {
            var i = 0;
            if (this.files.length) {
                var _i, _len;
                for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                    if (this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
                        this.removeFile(file);
                        i++;
                    }
                }
            }
        });
        myDropzone.on('sending', function(file, xhr, formData) {
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var response = JSON.parse(this.response);
                    if (response['error'] == false) {
                        iziToast.success({
                            position: 'topRight',
                            message: response['message'],
                        });
                    } else {
                        iziToast.error({
                            position: 'topRight',
                            title: 'Error',
                            message: response['message'],
                        });
                    }
                    $(file.previewElement).find('.dz-error-message').text(response.message);
                }
            };
        });


        $('#upload-files-btn').on('click', function(e) {
            e.preventDefault();
            myDropzone.processQueue();
        });
    </script>
</body>

</html>