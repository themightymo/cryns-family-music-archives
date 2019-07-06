<?php

// Support tab HTML
include( FACETWP_DIR . '/templates/page-support.php' );
$support_html = new FacetWP_Support();
$support_html = $support_html->get_html();

// Settings
$settings_admin = new FacetWP_Settings_Admin();
$settings_array = $settings_admin->get_settings();
$i18n = $settings_admin->get_i18n_strings();
$image_sizes = $settings_admin->get_image_size_labels();

// Useful data
$data = FWP()->helper->settings;
$facet_types = FWP()->helper->facet_types;
$data_sources = FWP()->helper->get_data_sources();
$layout_data = FWP()->builder->get_layout_data();
$query_data = FWP()->builder->get_query_data();

// Clone facet settings HTML
$facet_clone = [];
$admin_scripts = [];

foreach ( $facet_types as $name => $class ) {
    $facet_clone[ $name ] = '<div>' . __( 'This facet type has no additional settings.', 'fwp' ) . '</div>';
    if ( method_exists( $class, 'settings_html' ) ) {
        ob_start();
        $class->settings_html();
        $output = ob_get_clean();
        $facet_clone[ $name ] = trim( $output );
    }

    if (method_exists( $class, 'admin_scripts' ) ) {
        ob_start();
        $class->admin_scripts();
        $admin_scripts[] = ob_get_clean();
    }
}

?>

<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vue/vue.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vue/Sortable.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vue/vuedraggable.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vue/vue-clickaway.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vue/vue-multiselect.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/font-awesome/solid.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/font-awesome/fontawesome.min.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/js/src/event-manager.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/fSelect/fSelect.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/vendor/vanilla-picker-mini/vanilla-picker-mini.min.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<?php

// Let add-ons register custom Vue components
echo implode( '', $admin_scripts );

?>
<script src="<?php echo FACETWP_URL; ?>/assets/js/dist/admin.min.js?ver=<?php echo FACETWP_VERSION; ?>"></script>
<script>

window.FWP = {
    __: function(str) {
        return ('undefined' !== typeof FWP.i18n[str]) ? FWP.i18n[str] : str;
    },
    data: <?php echo json_encode( $data ); ?>,
    i18n: <?php echo json_encode( $i18n ); ?>,
    image_sizes: <?php echo json_encode( $image_sizes ); ?>,
    clone: <?php echo json_encode( $facet_clone ); ?>,
    facet_types: <?php echo json_encode( $facet_types ); ?>,
    data_sources: <?php echo json_encode( $data_sources ); ?>,
    layout_data: <?php echo json_encode( $layout_data ); ?>,
    query_data: <?php echo json_encode( $query_data ); ?>,
    support_html: <?php echo json_encode( $support_html ); ?>,
    nonce: '<?php echo wp_create_nonce( 'fwp_admin_nonce' ); ?>',
    hooks: FWP.hooks // WP-JS-Hooks
};

// Settings load hook
FWP.data.settings = FWP.hooks.applyFilters('facetwp/load_settings', FWP.data.settings);

</script>
<link href="<?php echo FACETWP_URL; ?>/assets/css/admin.css?ver=<?php echo FACETWP_VERSION; ?>" rel="stylesheet">
<link href="<?php echo FACETWP_URL; ?>/assets/vendor/fSelect/fSelect.css?ver=<?php echo FACETWP_VERSION; ?>" rel="stylesheet">
<link href="<?php echo FACETWP_URL; ?>/assets/vendor/vue/vue-multiselect.min.css" rel="stylesheet">

