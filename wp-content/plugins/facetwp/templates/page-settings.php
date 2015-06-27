<?php

global $wpdb;

// An array of facet type objects
$facet_types = FWP()->helper->facet_types;

// Get taxonomy list
$taxonomies = get_taxonomies( array(), 'object' );

// Determine the excluded meta keys
$excluded_fields = apply_filters( 'facetwp_excluded_custom_fields', array(
    '_edit_last',
    '_edit_lock',
) );

$meta_keys = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} ORDER BY meta_key" );
$custom_fields = array_diff( $meta_keys, $excluded_fields );

// activation status
$message = __( 'Not yet activated', 'fwp' );
$activation = get_option( 'facetwp_activation' );
if ( ! empty( $activation ) ) {
    $activation = json_decode( $activation );
    if ( 'success' == $activation->status ) {
        $message = __( 'License active', 'fwp' );
        $message .= ' (' . __( 'expires', 'fwp' ) . ' ' . date( 'M j, Y', strtotime( $activation->expiration ) ) . ')';
    }
    else {
        $message = $activation->message;
    }
}

// Export feature
$export = array();
$settings = FWP()->helper->settings_raw;

foreach ( $settings['facets'] as $facet ) {
    $export['facet-' . $facet['name']] = 'Facet - ' . $facet['label'];
}

foreach ( $settings['templates'] as $template ) {
    $export['template-' . $template['name']] = 'Template - '. $template['label'];
}

?>

<script src="<?php echo FACETWP_URL; ?>/assets/js/event-manager.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<?php
foreach ( $facet_types as $class ) {
    $class->admin_scripts();
}
?>
<script src="<?php echo FACETWP_URL; ?>/assets/js/admin.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<link href="<?php echo FACETWP_URL; ?>/assets/css/admin.css?ver=<?php echo FACETWP_VERSION; ?>" rel="stylesheet">

<div class="facetwp-header">
    <span class="facetwp-logo" title="FacetWP">&nbsp;</span>
    <span class="facetwp-header-nav">
        <a class="facetwp-nav-tab" rel="facets"><?php _e( 'Facets', 'fwp' ); ?></a>
        <a class="facetwp-nav-tab" rel="templates"><?php _e( 'Templates', 'fwp' ); ?></a>
        <a class="facetwp-nav-tab" rel="settings"><?php _e( 'Settings', 'fwp' ); ?></a>
        <a class="facetwp-nav-tab" rel="support"><?php _e( 'Support', 'fwp' ); ?></a>
    </span>
</div>

