<?php
$title = 'index';

ob_start();
?>
    <table id="table_id" class="display col-7">
        <caption>users</caption>
        <thead>
        <tr>
            <th>user id</th>
            <th>first name</th>
            <th>last name</th>
            <th>birthdate</th>
            <th>height</th>
            <th>club_member</th>
            <th>actions</th>
        </tr>
        </thead>
    </table>


    <script>
        $(document).ready(function ()
        {
            let table = $('#table_id').DataTable(
                {
                'serverSide': true,
                'processing': true,
                ajax: {
                    url: '/datatables/users',
                    type: 'POST',
                    data: function (d)
                    {
                        d.form_id = '<?= $data['form_id']; ?>';
                        d.form_token = '<?= $data['form_token']; ?>';
                    },

                },
                columns: [
                    {"data": 'id'},
                    {"data": 'first_name'},
                    {"data": 'last_name'},
                    {"data": 'birthdate'},
                    {"data": 'height'},
                    {"data": 'club_member'},
                ],

                "columnDefs": [{
                    "targets": 6,
                    "data": "actions",
                    "render": function (data, type, row, meta)
                    {
                        return '' +
                            '<div class="d-grid gap-2 d-md-flex">' +
                            '<a href="/users/show/' + row.id + '" type="button" class="btn btn-outline-primary btn-sm ">show</a>' +
                            '<a href="/users/edit/' + row.id + '" type="button" class="btn btn-outline-warning btn-sm ">edit</a>' +
                            '<form method="post" action="/users/delete/' + row.id + '">' +
                                '<input type="hidden" id="form_token" name="form_token" value="<?= $data['form_token']; ?>" class="form-control">' +
                                '<input type="hidden" id="form_id" name="form_id" value="<?= $data['form_id']; ?>" class="form-control">' +
                                '<button type="submit" id="delete_user_' + row.id + '" class="btn btn-outline-danger btn-sm ">delete</button>' +
                            '</form>' +
                            '</div>'
                    }
                }],
            });
        });
    </script>

<?php
$output = ob_get_contents();
ob_end_clean();
