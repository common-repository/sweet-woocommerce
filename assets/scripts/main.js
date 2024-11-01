jQuery(document).ready(() => {
  jQuery('.sweet-menu-btn').click(function(){
    var tab = jQuery(this).data('tab');
    jQuery('.sweet-tab-item').removeClass('active');
    jQuery('.sweet-menu-btn').removeClass('active');
    jQuery(this).addClass('active');
    jQuery(tab).addClass('active');
  });
});