<div class="wrap">

    <div class="facetwp-response"></div>
    <div class="facetwp-loading"></div>

    <!-- Facets tab -->

    <div class="facetwp-content facetwp-content-facets">
        <div class="facetwp-action-buttons">
            <div style="float:right">
                <a class="button facetwp-rebuild"><?php _e( 'Re-index', 'fwp' ); ?></a>
                <a class="button-primary facetwp-save"><?php _e( 'Save Changes', 'fwp' ); ?></a>
            </div>
            <a class="button add-facet"><?php _e( 'Add Facet', 'fwp' ); ?></a>
            <div class="clear"></div>
        </div>

        <div class="facetwp-tabs">
            <ul></ul>
        </div>
        <div class="facetwp-facets"></div>
        <div class="clear"></div>
    </div>

    <!-- Templates tab -->

    <div class="facetwp-content facetwp-content-templates">
        <div class="facetwp-action-buttons">
            <div style="float:right">
                <a class="button-primary facetwp-save"><?php _e( 'Save Changes', 'fwp' ); ?></a>
            </div>
            <a class="button add-template"><?php _e( 'Add Template', 'fwp' ); ?></a>
            <div class="clear"></div>
        </div>

        <div class="facetwp-tabs">
            <ul></ul>
        </div>
        <div class="facetwp-templates"></div>
        <div class="clear"></div>
    </div>

    <!-- Settings tab -->

    <div class="facetwp-content facetwp-content-settings">
        <div class="facetwp-action-buttons">
            <div style="float:right">
                <a class="button-primary facetwp-save"><?php _e( 'Save Changes', 'fwp' ); ?></a>
            </div>
            <div class="clear"></div>
        </div>

        <div class="facetwp-settings-wrap">
            <table>
                <tr>
                    <td style="width:175px"><?php _e( 'License Key', 'fwp' ); ?></td>
                    <td>
                        <input type="text" class="facetwp-license" style="width:280px" value="<?php echo get_option( 'facetwp_license' ); ?>" />
                        <input type="button" class="button facetwp-activate" value="<?php _e( 'Activate', 'fwp' ); ?>" />
                        <div class="facetwp-activation-status field-notes"><?php echo $message; ?></div>
                    </td>
                </tr>
            </table>

            <!-- General settings -->

            <table style="width:100%; margin-top:20px">
                <tr>
                    <td style="width:175px; vertical-align:top">
                        <?php _e( 'Permalink Type', 'fwp' ); ?>
                        <div class="facetwp-tooltip">
                            <span class="icon-question">?</span>
                            <div class="facetwp-tooltip-content"><?php _e( 'How should permalinks be constructed?', 'fwp' ); ?></div>
                        </div>
                    </td>
                    <td>
                        <select class="facetwp-setting" data-name="permalink_type">
                            <option value="hash"><?php _e( 'URL Hash', 'fwp' ); ?></option>
                            <option value="get"><?php _e( 'GET variables', 'fwp' ); ?></option>
                        </select>
                        <div class="field-notes" style="margin-bottom:20px"><?php _e( 'GET variables are uglier, but more SEO-friendly.', 'fwp' ); ?></div>
                    </td>
                </tr>
                <tr>
                    <td style="width:175px; vertical-align:top">
                        <?php _e( 'Term URLs', 'fwp' ); ?>
                        <div class="facetwp-tooltip">
                            <span class="icon-question">?</span>
                            <div class="facetwp-tooltip-content"><?php _e( 'What should appear in the URL for taxonomy terms?', 'fwp' ); ?></div>
                        </div>
                    </td>
                    <td>
                        <select class="facetwp-setting" data-name="term_permalink">
                            <option value="term_id"><?php _e( 'Term ID', 'fwp' ); ?></option>
                            <option value="slug"><?php _e( 'Slug', 'fwp' ); ?></option>
                        </select>
                        <div class="field-notes"><?php _e( 'Please re-index after changing this value.', 'fwp' ); ?></div>
                    </td>
                </tr>
            </table>

            <!-- Migration -->

            <table style="width:100%; margin-top:20px">
                <tr>
                    <td style="width:175px; vertical-align:top">
                        <?php _e( 'Export', 'fwp' ); ?>
                    </td>
                    <td valign="top" style="width:260px">
                        <select class="export-items" multiple="multiple" style="width:250px; height:100px">
                            <?php foreach ( $export as $val => $label ) : ?>
                            <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div style="margin-top:5px"><a class="button export-submit"><?php _e( 'Export', 'fwp' ); ?></a></div>
                    </td>
                    <td valign="top">
                        <textarea class="export-code" placeholder="Loading..."></textarea>
                    </td>
                </tr>
            </table>

            <table style="width:100%; margin-top:20px">
                <tr>
                    <td style="width:175px; vertical-align:top">
                        <?php _e( 'Import', 'fwp' ); ?>
                    </td>
                    <td>
                        <div><textarea class="import-code" placeholder="<?php _e( 'Paste the import code here', 'fwp' ); ?>"></textarea></div>
                        <div><input type="checkbox" class="import-overwrite" /> <?php _e( 'Overwrite existing items?', 'fwp' ); ?></div>
                        <div style="margin-top:5px"><a class="button import-submit"><?php _e( 'Import', 'fwp' ); ?></a></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Support tab -->

    <div class="facetwp-content facetwp-content-support">
        <?php include( FACETWP_DIR . '/templates/page-support.php' ); ?>
    </div>

    <!-- Hidden: clone settings -->

    <div class="facets-hidden">
        <div class="facetwp-facet">
            <table class="facetwp-table">
                <tr>
                    <td style="width:175px"><?php _e( 'Label', 'fwp' ); ?>:</td>
                    <td>
                        <input type="text" class="facet-label" value="" />
                        <input type="text" class="facet-name" value="" />
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Facet type', 'fwp' ); ?>:</td>
                    <td>
                        <select class="facet-type">
                            <?php foreach ( $facet_types as $name => $class ) : ?>
                            <option value="<?php echo $name; ?>"><?php echo $class->label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="facetwp-show name-source">
                    <td>
                        <?php _e( 'Data source', 'fwp' ); ?>:
                    </td>
                    <td>
                        <select class="facet-source">
                            <optgroup label="<?php _e( 'Posts', 'fwp' ); ?>">
                                <option value="post_type"><?php _e( 'Post Type', 'fwp' ); ?></option>
                                <option value="post_date"><?php _e( 'Post Date', 'fwp' ); ?></option>
                                <option value="post_modified"><?php _e( 'Post Modified', 'fwp' ); ?></option>
                                <option value="post_title"><?php _e( 'Post Title', 'fwp' ); ?></option>
                                <option value="post_author"><?php _e( 'Post Author', 'fwp' ); ?></option>
                            </optgroup>
                            <optgroup label="<?php _e( 'Taxonomies', 'fwp' ); ?>">
                                <?php foreach ( $taxonomies as $tax ) : ?>
                                <option value="tax/<?php echo esc_attr( $tax->name ); ?>"><?php echo esc_attr( $tax->labels->name ); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="<?php _e( 'Custom Fields', 'fwp' ); ?>">
                                <?php foreach ( $custom_fields as $cf ) : ?>
                                <option value="cf/<?php echo esc_attr( $cf ); ?>"><?php echo esc_attr( $cf ); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </td>
                </tr>
