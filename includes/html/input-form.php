<div id="form_container">
    <form action="index.php" method="post">
        <a href="javascript:void(0);" id="add_another_line">Add line item</a>

        <div id="form_part_1">
            <div id="form_copy_fields">
                <p>
                    <label for="firstName" id="first_name_label">First Name: </label>
                    <input id="firstName" type="text" name="firstName[]" class="input_field_slarge"/>

                    <label for="lastName" id="last_name_label">Last Name: </label>
                    <input id="lastName" type="text" name="lastName[]" class="input_field_slarge"/>
                </p>
            </div>
        </div>


        <div id="form_part_2">
            <p>
                <label for="baseOU" id="baseOU_label">Where are these users going?: </label>
                <input id="baseOU" type="text" name="baseOU" class="input_field_large"/>
            </p>


            <p>
                <label for="groupList" id="groupList_label" class="hidden">Groups to add user(s) to (comma
                    separated): </label> <br/>
                <textarea id="groupList" name="groupList" class="input_field_xlarge" rows="5"></textarea>

            </p>


            <p>
                <input type="submit" name="create_button" value="Create User(s)"/>
                <input type="reset" name="clear_button" value="Clear"/>
            </p>

        </div>

    </form>
</div>