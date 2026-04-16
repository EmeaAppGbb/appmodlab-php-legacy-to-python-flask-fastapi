// CityPulse Events - Legacy JavaScript with jQuery

$(document).ready(function() {
    console.log('CityPulse Events loaded');
    
    // Simple form validation (minimal)
    $('form').on('submit', function(e) {
        var valid = true;
        
        $(this).find('input[required], textarea[required], select[required]').each(function() {
            if ($(this).val() === '') {
                valid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '#ddd');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });
    
    // Auto-hide messages after 5 seconds
    setTimeout(function() {
        $('.message').fadeOut();
    }, 5000);
    
    // Confirm delete actions
    $('a[onclick*="confirm"]').on('click', function(e) {
        if (!confirm('Are you sure?')) {
            e.preventDefault();
        }
    });
});

// Global functions (bad practice - polluting global namespace)
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
