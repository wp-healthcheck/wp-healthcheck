<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$opts = WP_Healthcheck::get_autoload_options();
?>

<form id="wphc-autoload-form" name="wphc-autoload-form">
  <div class="wphc_autoload_list">
    <ul>
      <li></li>
      <li><?php _e( 'Name', 'wp-healthcheck' ); ?></li>
      <li><?php _e( 'Size', 'wp-healthcheck' ); ?></li>
    </ul>
    <?php
    $i = 1;

    foreach ( $opts as $name => $size ) :
      $title = '';
      $id = 'wphc-opt-' . urlencode( $name );
    ?>
      <ul>
        <?php if ( ! WP_Healthcheck::is_core_option( $name ) ) : ?>
          <li><input name="<?php echo $id; ?>" id="<?php echo $id; ?>" type="checkbox" />&nbsp;</li>
        <?php else : ?>
          <?php
          $title = 'title="' . __( 'You can\'t deactivate a WordPress core option.', 'wp-healthcheck' ) . '"';
          ?>
          <li><input type="checkbox" disabled="disabled" <?php echo $title; ?> /></li>
        <?php endif; ?>
        <li class="wphc-col-name"><label for="<?php echo $id; ?>" <?php echo $title; ?>><?php echo $name; ?></label></li>
        <li><label for="<?php echo $id; ?>" <?php echo $title; ?>><?php echo number_format( $size, 2 ); ?>MB</label></li>
      </ul>
    <?php
      $i++;
    endforeach;
    ?>
  </div>
</form>

<div>
  <button type="submit" class="button button-small button-primary" form="wphc-autoload-form"><?php _e( 'Deactivate Selected Options', 'wp-healthcheck' ); ?></button>

  <button type="submit" class="button button-small" id="wphc-btn-autoload-close"><?php _e( 'Close', 'wp-healthcheck' ); ?></button></div>
</div>
