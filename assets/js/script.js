// Car Rental System JavaScript

$(document).ready(function() {
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });

    // Form validation
    $('.needs-validation').on('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Date picker initialization
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date()
        });
    }

    // Booking form date calculation
    $('#pickup_date, #return_date').on('change', function() {
        calculateTotal();
    });

    // Vehicle selection change
    $('#vehicle_id').on('change', function() {
        calculateTotal();
    });

    // Calculate booking total
    function calculateTotal() {
        var pickupDate = $('#pickup_date').val();
        var returnDate = $('#return_date').val();
        var vehicleId = $('#vehicle_id').val();

        if (pickupDate && returnDate && vehicleId) {
            var start = new Date(pickupDate);
            var end = new Date(returnDate);
            var days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

            if (days > 0) {
                // Get vehicle daily rate via AJAX
                $.ajax({
                    url: 'ajax/get_vehicle_rate.php',
                    type: 'POST',
                    data: { vehicle_id: vehicleId },
                    success: function(response) {
                        var dailyRate = parseFloat(response);
                        var total = days * dailyRate;
                        $('#total_amount').val(total.toFixed(2));
                        $('#total_display').text('$' + total.toFixed(2));
                        $('#days_display').text(days + ' day(s)');
                    }
                });
            }
        }
    }

    // Search functionality
    $('#search_form').on('submit', function(e) {
        e.preventDefault();
        var searchTerm = $('#search_input').val();
        if (searchTerm.trim() !== '') {
            window.location.href = 'vehicles.php?search=' + encodeURIComponent(searchTerm);
        }
    });

    // Filter functionality
    $('.filter-option').on('change', function() {
        $('#filter_form').submit();
    });

    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });

    // Status change confirmation
    $('.status-change').on('change', function() {
        var newStatus = $(this).val();
        var itemId = $(this).data('id');
        var itemType = $(this).data('type');
        
        if (confirm('Are you sure you want to change the status to ' + newStatus + '?')) {
            $.ajax({
                url: 'ajax/update_status.php',
                type: 'POST',
                data: {
                    id: itemId,
                    type: itemType,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Status updated successfully!', 'success');
                    } else {
                        showAlert('Error updating status!', 'danger');
                    }
                }
            });
        } else {
            // Reset to previous value
            $(this).val($(this).find('option:selected').data('previous'));
        }
    });

    // Show alert function
    function showAlert(message, type) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                       message +
                       '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                       '</div>';
        $('.alert-container').html(alertHtml);
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Image preview for file uploads
    $('input[type="file"]').on('change', function() {
        var file = this.files[0];
        var reader = new FileReader();
        var preview = $(this).siblings('.image-preview');
        
        reader.onload = function(e) {
            preview.html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 200px;">');
        }
        
        if (file) {
            reader.readAsDataURL(file);
        }
    });

    // Password strength meter
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        var strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
        var strengthClass = ['danger', 'warning', 'info', 'success', 'success'];
        
        $('#password-strength').text(strengthText[strength - 1] || '');
        $('#password-strength').removeClass().addClass('text-' + (strengthClass[strength - 1] || 'muted'));
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Loading state for forms
    $('form').on('submit', function() {
        var submitBtn = $(this).find('button[type="submit"]');
        if (submitBtn.length) {
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="loading"></span> Processing...');
        }
    });

    // Responsive table
    $('.table-responsive').on('show.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "inherit" );
    });

    $('.table-responsive').on('hide.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "auto" );
    });

    // Back to top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });

    $('#back-to-top').click(function() {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });

    // Newsletter subscription
    $('#newsletter-form').on('submit', function(e) {
        e.preventDefault();
        var email = $('#newsletter-email').val();
        
        $.ajax({
            url: 'ajax/subscribe.php',
            type: 'POST',
            data: { email: email },
            success: function(response) {
                if (response.success) {
                    showAlert('Thank you for subscribing!', 'success');
                    $('#newsletter-email').val('');
                } else {
                    showAlert(response.message, 'danger');
                }
            }
        });
    });

    // Contact form submission
    $('#contact-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/contact.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('Message sent successfully! We will get back to you soon.', 'success');
                    $('#contact-form')[0].reset();
                } else {
                    showAlert(response.message, 'danger');
                }
            }
        });
    });

    // Vehicle availability check
    $('.check-availability').on('click', function() {
        var vehicleId = $(this).data('vehicle-id');
        var pickupDate = $('#pickup_date').val();
        var returnDate = $('#return_date').val();
        
        if (pickupDate && returnDate) {
            $.ajax({
                url: 'ajax/check_availability.php',
                type: 'POST',
                data: {
                    vehicle_id: vehicleId,
                    pickup_date: pickupDate,
                    return_date: returnDate
                },
                success: function(response) {
                    if (response.available) {
                        showAlert('Vehicle is available for selected dates!', 'success');
                    } else {
                        showAlert('Vehicle is not available for selected dates.', 'warning');
                    }
                }
            });
        } else {
            showAlert('Please select pickup and return dates first.', 'warning');
        }
    });

});

// Utility functions
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString();
}

function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    var re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone);
}
