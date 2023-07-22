<?php
$offset = 0;
$limit = 5;
$sql = "select tm.*,t.status,case when tm.type='admin' then a.username else u.name end as name from ticket_messages tm
    left join admin a on a.id = tm.ticket_id
    left join tickets t on t.id=tm.ticket_id
    left join users u on u.id = tm.user_id  WHERE tm.ticket_id = $id1 ORDER BY tm.id DESC limit $offset,$limit";
// echo $sql;
$db->sql($sql);
$messages = $db->getResult();
?>

<section class="content-header">
    <h1>Direct Chat<small><a href='support-system.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Tickets</a></small></h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- DIRECT CHAT PRIMARY -->
            <div class="box box-primary">
                <section class="box-header content-header">
                    <div class="row">
                        <div class="col-md-5">
                            <b>Ticket_Id:</b> #<?= $ticket[0]['id'] ?><br>
                            <b>Ticket_Title:</b> <?= $ticket[0]['message'] ?><br>
                            <?php if ($ticket[0]['status'] == 'opened') {
                                $status = '<label class="label label-primary">Opened</label>';
                            } elseif ($ticket[0]['status'] == 'closed') {
                                $status = '<label class="label label-danger">Closed</label>';
                            } elseif ($ticket[0]['status'] == 'reopen') {
                                $status = '<label class="label label-warning">Reopen</label>';
                            } elseif ($ticket[0]['status'] == 'resolved') {
                                $status = '<label class="label label-success">Resolved</label>';
                            } elseif ($ticket[0]['status'] == 'pending') {
                                $status = '<label class="label label-info">Pending</label>';
                            } ?>
                            <b>Ticket_Status:</b> <span><?= $status ?></span><br>
                        </div>
                        <div class="col-md-3">
                            <b>User:</b> <?= $ticket[0]['name'] ?><br>
                            <b>Created_ON:</b> <?= $ticket[0]['created'] ?><br><br />
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-3">
                            <form method="POST" id="filter_form" name="filter_form">
                                <input type="hidden" name="ticket_id" id="ticket_id" value="<?= $id1; ?>">
                                <input type="hidden" name="change_ticket_status" id="change_ticket_status" value="1">
                                <div class="form-group">
                                    <select id="change_status" name="change_status" placeholder="Select Status" required class="form-control ticket_status">
                                        <!-- <option value="">Change Ticket Status</option> -->
                                        <option value='pending' <?= $ticket[0]['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value='opened' <?= $ticket[0]['status'] == 'opened' ? 'selected' : ''; ?>>Opened</option>
                                        <option value='resolved' <?= $ticket[0]['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value='closed' <?= $ticket[0]['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        <option value='reopen' <?= $ticket[0]['status'] == 'reopen' ? 'selected' : ''; ?>>Reopen</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-9">
                            <div id="result"></div>
                            <?= $ticket[0]['status'] == 'resolved' || $ticket[0]['status'] == 'closed' ? '<label class="alert alert-warning">You can not send messgae because ticket is ' . ucfirst($ticket[0]['status']) . ' !</label>' : ''; ?>
                        </div>
                    </div>
                </section>
                <div class="box box-primary box-solid direct-chat direct-chat-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Direct Chat</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>

                    <!-- lode more -->
                    <!-- /.box-header -->
                    <div class="box-body chat-body">
                        <!-- Conversations are loaded here -->
                        <div id="chat" class="direct-chat-messages" data-limit="<?= $limit ?>" data-offset="<?= $offset + $limit ?>" data-max-loaded="false">
                            <!-- Message. Default to the left -->
                            <div class='inner'>
                                <?php
                                if (!empty($messages)) {
                                    $messages = array_reverse($messages);
                                    for ($i = 0; $i < count($messages); $i++) {

                                ?>

                                        <?php if ($messages[$i]['type'] == 'admin') { ?>
                                            <div class="direct-chat-msg right">
                                                <div>
                                                    <span class="user">Admin</span>
                                                    <div><span class="admin_created"><?= $messages[$i]['date_created']; ?></span></div>
                                                </div>
                                                <div class="triangle1"></div>
                                                <div id="" class="message1">
                                                    <span class="chat"><?= nl2br($messages[$i]['message']); ?></span> <br />
                                                </div>
                                            </div>
                                        <?php  } else {
                                        ?>
                                            <div id="message" class="direct-chat-msg">
                                                <div>
                                                    <span> <?= $messages[$i]['name']; ?></span>&nbsp;&nbsp;
                                                    <span><?= $messages[$i]['date_created']; ?></span>
                                                </div>
                                                <div class="triangle"></div>
                                                <div class="message">
                                                    <?php if (!empty($messages[$i]['attachments']) && $messages[$i]['attachments'] != 'NULL') {
                                                        $attachments = json_decode($messages[$i]['attachments']); ?>
                                                        <span><?= nl2br($messages[$i]['message']); ?></span><br>
                                                        <?php for ($j = 0; $j < count($attachments); $j++) { ?>
                                                            <br><a href="<?= DOMAIN_URL . $attachments[$j]; ?>" target="_blank"><img src="<?= $attachments[$j]; ?>" height="60" /></a><br />
                                                        <?php } ?>
                                                    <?php } else {
                                                    ?>
                                                        <span><?= nl2br($messages[$i]['message']); ?></span><br />
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php
                                        } ?>
                                <?php  }
                                } else {
                                    echo "<p class='text-center'>No messages found. Start sending messages</p>";
                                } ?>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <form method="post" id="form" enctype="multipart/form-data">
                            <input type="hidden" name="type" value="<?= ($_SESSION['email'] == $ticket[0]['email']) ? "user" : "admin"; ?>" id="type">
                            <input type="hidden" name="send" value="1" id="send">

                            <div class="input-group mb-5">
                                <textarea name="message" id="comment" rows="5" class="chat-btn" required placeholder="Type Message ..." style='width:100%'></textarea>
                                <span class="input-group-btn">
                                    <!-- <button type="button" class="btn btn-lg btn-success btn-flat atta_btn" id="atta_btn" name="attachments[]"><i class="fa fa-paperclip" aria-hidden="true"></i></button> -->
                                    <button type="submit" class="btn btn-lg btn-primary btn-flat" id="submit" name="submit" <?= $ticket[0]['status'] == 'resolved' || $ticket[0]['status'] == 'closed' ? 'disabled' : 'enabled'; ?>><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <!-- /.box-footer-->
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var ticket_id = "<?= (isset($_GET['id']) && !empty(trim($_GET['id'])) && is_numeric($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : ""; ?>";
    var type_id = "<?= (isset($_SESSION['id']) && !empty(trim($_SESSION['id'])) && is_numeric($_SESSION['id'])) ? $db->escapeString($fn->xss_clean($_SESSION['id'])) : ""; ?>";
    var username = "<?= (isset($_SESSION['user']) && !empty(trim($_SESSION['user']))) ? $db->escapeString($fn->xss_clean($_SESSION['user'])) : " Admin "; ?>";

    $(document).ready(function() {
        $("#chat").scrollTop($("#chat")[0].scrollHeight);
        $('#chat').scroll(function() {
            if ($('#chat').scrollTop() == 0) {
                load_messages($(this));
            }
        });
    });

    $('#chat').bind('mousewheel', function(e) {
        if (e.originalEvent.wheelDelta / 120 > 0) {
            if ($(".inner")[0].scrollHeight < 380) {
                load_messages($(this));
            }
        }
    });

    function load_messages(element) {
        var limit = element.data('limit');
        var offset = element.data('offset');

        var data = {
            get_complaint_comments: 1,
            ticket_id: ticket_id,
            limit: limit,
            offset: offset
        }
        element.data('offset', limit + offset);
        var max_loaded = element.data('max-loaded');
        if (max_loaded == false) {
            var loader = '<div id="loader"><img src="images/loader.gif" alt="Loading. please wait.. ." title="Loading. please wait.. ."></div>';
            $.ajax({
                url: 'public/db-operation.php',
                method: "post",
                data: data,
                processData: true,
                cache: false,
                beforeSend: function() {
                    $('.inner').prepend(loader);
                },
                dataType: 'json',
                success: function(result) {
                    var messages_html = is_right = "";
                    if (result.error == false && result.data.length > 0) {
                        result.data.forEach(message => {
                            is_right = (message.type == 'admin') ? 'right' : 'left';
                            msg_class = (message.type == 'admin') ? 'message1' : 'message';
                            tiangle_class = (message.type == 'admin') ? 'triangle1' : 'triangle';
                            admin_created = (message.type == 'admin') ? 'admin_created' : 'created';
                            username = (message.username != '' && message.username != 'admin') ? message.username : 'Admin';
                            messages = message.message.replace(/\n/g, '<br>');

                            if (message.attachments != '' && message.attachments != 'NULL') {
                                var attachments = jQuery.parseJSON(message.attachments)
                                var mess = messages;
                                for (var j = 0; j < attachments.length; j++) {
                                    mess += '<br><a href=" ' + attachments[j] + '" target="_blank"><img src=" ' + attachments[j] + '" height="60" /></a><br />';
                                }
                            } else {
                                var mess = messages;
                            }

                            messages_html = '<div id="message" class="direct-chat-msg ' + is_right + '">' +
                                '<div>' +
                                '<span class= "user"> ' + username + '</span>&nbsp;&nbsp' +
                                '<span class="' + admin_created + '">' + message.date_created + '</span>' +
                                '</div>' +
                                '<div class="' + tiangle_class + '"></div>' +
                                '<div class="' + msg_class + '">' +
                                '<span class="chat" style="white-space: pre-wrap;">' + mess + '</span><br />' +
                                '<div>' +
                                '</div>' +
                                '</div>' +
                                '</div>' + messages_html;
                        });
                        $('.inner').prepend(messages_html);
                    } else {
                        element.data('offset', offset);
                        element.data('max-loaded', true);
                        $('.inner').prepend('<div class="text-center"> <p>You have reached the top most message!</p></div>');
                    }
                    $('#loader').remove();
                    $('#chat').scrollTop(15); // Scroll alittle way down, to allow user to scroll more
                }
            });
        }
    }

    $('#form').on('submit', function(e) {
        e.preventDefault();

        let now = new Date();
        var date = '<?= date('Y-m-d H:m:s'); ?>';

        var message = $('#comment').val().trim();
        var formData = new FormData(this);
        formData.append('ticket_id', ticket_id);
        formData.append('type_id', type_id);
        formData.append('message', message);
        if (message != "") {
            $.ajax({
                url: 'public/db-operation.php',
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    var messages_html = "";
                    message = message.replace(/\n/g, '<br>');
                    messages_html += '<div id="message" class="direct-chat-msg right">' +
                        '<div>' +
                        '<span class= "user"> ' + username.charAt(0).toUpperCase() + username.slice(1) + '</span>' +
                        '<span class="admin_created">' + date + ' </span>' +
                        '</div>' +
                        '<div class="triangle1"></div>' +
                        '<div class="message1">' +
                        '<span class="chat" style="white-space: pre-wrap;" >' + message + '</span><br />' +
                        '<div>' +
                        '</div>' +
                        '</div>' +
                        '</div>'
                    $('.inner').append(messages_html);
                    $('#comment').val('');
                    $("#chat").scrollTop($("#chat")[0].scrollHeight);
                }
            });
        }
    });
</script>

<script>
    $('#filter_form').on('change', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        if (confirm('Are you sure want to change status?')) {
            $.ajax({
                type: 'POST',
                url: "public/db-operation.php",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    location.reload();
                }
            });
        }
    });
</script>


</body>

</html>