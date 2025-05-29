
jQuery(document).ready(function($) {
    // Single product page validation
    if (typeof wcMaxQuantityData !== 'undefined') {
        var maxQuantity = wcMaxQuantityData.maxQuantity;
        var currentCartQuantity = wcMaxQuantityData.currentCartQuantity;
        var errorMessage = wcMaxQuantityData.errorMessage;
        
        function validateQuantity() {
            var quantityInput = $('input[name="quantity"]');
            var addToCartForm = $('form.cart');
            var addToCartButton = addToCartForm.find('button[type="submit"], input[type="submit"]');
            
            if (quantityInput.length === 0) {
                return true;
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
    }
    
    // Shop/archive pages validation
    if (typeof wcMaxQuantityShopData !== 'undefined') {
        function validateShopQuantity(productId, quantityInput, addToCartButton) {
            if (!wcMaxQuantityShopData[productId]) {
                return true;
            }
            
            var data = wcMaxQuantityShopData[productId];
            var requestedQuantity = parseInt(quantityInput.val()) || 1;
            var totalQuantity = data.currentCartQuantity + requestedQuantity;
            
            // Remove any existing error notices for this product
            quantityInput.closest('.product').find('.woocommerce-error').remove();
            
            if (totalQuantity > data.maxQuantity) {
                // Show error message
                var noticeHtml = '<div class="woocommerce-error" role="alert" style="margin: 10px 0;">' + data.errorMessage + '</div>';
                quantityInput.closest('form').after(noticeHtml);
                
                // Disable the add to cart button
                addToCartButton.prop('disabled', true);
                
                return false;
            } else {
                // Re-enable the button if quantity is valid
                addToCartButton.prop('disabled', false);
                return true;
            }
        }
        
        // Handle quantity changes on shop pages
        $(document).on('change input', '.products input[name="quantity"]', function() {
            var quantityInput = $(this);
            var form = quantityInput.closest('form');
            var addToCartButton = form.find('button[type="submit"], input[type="submit"]');
            var productId = parseInt(form.find('input[name="add-to-cart"]').val() || form.find('button[name="add-to-cart"]').val());
            
            setTimeout(function() {
                validateShopQuantity(productId, quantityInput, addToCartButton);
            }, 100);
        });
        
        // Handle form submission on shop pages
        $(document).on('submit', '.products form.cart', function(e) {
            var form = $(this);
            var quantityInput = form.find('input[name="quantity"]');
            var addToCartButton = form.find('button[type="submit"], input[type="submit"]');
            var productId = parseInt(form.find('input[name="add-to-cart"]').val() || form.find('button[name="add-to-cart"]').val());
            
            if (quantityInput.length > 0 && !validateShopQuantity(productId, quantityInput, addToCartButton)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Handle add to cart button clicks on shop pages
        $(document).on('click', '.products button[name="add-to-cart"], .products input[name="add-to-cart"]', function(e) {
            var button = $(this);
            var form = button.closest('form');
            var quantityInput = form.find('input[name="quantity"]');
            var productId = parseInt(button.val());
            
            if (quantityInput.length > 0 && !validateShopQuantity(productId, quantityInput, button)) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Cart page checkout prevention
    $(document).on('click', '.checkout-button, a[href*="checkout"]', function(e) {
        // Check if any cart quantities exceed limits
        var hasErrors = $('.woocommerce-error').length > 0;
        if (hasErrors) {
            e.preventDefault();
            alert('Please adjust cart quantities before proceeding to checkout.');
            return false;
        }
    });
});