<div id="app">
    <div class="facetwp-header">
        <span class="facetwp-logo" title="FacetWP">&nbsp;</span>
        <span class="facetwp-version">v<?php echo FACETWP_VERSION; ?></span>

        <span class="facetwp-header-nav">
            <a class="facetwp-tab" :class="{ active: active_tab == 'facets' }" @click="tabClick('facets')"><?php _e( 'Facets', 'fwp' ); ?></a>
            <a class="facetwp-tab" :class="{ active: active_tab == 'templates' }" @click="tabClick('templates')"><?php _e( 'Templates', 'fwp' ); ?></a>
            <a class="facetwp-tab" :class="{ active: active_tab == 'settings' }" @click="tabClick('settings')"><?php _e( 'Settings', 'fwp' ); ?></a>
            <a class="facetwp-tab" :class="{ active: active_tab == 'support' }" @click="tabClick('support')"><?php _e( 'Support', 'fwp' ); ?></a>
        </span>

        <span class="facetwp-actions">
            <div class="btn-split facetwp-rebuild">
                <div class="btn-label" @click="rebuildAction" v-html="indexButtonLabel"></div>
                <div class="btn-caret" @click="is_rebuild_open = !is_rebuild_open"><i class="fas fa-caret-down"></i></div>
                <div class="btn-dropdown" v-cloak v-show="is_rebuild_open">
                    <div class="dropdown-inner">
                        <div @click="showIndexerStats"><?php _e( 'Show indexer stats', 'fwp' ); ?></div>
                        <div @click="searchablePostTypes"><?php _e( 'Show indexable post types', 'fwp' ); ?></div>
                        <div @click="purgeIndexTable"><?php _e( 'Purge the index table', 'fwp' ); ?></div>
                    </div>
                </div>
            </div>
            <div class="btn-normal" @click="saveChanges">
                <?php _e( 'Save changes', 'fwp' ); ?>
            </div>
        </span>

        <span class="facetwp-response"></span>
    </div>

    <div class="wrap">
        <div class="facetwp-loading" :class="{ hidden: true }"></div>

        <!-- Facets tab -->

        <div class="facetwp-region facetwp-region-facets" :class="{ active: active_tab == 'facets' }">
            <h3 v-show="isEditing">
                <a @click="doneEditing"><?php _e( 'Facets', 'fwp' ); ?></a> &nbsp;&raquo;&nbsp;
                <span>{{ getItemLabel() }}</span>
            </h3>
            <h3 v-show="!isEditing">
                <?php _e( 'Facets', 'fwp' ); ?>
                <span class="facetwp-btn facetwp-add" @click="addItem('facet')"><?php _e( 'Add new', 'fwp' ); ?></span>
            </h3>

            <div class="content-facets" v-show="!isEditing">
                <div class="facetwp-table-header">
                    <div></div>
                    <div><?php _e( 'Facet', 'fwp' ); ?></div>
                    <div></div>
                    <div><?php _e( 'Type', 'fwp' ); ?></div>
                    <div><?php _e( 'Source', 'fwp' ); ?></div>
                    <div><?php _e( 'Rows', 'fwp' ); ?></div>
                </div>
                <facets :facets="app.facets"></facets>
            </div>

            <facet-edit v-if="editing_facet"></facet-edit>
        </div>

        <!-- Templates tab -->

        <div class="facetwp-region facetwp-region-templates" :class="{ active: active_tab == 'templates' }">
            <h3 v-show="isEditing">
                <a @click="doneEditing"><?php _e( 'Templates', 'fwp' ); ?></a> &nbsp;&raquo;&nbsp;
                <span>{{ getItemLabel() }}</span>
            </h3>
            <h3 v-show="!isEditing">
                <?php _e( 'Templates', 'fwp' ); ?>
                <span class="facetwp-btn facetwp-add" @click="addItem('template')"><?php _e( 'Add new', 'fwp' ); ?></span>
            </h3>

            <div class="content-templates" v-show="!isEditing">
                <div class="facetwp-table-header">
                    <div></div>
                    <div><?php _e( 'Template', 'fwp' ); ?></div>
                    <div></div>
                    <div><?php _e( 'Display mode', 'fwp' ); ?></div>
                    <div><?php _e( 'Post types', 'fwp' ); ?></div>
                </div>
                <templates :templates="app.templates"></templates>
            </div>

            <template-edit v-if="editing_template"></template-edit>
        </div>

        <!-- Settings tab -->

        <div class="facetwp-region facetwp-region-settings" :class="{ active: active_tab == 'settings' }">
            <div class="facetwp-subnav">
                <?php foreach ( $settings_array as $key => $tab ) : ?>
                <a :class="{ active: active_subnav == '<?php echo $key; ?>' }" @click="active_subnav = '<?php echo $key; ?>'"><?php echo $tab['label']; ?></a>
                <?php endforeach; ?>
            </div>

            <?php foreach ( $settings_array as $key => $tab ) : ?>
            <div class="facetwp-settings-section" :class="{ active: active_subnav == '<?php echo $key; ?>' }">
                <?php foreach ( $tab['fields'] as $field_data ) : ?>
                <div class="facetwp-row">
                    <div>
                        <?php echo $field_data['label']; ?>
                        <?php if ( isset( $field_data['notes'] ) ) : ?>
                        <div class="facetwp-tooltip">
                            <span class="icon-question">?</span>
                            <div class="facetwp-tooltip-content"><?php echo $field_data['notes']; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div><?php echo $field_data['html']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Support tab -->

        <div class="facetwp-region facetwp-region-support" :class="{ active: active_tab == 'support' }">
            <div v-if="is_support_loaded" v-html="support_html"></div>
        </div>

        <!-- Copy to clipboard -->

        <input class="facetwp-clipboard hidden" value="" />

    </div>
</div>
