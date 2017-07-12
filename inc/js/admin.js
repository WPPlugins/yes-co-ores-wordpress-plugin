jQuery(document).ready( function($)
{
  /**
  * Toggle settings
  */
  jQuery('.yog-toggle-setting').click(function()
  {
    var msgHolder = jQuery('.msg', jQuery(this).parent());
    msgHolder.addClass('hide');

	  jQuery.post(ajaxurl, {'action': 'setsetting', 'cookie': encodeURIComponent(document.cookie), 'name' : this.name},
		  function(msg)
		  {
        msgHolder.html(msg);
        msgHolder.removeClass('hide');
        setTimeout(function(){ msgHolder.addClass('hide') }, 5000);
		  });
  });
  
  /**
  * Set settings
  */
  jQuery('.yog-set-setting').change(function()
  {
    var msgHolder = jQuery('.msg', jQuery(this).parent());
    msgHolder.addClass('hide');

	  jQuery.post(ajaxurl, {'action': 'setsetting', 'cookie': encodeURIComponent(document.cookie), 'name' : this.name, 'value': this.value},
		  function(msg)
		  {
        msgHolder.html(msg);
        msgHolder.removeClass('hide');
        setTimeout(function(){ msgHolder.addClass('hide') }, 5000);
		  });
  });
  
  /**
   * Show / hide order when yog-toggle-cat-custom not checked
   */
  jQuery('#yog-toggle-cat-custom').change(function()
  {
    if (this.checked)
    {
      jQuery('#yog-sortoptions').show();
    }
    else
    {
      jQuery('#yog-sortoptions').hide();
      jQuery('#yog_order').val('');
    }
  });

  /**
  * Add system link
  */
  jQuery('#yog-add-system-link').click(function()
  {
	  jQuery('#yog-add-system-link').hide();
	  jQuery('#yog-add-system-link-holder').addClass('loading');
    jQuery('#yog-add-system-link-holder').addClass('loading-padding');

    var secret  = jQuery('#yog-new-secret').val();

	  jQuery.post(ajaxurl, {'action': 'addkoppeling', 'activatiecode':secret, 'cookie': encodeURIComponent(document.cookie)},
		  function(html)
		  {
			  jQuery('#yog-system-links').append(html);
        jQuery('#yog-add-system-link-holder').removeClass('loading');
        jQuery('#yog-add-system-link-holder').removeClass('loading-padding');
        jQuery('#yog-new-secret').val('');
			  jQuery('#yog-add-system-link').show();
		  });
  });
});

/**
* Remove system link
*/
function yogRemoveSystemLink(secret)
{
  jQuery('#yog-system-link-' + secret + '-remove span').hide()
	jQuery('#yog-system-link-' + secret + '-remove').addClass('loading');
  jQuery('#yog-system-link-' + secret + '-remove').addClass('loading-padding');

	jQuery.post(ajaxurl, {action:"removekoppeling", 'activatiecode':secret, 'cookie': encodeURIComponent(document.cookie)},
		function(secret)
		{
      jQuery('#yog-system-link-' + secret).fadeOut();
      jQuery('#yog-system-link-' + secret).remove();
		});
}

/**
* Activate NB admin menu
*/
var yogActivateNbAdminMenu = function ()
{
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  var wpBodyContent = jQuery('#wpbody-content');

  if (mainMenuItem.length > 0)
  {
	var nbMenuLink    = jQuery('li a[href="edit.php?post_type=yog-nbpr"]', mainMenuItem);
    var nbMenuItem    = nbMenuLink.parent();

    if (nbMenuItem.length > 0 && nbMenuLink.length > 0)
    {
      nbMenuItem.addClass('current');
      nbMenuLink.addClass('current');
    }
  }

  if (wpBodyContent.length > 0)
  {
    var scenario = jQuery('#yog_scenario');
    if (scenario.length > 0)
      wpBodyContent.addClass('yog-' + scenario.attr('value'));
  }
}

/**
* Activate BBpr admin menu
*/
var yogActivateComplexAdminMenu = function ()
{
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  var wpBodyContent = jQuery('#wpbody-content');

  if (mainMenuItem.length > 0)
  {
	var nbMenuLink    = jQuery('li a[href="edit.php?post_type=yog-bbpr"]', mainMenuItem);
    var nbMenuItem    = nbMenuLink.parent();

    if (nbMenuItem.length > 0 && nbMenuLink.length > 0)
    {
      nbMenuItem.addClass('current');
      nbMenuLink.addClass('current');
    }
  }

  if (wpBodyContent.length > 0)
  {
    var scenario = jQuery('#yog_scenario');
    if (scenario.length > 0)
      wpBodyContent.addClass('yog-' + scenario.attr('value'));
  }
}