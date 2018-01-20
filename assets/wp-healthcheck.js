jQuery(document).ready(function($) {
  /**
   * Initiliaze the metaboxes.
   */
  postboxes.add_postbox_toggles(pagenow);

  /**
   * Internal functions.
   */
  function toggle_autoload_list(action) {
    var list = true;
    var history = false;

    if (action.match(/history/)) {
      list = false;
      history = true;
      action = 'wphc_autoload_history';
    } else {
      action = 'wphc_autoload_list';
    }

    if (!$('#wphc-autoload-list').is(':visible')) {
      $('#wphc-autoload-list').empty();
      $('#wphc-autoload-list').show();
    }

    wphc_do_ajax(action, null, 'wphc-autoload-list');

    $('#wphc-btn-autoload-history').prop('disabled', history);
    $('#wphc-btn-autoload-list').prop('disabled', list);
  }

  /**
   * Add the onclick actions.
   */
  $(document)
  .on('click', '#wphc-btn-transients-all', function() {
    wphc_do_ajax('wphc_transients_cleanup', null, 'wphc-transients-stats');
  })
  .on('click', '#wphc-btn-transients-expired', function() {
    wphc_do_ajax('wphc_transients_cleanup', {'expired': '1'}, 'wphc-transients-stats');
  })
  .on('click', '#wphc-btn-transients-object', function() {
    wphc_do_ajax('wphc_transients_cleanup', {'object_cache': '1'}, 'wphc-transients-stats');
  })
  .on('click', '#wphc-btn-autoload-list', function() {
    toggle_autoload_list('list');
  })
  .on('click', '#wphc-btn-autoload-history', function() {
    toggle_autoload_list('history');
  })
  .on('click', '#wphc-btn-autoload-close', function() {
    $('#wphc-autoload-list').hide();

    $('#wphc-btn-autoload-history').prop('disabled', false);
    $('#wphc-btn-autoload-list').prop('disabled', false);
  })
  .on('click', '.wphc-notice .notice-dismiss', function() {
    var classes = $(this).closest('.wphc-notice').attr('class');
    var software = classes.match(/wphc-notice-(?:php|database|wordpress|web|ssl)\s/)[0].replace('wphc-notice-', '');

    wphc_do_ajax('wphc_hide_admin_notice', {'software': software}, false);
  });

  /**
   * Add the onsubmit actions.
   */
  $(document)
  .on('submit', '#wphc-autoload-form', function() {
    var data = $('#wphc-autoload-form').serializeArray();

    var params = {};

    $(data).each(function(i, field) {
      params[field.name] = field.value;
    });

    var action = ($('#wphc-history').length) ? 'wphc_autoload_reactivate' : 'wphc_autoload_deactivate';

    wphc_do_ajax(action, params, 'wphc-autoload-list');

    $('#wphc-btn-autoload-list').prop('disabled', false);
    $('#wphc-btn-autoload-history').prop('disabled', false);

    return false;
  });

  /**
   * Hide some elements.
   */
  if ($('#wphc-autoload-list').length) {
    //$('#wphc-autoload-list').hide();
  }
});

/**
 * Run the AJAX requests.
 *
 * @param {string} action
 * @param {Object} params
 * @param {string} target
 */
function wphc_do_ajax(action, params, target) {
  var data = {
    'action': action
  };

  if (jQuery('#' + action + '_wpnonce').length) {
    _wpnonce = jQuery('#' + action + '_wpnonce').val();

    data = jQuery.extend(data, {'_wpnonce': _wpnonce});
  }

  if (typeof params === 'object') {
    data = jQuery.extend(data, params);
  }

  if (typeof target === 'string') {
    target = '#' + target;

    if (jQuery(target).length) {
      target = jQuery(target);
    } else {
      target = false;
    }
  }

  jQuery.ajax({
    method: 'POST',
    url: ajaxurl,
    data: data,
    beforeSend: function() {
      if (target) {
        target.html('<p class="wphc_loading"><img src="/wp-admin/images/loading.gif" /> Loading...</p>');
      }
    }
  })
  .done(function(response) {
    if (target) {
      target.html(response);
    }
  })
  .fail(function() {
    window.alert('fail');
  });
}

/**
 * Create an WordPress admin pointer.
 *
 * @param {string} title
 * @param {string} content
 * @param {string} target
 */
function wphc_do_pointer(title, content, target) {
  jQuery('#' + target).pointer({
    content: '<h3>' + title + '</h3><p>' + content + '</p>',
    position: {
      edge: 'left',
      align: 'middle'
    }
  });

  jQuery('#' + target).on('click', function() { jQuery('#' + target).pointer('open'); });
}
