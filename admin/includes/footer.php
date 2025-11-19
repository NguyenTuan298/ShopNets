  </main>

  <?php include 'popup_logout.php'; ?>
  
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/js/main.js"></script>
  <script src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/js/logout.js"></script>
  
  <script>
    window.adminBaseUrl = '<?php echo isset($baseUrl) ? $baseUrl : ''; ?>';
  </script>
</body>
</html>
