$(function () {

  $(document).on('scroll', function () {
    if ($(this).scrollTop() > 200) {
      $('.f-header').addClass('offset')
    } else {
      $('.f-header').removeClass('offset')
    }
  });

  

});
