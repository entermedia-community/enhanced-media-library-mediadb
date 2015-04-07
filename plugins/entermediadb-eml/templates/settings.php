<div class="wrap">
    <h2>EnterMedia DB Settings</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('emdb-publish'); ?>
        <?php @do_settings_fields('emdb-publish'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="emdb_entermediakey">Access Key</label></th>
                <td><input type="text" name="emdb_entermediakey" id="emdb_entermediakey" value="<?php echo get_option('emdb_entermediakey'); ?>" /><span style="padding-left:5em;">corresponds to the Access Key in your EnterMedia Wordpress publish destination</span></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="emdb_mediadbappid">MediaDB Application ID</label></th>
                <td><input type="text" name="emdb_mediadbappid" id="emdb_mediadbappid" value="<?php echo get_option('emdb_mediadbappid'); ?>" /><span style="padding-left:5em;">corresponds to catalogsettings/mediadbappid</span> </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="emdb_cdn_prefix">CDN Prefix</label></th>
                <td><input type="text" name="emdb_cdn_prefix" id="emdb_cdn_prefix" value="<?php echo get_option('emdb_cdn_prefix'); ?>" /><span style="padding-left:5em;">corresponds to catalogsettings/cdn_prefix (set to FQDN of your EnterMedia server if unused)</span></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>
