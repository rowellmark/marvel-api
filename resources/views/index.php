<div class="wrap">
    <h2>Marvel API Plugin Settings</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('marvel_api_settings');
        do_settings_sections('marvel-api-settings');
        submit_button();
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Marvel API Public Key</th>
                <td><input type="text" name="marvel_api_public_key" value="<?php echo esc_attr(get_option('marvel_api_public_key')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Marvel API Private Key</th>
                <td><input type="text" name="marvel_api_private_key" value="<?php echo esc_attr(get_option('marvel_api_private_key')); ?>" /></td>
            </tr>
        </table>


    </form>
</div>