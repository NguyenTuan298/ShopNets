$(document).ready(function() {
  $('.field').each(function() {
    $(this).append('<div class="error-message"></div>');
  });

  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function validateField(field, value, type) {
    const $field = $(field).closest('.field');
    const $errorMsg = $field.find('.error-message');
    
    $field.removeClass('error success');
    $errorMsg.hide();

    if (type === 'email') {
      if (!value) {
        $field.addClass('error');
        $errorMsg.text('Email is required').show();
        return false;
      } else if (!validateEmail(value)) {
        $field.addClass('error');
        $errorMsg.text('Invalid email').show();
        return false;
      } else {
        $field.addClass('success');
        return true;
      }
    } else if (type === 'password') {
      if (!value) {
        $field.addClass('error');
        $errorMsg.text('Password is required').show();
        return false;
      } else if (value.length < 6) {
        $field.addClass('error');
        $errorMsg.text('Password must be at least 6 characters').show();
        return false;
      } else {
        $field.addClass('success');
        return true;
      }
    }
    return true;
  }

  function handleLoginError(message) {
    $('#loadingOverlay').hide();
    
    $('.btn-primary').prop('disabled', false).text('Login');
    
    $('.login-error').remove();
    
    const errorHtml = '<div class="login-error">' + message + '</div>';
    $('.brand').after(errorHtml);
    
    setTimeout(function() {
      $('.login-error').fadeOut(function() {
        $(this).remove();
      });
    }, 5000);

    $('#password').val('');
    $('#password').closest('.field').removeClass('success error');
    $('#password').closest('.field').find('.error-message').hide();
    
    $('#email').focus();
  }

  function performLogin(email, password) {
    const startTime = Date.now();
    const minLoadingTime = 1500; 

    $.ajax({
      url: 'login_action.php?api=1',
      method: 'POST',
      dataType: 'json',
      data: {
        email: email,
        password: password
      },
      success: function(response) {
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
        
        setTimeout(function() {
          if (response.success) {
            $('#loadingText').text('Login successful!');
            
            setTimeout(function() {
              window.location.href = '../index.php';
            }, 1000);
          } else {
            handleLoginError(response.error || 'Login failed!');
          }
        }, remainingTime);
      },
      error: function(xhr, status, error) {
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, minLoadingTime - elapsedTime);
        
        console.log('AJAX Error:', xhr, status, error);
        
        let errorMessage = 'An error occurred while logging in. Please try again.';
        
        setTimeout(function() {
          $('#loadingText').text('Login failed!');
          $('#loadingSubtext').text('Processing error...');
          
          setTimeout(function() {
            handleLoginError(errorMessage);
          }, 500);
        }, remainingTime);
      },
      timeout: 10000 
    });
  }

  $('#email').on('blur keyup', function() {
    validateField(this, $(this).val(), 'email');
  });

  $('#password').on('blur keyup', function() {
    validateField(this, $(this).val(), 'password');
  });

  $('.form').on('submit', function(e) {
    e.preventDefault();

    const email = $('#email').val().trim();
    const password = $('#password').val();

    const emailValid = validateField('#email', email, 'email');
    const passwordValid = validateField('#password', password, 'password');

    if (!emailValid || !passwordValid) {
      return false;
    }

    $('#loadingOverlay').css('display', 'flex');
    $('.btn-primary').prop('disabled', true).text('Logging in...');

    $('#loadingText').text('Logging in...');
    $('#loadingSubtext').text('Please wait...');

    performLogin(email, password);
  });

  $('.form input').on('keypress', function(e) {
    if (e.which === 13) {
      $('.form').submit();
    }
  });

  $('#email').focus();
});