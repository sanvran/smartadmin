<html>

<body>
    <footer class="main-footer">
        <div class="pull-right hidden-xs"></div>
    </footer>
    </div>

    <script src="bootstrap/js/bootstrap-table.js"></script>
    <script src="bootstrap/js/bootstrap-table-filter-control.min.js"></script>
    <script src="bootstrap/js/tableExport.js"></script>
    <script src="bootstrap/js/bootstrap-table-export.min.js"></script>
    <script src="plugins/jQueryUI/jquery-ui.min.js"></script>
    <script src="plugins/jQueryUI/raphael-min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/morris/morris.min.js"></script>
    <script src="plugins/sparkline/jquery.sparkline.min.js"></script>
    <script src="plugins/knob/jquery.knob.js"></script>
    <script src="plugins/datetimepicker/moment-with-locales.js"></script>
    <script src="plugins/datetimepicker/bootstrap-datetimepicker.js"></script>
    <script src="plugins/timepicker/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <script src="plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
    <script src="plugins/jQuery/jquery.slimscroll.min.js"></script>
    <script src="plugins/fastclick/fastclick.min.js"></script>
    <script src="dist/js/app.min.js"></script>
    <script src="dist/js/demo.js"></script>
    <script src="plugins/dropzone/dropzone.js"></script>
    <script src="plugins/izitoast/iziToast.min.js"></script>
    <script src="plugins/izitoast/iziToast.js"></script>
    <script src="plugins/select2/select2.min.js"></script>
    <script>
        tinymce.init({
            selector: '.addr_editor',
            height: 300,
            menubar: true,
            plugins: [
                'advlist autolink lists charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount directionality'
            ],
            directionality: 'ltr',
            toolbar: 'undo redo | formatselect | fontsizeselect |' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ltr rtl |' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        });
    </script>
    <script>
        $(function() {
            $(".select").select2();
        });
    </script>
    <script>
        var url = window.location;
        $('ul.nav-sidebar a').filter(function() {
            return this.href == url;
        }).addClass('active');

        $('ul.nav-treeview a').filter(function() {
            return this.href == url;
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
    </script>
</body>

</html>