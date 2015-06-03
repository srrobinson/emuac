<div id="form_container">
    <form action="index.php" method="post">
        <a href="javascript:void(0);" id="add_another_line">Add line item</a>

        <div id="form_part_1">
            <div id="form_copy_fields">
                <p>
                    <label for="first_name" id="first_name_label">First Name: </label>
                    <input id="first_name" type="text" name="first_name[]" class="input_field_slarge"
                           autofocus="autofocus"/>
                    &nbsp;&nbsp;
                    <label for="last_name" id="last_name_label">Last Name: </label>
                    <input id="last_name" type="text" name="last_name[]" class="input_field_slarge"/>
                    &nbsp; &nbsp;
                    <label for="username" id="username_label">Username: </label>
                    <input id="username" type="text" name="username[]" class="input_field_slarge"/>
                </p>
            </div>
        </div>


        <div id="form_part_2">
            <p>
                <label for="base_ou" id="base_ou_label">Where are these users going?: </label>
                <input id="base_ou" type="text" name="base_ou" class="input_field_large"/>
            </p>


            <p>
                <label for="group_list" id="group_list_label">Groups to add user(s) to (comma
                    separated): </label> <br/>
                <textarea id="group_list" name="group_list" class="input_field_xlarge" rows="5"></textarea>

            </p>

            <p>
                <label for="enable_email" id="enable_email_label">Send an Email: </label>
                <input id="enable_email" name="enable_email" type="checkbox"/>
            </p>

            <p>
                <label for="email_text" id="email_text_label" class="hidden">Email Text: </label> <br/>
                <textarea id="email_text" name="email_text" rows=10 class="hidden input_field_xlarge"></textarea>

            </p>


            <p>
                <input type="submit" name="create_button" value="Create User(s)"/>
                <input type="reset" name="clear_button" value="Clear"/>
            </p>

        </div>

    </form>
</div>