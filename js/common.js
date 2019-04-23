$(function () {

  $(document).on('scroll', function () {
    if ($(this).scrollTop() > 200) {
      $('.f-header').addClass('offset')
    } else {
      $('.f-header').removeClass('offset')
    }
  });

  $('.summer--nav__item').click(function () {
    $(".summer--pic__item").removeClass("active").eq($(this).parent().index()).addClass("active");
  });

  $('.slides').slick({
    navs: false,
    dots: true,
  })


});