<?php
foreach ( $facet_types as $class ) {
    $class->settings_html();
}
?>
            </table>
            <a class="remove-facet"><?php _e( 'Delete Facet', 'fwp' ); ?></a>
        </div>
    </div>

    <div class="templates-hidden">
        <div class="facetwp-template">
            <div class="table-row">
                <div class="row-label">
                    <?php _e( 'Label', 'fwp' ); ?>:
                    <div class="facetwp-tooltip">
                        <span class="icon-question">?</span>
                        <div class="facetwp-tooltip-content">Use the template name (to the right of the label) when using the template shortcode</div>
                    </div>
                </div>
                <input type="text" class="template-label" value="" />
                <input type="text" class="template-name" value="" />
            </div>
            <div class="table-row">
                <div class="row-label">
                    <?php _e( 'Query Arguments', 'fwp' ); ?>:
                    <div class="facetwp-tooltip">
                        <span class="icon-question">?</span>
                        <div class="facetwp-tooltip-content">This box returns an array of <a href="http://codex.wordpress.org/Class_Reference/WP_Query" target="_blank">WP_Query</a> arguments that are used to fetch the initial batch of posts from the database.</div>
                    </div>
                </div>
                <textarea class="template-query"></textarea>
            </div>
            <div class="table-row">
                <div class="row-label">
                    <?php _e( 'Display Code', 'fwp' ); ?>:
                    <div class="facetwp-tooltip">
                        <span class="icon-question">?</span>
                        <div class="facetwp-tooltip-content">This is your template output. Using the <a href="http://codex.wordpress.org/The_Loop" target="_blank">WordPress Loop</a>, we iterate through our posts to display some HTML for each.</div>
                    </div>
                </div>
                <textarea class="template-template"></textarea>
            </div>
            <a class="remove-template"><?php _e( 'Delete Template', 'fwp' ); ?></a>
        </div>
    </div>
</div>
