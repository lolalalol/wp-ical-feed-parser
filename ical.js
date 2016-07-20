jQuery(document).ready(function(){
  jQuery('.icalfeed').each(function(e){
    jQuery(this).data('ncol', Math.round(jQuery('li', this).length / 8));
    var w = jQuery('li:first', this).outerWidth();
    jQuery('ul', this).css('width', w*jQuery(this).data('ncol'));
    var pages = '';
    for(var i = 0; i < jQuery(this).data('ncol'); i++) 
    {
      pages += '<span data-idx="'+i+'">&#x25c9;</span>';
    }
    jQuery(this).append('<div class="pager">'+pages+'</div>');
    jQuery('.pager span', this).click(function(e){
      var idx = jQuery(this).data('idx');
      var $ul = jQuery(this).parent().parent().find('ul');
      jQuery('.active', jQuery($ul).parent()).toggleClass('active');
      jQuery(this).addClass('active');
      jQuery($ul).animate({'left': '-'+(w*idx)+'px'});
    }).eq(0).trigger('click');
  });
});
