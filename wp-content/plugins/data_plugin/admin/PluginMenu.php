<div class="wrap">
    <h1><?php esc_html_e('Plugin to API', 'data_plugin'); ?></h1>

    <?php
    // Debug message
    echo '<p>' . esc_html__('TEST MENU SYSTEM, PLACE FORM HERE', 'data_plugin') . '</p>';
    ?>

    <form method="post" action="">
        <label for="name"><?php esc_html_e('API name:', 'data_plugin'); ?></label>
        <input type="text" name="name" required>
        <br>

        <label for="ID"><?php esc_html_e('Unique ID:', 'data_plugin'); ?></label>
        <input type="text" name="ID" required>
        <br>

        <label for="url"><?php esc_html_e('Base URL:', 'data_plugin'); ?></label>
        <input type="url" name="url" required>
        <br>

        <?php submit_button(__('Submit', 'data_plugin')); ?>
    </form>
</div>
