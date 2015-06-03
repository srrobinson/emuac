/** (c) 2015 srrobinson
 * Created with assistance from these snippets:
 * http://jsfiddle.net/wc28f/321/
 * http://www.sanwebe.com/2013/03/addremove-input-fields-dynamically-with-jquery
 *
 * **/

$(document).ready(function () {

    var field_index = 0;

    //every time someone clicks add line item, increase field index.
    $('#add_another_line').on('click', function () {
        field_index++;

        //clone all fields within the #form_copy_fields div and stick it inside the #form_part_1 div
        $("#form_copy_fields").clone().attr("id", "form_copy_fields_" + field_index).appendTo('#form_part_1');

        //append a number onto each fields ID value for uniqueness
        //$("#form_copy_fields" + field_index).css("display","inline");
        $("#form_copy_fields_" + field_index + " :input").each(function () {
            $(this).attr("id", $(this).attr("id") + field_index);
            //clear the field value in case it has a value!
            $(this).val("");
        });
        //append the remove link dynamically and append an on click listener for removal requests (decrease index too)
        $("#form_copy_fields_" + field_index).find('p').
            append('&nbsp;<a href="javascript:void(0);" class="remove_field">Remove</a>')
            .on('click', ".remove_field", function () {
                $("#form_copy_fields_" + field_index).remove();
                field_index--;
            });

    });

    //un-hide email field if the check box
    $('#enable_email').change(function () {
        $('#email_text_label').toggle();
        $('#email_text').toggle().val('');

    });



    $(".go_back_link").on('click', function (e) {
        e.preventDefault();
        window.history.back();
    });


});
