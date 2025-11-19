$(document).ready(function () {
  $(".add-to-cart").on("click", function () {
    var productId = $(this).data("id");
    $.ajax({
      url: "ajax/add_to_cart.php",
      type: "post",
      data: { product_id: productId },
      success: function (response) {
        alert("Đã thêm vào giỏ hàng");
      },
    });
  });
});
