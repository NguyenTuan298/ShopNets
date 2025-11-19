function handleLogout(event) {
  event.preventDefault();
  $('#logoutModal').css('display', 'flex');
}

function cancelLogout() {
  $('#logoutModal').hide();
}

function confirmLogout() {
  $('#logoutModal').find('h3').text('Logging out...');
  $('#logoutModal').find('p').text('Please wait...');
  $('#logoutModal').find('button').prop('disabled', true);

  const baseUrl = window.adminBaseUrl || '';

  $.ajax({
    url: baseUrl + 'login/logout.php?api=1',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        $('#logoutModal').find('h3').text('Log out successful!');
        
        setTimeout(function() {
          window.location.href = baseUrl + 'login/login.php?message=Log+out+successful';
        }, 1000);
      } else {
        handleLogoutError();
      }
    },
    error: function() {
      handleLogoutError();
    }
  });
}

function handleLogoutError() {
  $('#logoutModal').find('h3').text('Có lỗi xảy ra!');
  $('#logoutModal').find('p').text('Không thể đăng xuất. Đang chuyển hướng...');
  
  const baseUrl = window.adminBaseUrl || '';
  
  setTimeout(function() {
    window.location.href = baseUrl + 'login/logout.php';
  }, 1500);
}

$(document).ready(function() {
  window.adminBaseUrl = window.adminBaseUrl || '';
  
  // Only handle logout for specific logout buttons
  $('#logoutBtn').on('click', function(event) {
    handleLogout(event);
  });

  $('#logoutBtn').hover(
    function() {
      $(this).css('background-color', '#dc3545');
      $(this).css('color', 'white');
    },
    function() {
      $(this).css('background-color', '');
      $(this).css('color', '');
    }
  );
  
  $(document).on('keydown', function(e) {
    if (e.keyCode === 27) { // ESC key
      cancelLogout();
    }
  });
  
  $('#logoutModal').on('click', function(e) {
    if (e.target === this) {
      cancelLogout();
    }
  });
});