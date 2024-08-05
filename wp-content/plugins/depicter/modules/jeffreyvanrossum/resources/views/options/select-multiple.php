<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo $option->get_id_attribute(); ?>"><?php echo $option->get_label(); ?></label>
    </th>
    <td class="forminp forminp-text">
        <select id="<?php echo $option->get_id_attribute(); ?>" name="<?php echo esc_attr($option->get_name_attribute()); ?>" multiple>
            <?php foreach ($option->args['options'] as $key => $label) { ?>
                <option value="<?php echo $key; ?>" <?php echo in_array($key, $option->get_value_attribute()) ? 'selected' : null; ?>><?php echo $label; ?></option>
            <?php } ?>
        </select>
        <?php if($description = $option->get_arg('description')) { ?>
            <p class="description"><?php echo $description; ?></p>
        <?php } ?>

        <?php if($error = $option->has_error()) { ?>
            <div class="wps-error-feedback"><?php echo $error; ?></div>
        <?php } ?>
    </td>
</tr>
