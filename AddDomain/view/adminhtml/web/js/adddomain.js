require([
    "jquery"
], function ($) {
    "use strict";

    //console.log("Ime Custom JS Loaded!");

    $(document).on("click", ".add_more_button", function () {
       // alert("Button clicked!");
        jQuery(this).closest('tr').before('<tr class="wk-row-view"> <td><input type="text" value="" name="domain[]"/></td><td class=""><button class="btn delete-fld" type="button">Delete</button></td></tr> ');
    });

     $(document).on("click", ".delete-fld", function () {
        $(this).parent().prev().remove();
         $(this).parent().remove();
     });
});
