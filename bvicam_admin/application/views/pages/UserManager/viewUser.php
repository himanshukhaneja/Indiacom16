<?php
/**
 * Created by PhpStorm.
 * User: Kisholoy
 * Date: 8/13/14
 * Time: 9:11 PM
 */
?>

<div class="col-sm-10 col-md-10" id="contentPanel">
    <h1 class="page-header">Manage User : <span
            class="h2 text-capitalize"><?php echo $userInfo->user_name; ?></span></h1>

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <form action="#" method="post">
                <div class="col-sm-8 col-sm-offset-4 text-danger h5">
                    <?php if (isset($pageError)) echo $pageError; ?>
                    <?php echo validation_errors(); ?>
                </div>
                <div class="col-md-12">
                    <table class="table table-condensed">
                        <tr>
                            <td id="selectOperationPlaceHolder">
                                <select name="role" class="form-control">
                                    <option value>Select Role</option>
                                    <?php
                                    foreach ($roles as $role) {
                                        ?>
                                        <option
                                            value="<?php echo $role->role_id; ?>"><?php echo $applications[$role->role_application_id] . " : " . $role->role_name; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <button type="submit" id="submitButton" class="btn btn-primary">Add Role</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="col-md-12">
                <table class="table table-responsive table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Application Name</th>
                        <th>Role Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($userRoles as $key => $userRole) {
                        ?>
                        <tr <?php if ($userRole->user_event_role_mapper_dirty) { ?> class="danger" <?php } ?>>
                            <td><?php echo $key; ?></td>
                            <td><?php echo $applications[$userRole->role_application_id]; ?></td>
                            <td>
                                <a href="../../RoleManager/viewRole/<?php echo $userRole->role_id; ?>"><?php echo $userRole->role_name; ?></a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php
                                    if ($userRole->user_event_role_mapper_dirty == 0) {
                                        ?>
                                        <a class="btn btn-sm btn-default"
                                           href="../disableUserRole/<?php echo $userInfo->user_id; ?>/<?php echo $userRole->role_id; ?>">Disable</a>
                                    <?php
                                    } else {
                                        ?>
                                        <a class="btn btn-sm btn-default"
                                           href="../enableUserRole/<?php echo $userInfo->user_id; ?>/<?php echo $userRole->role_id; ?>">Enable</a>
                                    <?php
                                    }
                                    ?>
                                    <a class="btn btn-sm btn-default"
                                       href="../deleteUserRole/<?php echo $userInfo->user_id; ?>/<?php echo $userRole->role_id; ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function () {
        $('#addEventRole').click(function () {
            var operationOptions = "";
            var entityOptions = "";
            <?php
            foreach($entities as $entity)
            {
            ?>
            entityOptions += "<option value=\"<?php echo $entity; ?>\"><?php echo $entity; ?></option>";
            <?php
            }
            ?>
            var html = "<select name=\"entity\" class=\"form-control entityyList\">" +
                "<option value>Select Entity</option>" +
                entityOptions +
                "</select>";
            var html2 = "<select name=\"operation\" class=\"form-control\">" +
                "<option value>Select Operation</option>" +
                "<option value=\"Select\">Select</option>" +
                "<option value=\"Update\">Update</option>" +
                "<option value=\"Insert\">Insert</option>" +
                "<option value=\"Delete\">Delete</option>" +
                "</select>";
            $('#selectEntityPlaceHolder').append(html);
            $('#selectOperationPlaceHolder').append(html2);
            $('#submitButton').css('display', 'initial');
            $(this).css('display', 'none');

            $('.entityyList').change(function () {
                var optionSelected = $(this).find("option:selected");
                $('#selectOperationPlaceHolder').empty();
                var html = "<select name=\"operation\" class=\"form-control\">" +
                    "<option value>Select Operation</option>" +
                    "<option value=\"Select\">Select</option>" +
                    "<option value=\"Update\">Update</option>" +
                    "<option value=\"Insert\">Insert</option>" +
                    "<option value=\"Delete\">Delete</option>" +
                    "</select>";
                $('#selectOperationPlaceHolder').append(html);
            });
        });
    });
</script>