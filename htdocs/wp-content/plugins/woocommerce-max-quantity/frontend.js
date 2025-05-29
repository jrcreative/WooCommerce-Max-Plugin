
jQuery(document).ready(function($) {
    if (typeof wcMaxQuantityData === 'undefined') {
        return;
    }
    
    var maxQuantity = wcMaxQuantityData.maxQuantity;
    var currentCartQuantity = wcMaxQuantityData.currentCartQuantity;
    var errorMessage = wcMaxQuantityData.errorMessage;
    
    function validateQuantity() {
        var quantityInput = $('input[name="quantity"]');
        var addToCartForm = $('form.cart');
        var addToCartButton = addToCartForm.find('button[type="submit"], input[type="submit"]');
        
        if (quantityInput.length === 0) {
            return;
        }
        
        var requestedQuantity = parseInt(quantityInput.val()) || 1;
        var totalQuantity = currentCartQuantity + requestedQuantity;
        
        // Clear any existing notices
        $('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
        
        if (totalQuantity > maxQuantity) {
            // Show error message
            var noticeHtml = '<div class="woocommerce-error" role="alert">' + errorMessage + '</div>';
            addToCartForm.before(noticeHtml);
            
            // Disable the add to cart button temporarily
            addToCartButton.prop('disabled', true);
            
            // Scroll to the error message
            $('html, body').animate({
                scrollTop: $('.woocommerce-error').offset().top - 100
            }, 500);
            
            return false;
        } else {
            // Re-enable the button if quantity is valid
            addToCartButton.prop('disabled', false);
            return true;
        }
    }
    
    // Validate on quantity input change
    $(document).on('change input', 'input[name="quantity"]', function() {
        setTimeout(validateQuantity, 100);
    });
    
    // Prevent form submission if quantity exceeds limit
    $(document).on('submit', 'form.cart', function(e) {
        if (!validateQuantity()) {
            e.preventDefault();
            return false;
        }
    });
    
    // Also prevent click on add to cart button
    $(document).on('click', 'form.cart button[type="submit"], form.cart input[type="submit"]', function(e) {
        if (!validateQuantity()) {
            e.preventDefault();
            return false;
        }
    });
    
    // Initial validation on page load
    validateQuantity();
});
