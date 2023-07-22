<section class="content-header">
    <h1>Web Front-End Settings</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">

    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <?php if ($permissions['settings']['read'] == 1) {
                if ($permissions['settings']['update'] == 0) { ?>
                    <div class="alert alert-danger">You have no permission to update settings</div>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update Url Settings</h3>
                    </div>
                    <!-- /.box-header -->
                    <?php
                    $db->sql("SET NAMES 'utf8'");
                    $sql = "SELECT * FROM settings WHERE  variable='front_end_settings'";
                    $db->sql($sql);

                    $res_time = $db->getResult();
                    if (!empty($res_time)) {
                        foreach ($res_time as $row) {
                            $data = json_decode($row['value'], true);
                        }
                    }
                    ?>
                    <!-- form start -->
                    <form id="system_configurations_form" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="front_end_settings" name="front_end_settings" required="" value="1" aria-required="true">
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Android Application's URL: </label>
                                    <input type="text" class="form-control" name="android_app_url" value="<?= isset($data['android_app_url']) ? $data['android_app_url'] : '' ?>" placeholder='Android App URL' />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Front Website Url: </label>
                                    <input type="text" class="form-control" name="call_back_url" value="<?= isset($data['call_back_url']) ? $data['call_back_url'] : '' ?>" placeholder='Front Website Url' />
                                </div>
                            </div>

                            <!--  -->
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Copy Right Title: </label>
                                    <input type="text" class="form-control" name="copy_right_title" value="<?= isset($data['copy_right_title']) ? $data['copy_right_title'] : '' ?>" placeholder='Copy Right Title' />

                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Copy Right URL: </label>
                                    <input type="text" class="form-control" name="copy_right_url" value="<?= isset($data['copy_right_url']) ? $data['copy_right_url'] : '' ?>" placeholder='Copy Right Url' />

                                </div>
                            </div>

                            <!--  -->
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Common Meta Keywords: </label>
                                    <textarea id="common_meta_keywords" class="form-control" name="common_meta_keywords" placeholder="Common Meta Keywords" rows="4" cols="30"><?= isset($data['common_meta_keywords']) ? $data['common_meta_keywords'] : '' ?></textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Common Meta Description: </label>
                                    <textarea id="common_meta_description" class="form-control" name="common_meta_description" placeholder="Common Meta Description" rows="4" cols="30"><?= isset($data['common_meta_description']) ? $data['common_meta_description'] : '' ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="app_name">Favicon Icon: </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?= isset($data['favicon']) ? DOMAIN_URL . 'dist/img/' . $data['favicon'] : ''; ?>" style="max-width: 100%;" /><br>
                                        </div>
                                    </div>
                                    <br> <input type='file' name='favicon' id='favicon' accept="image/*" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="app_name">Web Logo: </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?= isset($data['web_logo']) ? DOMAIN_URL . 'dist/img/' . $data['web_logo'] : ''; ?>" style="max-width: 100%;" /><br>
                                        </div>
                                    </div>
                                    <br> <input type='file' name='web_logo' id='web_logo' accept="image/*" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="app_name">loading Icon: </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?= isset($data['loading']) ? DOMAIN_URL . 'dist/img/' . $data['loading'] : ''; ?>" style="max-width: 100%;" /><br>
                                        </div>
                                    </div>
                                    <br> <input type='file' name='loading' id='loading' accept="image/*" />
                                </div>

                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="show_color_picker_in_website">Show Color Picker In Website? :</label>&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" id="show_color_picker_in_website_btn" class="js-switch" <?= $data['show_color_picker_in_website'] == 1 ? 'checked' : ''; ?>>
                                <input type="hidden" id="show_color_picker_in_website" name="show_color_picker_in_website" value="<?= (isset($data['show_color_picker_in_website']) && !empty($data['show_color_picker_in_website'])) ? $data['show_color_picker_in_website'] : 0; ?>">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="colorpicker">Color Picker:</label>
                                <input type="color" id="color" name='color' value="<?= isset($data['color']) ? $data['color'] : '' ?>">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="show_theme_on_website">Which theme you want to use? :</label>
                                <select id="show_theme_on_website" name="show_theme_on_website" required class="form-control col-md-12 select2">


                                    <option selected="selected" hidden> <b> <?= isset($data['show_theme_on_website']) ? $data['show_theme_on_website'] : ''  ?> </b> </option>

                                    <option id="eCart_01" value="eCart_01">eCart_01</option>
                                    <option id="eCart_02" value="eCart_02">eCart_02</option>


                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div id="result"></div>
                        <div class="box-footer">
                            <input type="submit" id="settings_btn_update" class="btn-primary btn" value="Update" name="settings_btn_update" />
                        </div>

                    </form>
                <?php } else { ?>
                    <div class="alert alert-danger">You have no permission to view settings</div>
                <?php } ?>
                </div>
                <!-- /.box -->
        </div>
    </div>
</section>
<div class="separator"> </div>
<script>
    $('#system_configurations_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: formData,
            beforeSend: function() {
                $('#settings_btn_update').html('Please wait..');
            },
            cache: false,
            contentType: false,
            processData: false,
            success: function(result) {
                $('#result').html(result);
                $('#result').show().delay(5000).fadeOut();
                $('#settings_btn_update').html('Save Settings');
            }
        });
    });
</script>
<script>
    var changeCheckbox = document.querySelector('#show_color_picker_in_website_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#show_color_picker_in_website').val(1);
        } else {
            $('#show_color_picker_in_website').val(0);
        }
    };
</script>
<!-- <script>
    $('#show_theme_on_website').on('change', function(e) {
        if ($(this).find(':selected').val('theme-1')) {

        }
        // $('#system_timezone_gmt').val(gmt);

    });
</script> -->
<!-- <script>
    $("#show_theme_on_website").change(function() {

        var id = $(this).children(":selected").attr("id");
        if (id == 'theme-1') {
            $('#theme-1').attr('hidden', true);
            $('#theme-2').removeAttr('hidden');
        }
        if (id == 'theme-2') {
            $('#theme-2').attr('hidden', true);
            $('#theme-1').removeAttr('hidden');
        }
    });

</script> -->