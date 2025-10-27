require(["jquery",'mage/url','mage/calendar'], function($, url){

    $(function () { // same as $(document).ready()
        
    });
    window.fetchAddtierPrice = function (productId,productSku,productPrice,qty,groupId,storeId) {
        var BaseUrl = jQuery('.base-url').val();
        var wishlisturl = jQuery('.wishlisturl').val();
        url = BaseUrl+'addmyproducts/ajax/addmyproducts';
        var actionType = 'addtierprice';
       // alert("kkk")
 
       jQuery.ajax({
		url: url,
		dataType: 'json',
		global: false,
		type:        'POST',

		data:({'action': actionType, 'productId':productId,'productSku':productSku,'productPrice':productPrice,'qty':qty,'groupId':groupId,'storeId':storeId}),
        success:function(msg){
            window.location.href=wishlisturl;
        }

       });
        
    };

    window.fetchDeletetierPrice = function (productId,productSku,productPrice,qty,groupId,storeId,productUrl) {
        var BaseUrl = jQuery('.base-url').val();
        var wishlisturl = jQuery('.wishlisturl').val();
        url = BaseUrl+'addmyproducts/ajax/addmyproducts';
        var actionType = 'deletetierprice';
        jQuery.ajax({
		url: url,
		dataType: 'json',
		global: false,
		type:        'POST',

		data:({'action': actionType, 'productId':productId,'productSku':productSku,'productPrice':productPrice,'qty':qty,'groupId':groupId,'storeId':storeId}),
        success:function(msg){
            window.location.href=productUrl;
        }

       });

    }

    function addWItemToCart(itemId) { //for Add all to cart logic
        var url = jQuery('.addtocartUrl').val();
        url = url.gsub('%item%', itemId);
        var form = $('wishlist-view-form');
        if (form) {
            var input = form['qty[' + itemId + ']'];
            if (input) {
                var separator = (url.indexOf('?') >= 0) ? '&' : '?';
                url += separator + input.name + '=' + encodeURIComponent(input.value);
            }
        }
        setLocation(url);
    }
   
});
