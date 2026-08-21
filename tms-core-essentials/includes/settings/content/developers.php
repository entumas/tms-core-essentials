<?php
/**
 * Includes -> Settings -> Content -> Developers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_developers_render_tab_fields(): void {
	?>
	<div class="tcres-settings-panel with-sidebar">
		<main>
			<h2><?php esc_html_e( 'Developer reference', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'PHP APIs and frontend JavaScript utilities you can use from the theme or custom code. Module helpers (breadcrumbs, hero, share, etc.) are documented in each module’s Usage panel.', 'tms-core-essentials' ); ?>
			</p>

			<h2 id="tcres-dev-data"><?php esc_html_e( 'Data', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Read post meta, term meta, and plugin or WordPress options.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-field-get">
				<h3>⇒ <?php esc_html_e( 'Get post fields', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_field_get( array $args = [] ): string' ); ?></code></p>
				<p><?php esc_html_e( 'Reads post post_meta. Returns an empty string if the post, field, or value is missing. Shortcodes run only when the value contains [.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Meta structure: simple → string; repeatable → array( \'a\', \'b\' ); group → array( array( \'title\' => \'…\' ), … ); repeatable within group → row with a key whose value is array( \'…\', \'…\' ).', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$args[\'field\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Post meta key.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'post_id\']' ); ?></code>
						(<?php echo esc_html( 'int' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Post ID. Defaults to the current post.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'field_num\']' ); ?></code>
						(<?php echo esc_html( 'int|string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Repeatable value index (1, 2, …). -1 returns all concatenated; empty or 0 uses the first value.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'group\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Meta key that stores a field group.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'group_num\']' ); ?></code>
						(<?php echo esc_html( 'int|string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Repeatable group index when $args[\'group\'] is set.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'before\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Text prepended to the value.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'after\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Text appended to the value.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'before_repeat\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Text prepended before concatenating repeatable values.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'after_repeat\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Text appended after concatenating repeatable values.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'format\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Output format: wpautop or esc_html.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "// Simple\necho tcres_field_get( array( 'field' => 'my_custom_field' ) );\n\n// Repeatable (meta = array of strings)\necho tcres_field_get( array( 'field' => 'gallery_caption', 'field_num' => -1 ) );\n\n// Group (group meta = array of associative rows)\necho tcres_field_get( array( 'group' => 'slides', 'field' => 'title', 'group_num' => 1 ) );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: HTML/text for the requested value. Empty repeatable entries are skipped; field_num is 1-based (1 = first).', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-tax-field-get">
				<h3>⇒ <?php esc_html_e( 'Get term fields', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_tax_field_get( array $args = [] ): string' ); ?></code></p>
				<p><?php esc_html_e( 'Same as tcres_field_get, but reads term_meta from the first term assigned to the post in the given taxonomy.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Same meta structure and arguments as tcres_field_get (simple, repeatable, group).', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$args[\'tax\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Taxonomy slug.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'field\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Term meta key.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<p class="description"><?php esc_html_e( 'Optional: same as tcres_field_get (post_id, field_num, group, group_num, before, after, before_repeat, after_repeat, format).', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "echo tcres_tax_field_get( array( 'tax' => 'category', 'field' => 'color' ) );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: term meta value, or an empty string if the post has no terms in that taxonomy.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-option-get">
				<h3>⇒ <?php esc_html_e( 'Get options', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_option_get( string $name, string $key = \'\', string $format = \'\' ): mixed' ); ?></code></p>
				<p><?php esc_html_e( 'Reads tcres_settings first. If $name is a settings group, returns that group/key (or null if the key is missing). Otherwise falls back to a standalone WordPress option. With WPML/Polylang, merges translations over that option.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Full group: tcres_option_get( \'security\' ). Keys with null values in settings count as existing.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$name' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Settings group or WordPress option name.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$key' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key within the group. If empty, returns the full group.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$format' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Output format: empty (no formatting), esc_html, or wpautop.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$enabled = tcres_option_get( \'security\', \'enable\' );' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: the requested value, or null if the key does not exist. With format esc_html or wpautop, returns a formatted string.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-discovery"><?php esc_html_e( 'Options / discovery', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Resolve which post types or taxonomies are enabled for a settings group, and list included site types.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-option-get-for-post-types">
				<h3>⇒ <?php esc_html_e( 'Get post types by option', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_option_get_for_post_types( string $option_group, string $option_prefix ): array|false' ); ?></code></p>
				<p><?php esc_html_e( 'Returns post types where a prefixed option is active (truthy value).', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Iterates post types from tcres_post_types_get_included(). Only keys with a truthy PHP value.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$option_group' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Settings group to search for keys.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$option_prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key prefix; concatenated with each included post type (enable_ + post → enable_post).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$types = tcres_option_get_for_post_types( \'features\', \'enable_\' );' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: array of slugs with the option active (e.g. post, page), or false if none are active.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-option-get-for-taxonomies">
				<h3>⇒ <?php esc_html_e( 'Get taxonomies by option', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_option_get_for_taxonomies( string $option_group, string $option_prefix ): array|false' ); ?></code></p>
				<p><?php esc_html_e( 'Returns taxonomies where a prefixed option is active (truthy value).', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Iterates taxonomies from tcres_taxonomies_get_included(). Same prefix pattern as the post types helper.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$option_group' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Settings group to search for keys.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$option_prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key prefix; concatenated with each included taxonomy (enable_ + category → enable_category).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$taxes = tcres_option_get_for_taxonomies( \'features\', \'enable_\' );' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: array of taxonomy slugs with the option active, or false if none are active.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-option-post-types-diff">
				<h3>⇒ <?php esc_html_e( 'Compare post types across options', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_option_get_for_post_types_diff( string $from_option_group, string $from_option_prefix, string $against_option_group, string $against_option_prefix ): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns post types active in one option set but not in another.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$from_option_group' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Settings group for the source set.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$from_option_prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key prefix for the source set.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$against_option_group' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Settings group for the comparison set.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$against_option_prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key prefix for the comparison set.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$diff = tcres_option_get_for_post_types_diff( \'a\', \'on_\', \'b\', \'on_\' );' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: post types active in a/on_ but not in b/on_; empty array if there are no differences.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-post-types-included">
				<h3>⇒ <?php esc_html_e( 'Get included post types', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_post_types_get_included(): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns useful site post types, excluding internal WordPress and common plugin types.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Extensible via the Filters section below.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<p class="description"><?php esc_html_e( 'No parameters.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$types = tcres_post_types_get_included();' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: array of slugs. Excludes attachment, revision, FSE types, WooCommerce orders, etc. by default.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-taxonomies-included">
				<h3>⇒ <?php esc_html_e( 'Get included taxonomies', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_taxonomies_get_included(): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns useful site taxonomies, excluding system and common plugin taxonomies.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Extensible via tcres_taxonomies_get_included_exclude and tcres_taxonomies_get_included filters.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<p class="description"><?php esc_html_e( 'No parameters.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$taxes = tcres_taxonomies_get_included();' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: array of taxonomy slugs. Excludes nav_menu, post_format, FSE and WooCommerce system taxonomies by default.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-media"><?php esc_html_e( 'Media', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Image sizes, dimension checks, and SVG sprite icons.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-image-sizes">
				<h3>⇒ <?php esc_html_e( 'Get registered image sizes', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_image_size_get_registered(): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns image sizes registered in WordPress, ready for UI selects.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Skips sizes without defined width and height.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<p class="description"><?php esc_html_e( 'No parameters.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$sizes = tcres_image_size_get_registered();' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: indexed array of rows with label, value, and optional selected (medium).', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-image-validate-size">
				<h3>⇒ <?php esc_html_e( 'Validate image size', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_image_validate_size( int $attachment_id, ?array $width = null, ?array $height = null ): bool' ); ?></code></p>
				<p><?php esc_html_e( 'Checks whether an attachment dimensions meet optional width and/or height rules.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$attachment_id' ); ?></code>
						(<?php echo esc_html( 'int' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Image attachment ID.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$width' ); ?></code>
						(<?php echo esc_html( 'array|null' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Width rule: array( \'size\' => int, \'operator\' => \'>\' | \'>=\' | \'<\' | \'<=\' | \'==\' ).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$height' ); ?></code>
						(<?php echo esc_html( 'array|null' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Height rule with the same structure as $width.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$valid = tcres_image_validate_size( 42, array( \'size\' => 1200, \'operator\' => \'>=\' ), array( \'size\' => 800, \'operator\' => \'<=\' ) );' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: true only if at least one rule was passed and all pass; false otherwise or without image metadata.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-svg-icon">
				<h3>⇒ <?php esc_html_e( 'Get SVG icon', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_svg_icon_get( array $args = [] ): string' ); ?></code></p>
				<p><?php esc_html_e( 'Outputs SVG icon markup using a sprite (<use href="file.svg#icon-id">).', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Classes: tcres-svg-icon + tcres-svg-icon-{icon-id}. Optional inline adds tcres-svg-icon-inline.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$args[\'icon\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Symbol ID within the sprite (e.g. arrow-up). Empty returns an empty string.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'file\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Full URL to the sprite .svg. Empty uses Extras → SVG icons (frontend/admin), then the plugin default.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'inline\']' ); ?></code>
						(<?php echo esc_html( 'bool' ); ?><?php esc_html_e( ', optional, default false', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'If true, adds the class tcres-svg-icon-inline.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "echo tcres_svg_icon_get( array( 'icon' => 'arrow-up', 'inline' => true ) );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: escaped HTML ready to echo.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-helpers"><?php esc_html_e( 'Helpers', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Theme templates, slug conversion, and locale-aware dates.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-template-info">
				<h3>⇒ <?php esc_html_e( 'Get template information', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_template_get_info(): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns page templates registered by the active theme.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Slug example: templates/template-contact.php → contact. No hyphens in the slug.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<p class="description"><?php esc_html_e( 'No parameters.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( '$templates = tcres_template_get_info();' ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: array of rows with slug, name, and file (path relative to the theme).', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-slug-to-id">
				<h3>⇒ <?php esc_html_e( 'Convert slug to ID', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_slug_convert_to_id( string|array $value, string $type = \'post\', string $taxonomy = \'\' ): int|array|null' ); ?></code></p>
				<p><?php esc_html_e( 'Converts one or more slugs to post or term IDs. Also accepts numeric values. For posts, only searches included post types.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Posts: publish status only and post types from tcres_post_types_get_included(). Numeric IDs are returned as-is.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$value' ); ?></code>
						(<?php echo esc_html( 'string|array' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Slug or list of slugs/IDs. If an array, returns an array of IDs in the same order.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$type' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Entity type: post (default) or term.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$taxonomy' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Term taxonomy. Required when $type is term.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "\$id = tcres_slug_convert_to_id( 'my-post' );\n\$ids = tcres_slug_convert_to_id( array( 'foo', 'bar' ) );\n\$term_id = tcres_slug_convert_to_id( 'news', 'term', 'category' );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: ID or null (published posts only). With an array, a list of IDs in the same order.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-date-format">
				<h3>⇒ <?php esc_html_e( 'Format dates', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_date_format( string $date, string $format = \'\' ): string' ); ?></code></p>
				<p><?php esc_html_e( 'Parses a date string and formats it with the WordPress locale (wp_date).', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$date' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Date as text (2024-06-15, 15/06/2024, d-m-Y, etc.).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$format' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'PHP output format. If empty, uses Settings → General.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "echo tcres_date_format( '2024-06-15' );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: formatted date for the site locale, or an empty string if the date is invalid.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-meta-write"><?php esc_html_e( 'Meta write', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Save metabox fields from $_POST with nonce and capability checks.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-post-meta-update">
				<h3>⇒ <?php esc_html_e( 'Update post meta', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_post_meta_update( int $post_id, array $meta_keys, string $prefix = \'\', string $sanitize = \'text\' ): void' ); ?></code></p>
				<p><?php esc_html_e( 'Saves or deletes post meta from $_POST in the admin, with nonce verification and edit_post capability. Call it on save_post.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Nonce: with prefix my_ and key my_subtitle → field my_subtitle_nonce and action _my_subtitle_nonce (segment after the prefix, up to the first _ if more follow). Does not run on autosave or without edit_post.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$post_id' ); ?></code>
						(<?php echo esc_html( 'int' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Post ID to update.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$meta_keys' ); ?></code>
						(<?php echo esc_html( 'array<string>' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Meta keys to read from $_POST. Nonce is derived from the first element.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Common prefix for keys and nonce (e.g. my_).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$sanitize' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional, default text', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'text uses sanitize_text_field (+ filter); html uses wp_kses_post (+ HTML filter).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "// Metabox\nwp_nonce_field( '_my_subtitle_nonce', 'my_subtitle_nonce' );\n// <input name=\"my_subtitle\" value=\"…\">\n\n// save_post — multiple keys at once\nadd_action( 'save_post', function ( int \$post_id ): void {\n\ttcres_post_meta_update( \$post_id, array( 'my_subtitle', 'my_teaser' ), 'my_' );\n} );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: nothing. Saves a sanitized value or deletes meta if the POST field is empty. The value "0" is saved.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-term-meta-update">
				<h3>⇒ <?php esc_html_e( 'Update term meta', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_term_meta_update( int $term_id, array $meta_keys, string $prefix = \'\', string $sanitize = \'text\' ): void' ); ?></code></p>
				<p><?php esc_html_e( 'Same pattern as tcres_post_meta_update, for taxonomy terms. Call it from created_{taxonomy} / edited_{taxonomy} (or equivalent). Requires edit_term capability.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Nonce derivation matches post meta. Sanitize modes: text or html.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$term_id' ); ?></code>
						(<?php echo esc_html( 'int' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Term ID to update.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$meta_keys' ); ?></code>
						(<?php echo esc_html( 'array<string>' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Meta keys to read from $_POST. Nonce is derived from the first element.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$prefix' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Common prefix for keys and nonce (e.g. my_).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$sanitize' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional, default text', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'text or html (same behaviour as post meta update).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "add_action( 'edited_category', function ( int \$term_id ): void {\n\ttcres_term_meta_update( \$term_id, array( 'my_color' ), 'my_' );\n} );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: nothing. Saves or deletes term meta like the post helper.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-filters"><?php esc_html_e( 'Filters', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Hooks to adjust included post types and meta sanitization.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-filter-post-types-exclude">
				<h3>⇒ <?php esc_html_e( 'Exclude post types from the included list', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_post_types_get_included_exclude' ); ?></code></p>
				<p><?php esc_html_e( 'Adds post type slugs to the exclusion list before computing included types.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Filter parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$exclude' ); ?></code>
						(<?php echo esc_html( 'array<string>' ); ?>)
						&mdash; <?php esc_html_e( 'Post type slugs excluded by default.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "add_filter( 'tcres_post_types_get_included_exclude', function ( array \$exclude ): array {\n\t\$exclude[] = 'my_internal_cpt';\n\treturn \$exclude;\n} );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: the array you return replaces the default exclusion list before computation.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-filter-post-types-included">
				<h3>⇒ <?php esc_html_e( 'Modify included post types', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_post_types_get_included' ); ?></code></p>
				<p><?php esc_html_e( 'Alters the final list returned by tcres_post_types_get_included().', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Filter parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$include' ); ?></code>
						(<?php echo esc_html( 'array<string>' ); ?>)
						&mdash; <?php esc_html_e( 'Slugs included after exclusions are applied.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "add_filter( 'tcres_post_types_get_included', function ( array \$include ): array {\n\treturn array_values( array_diff( \$include, array( 'product' ) ) );\n} );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: the array you return is the final tcres_post_types_get_included() list.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-filter-post-meta-sanitize">
				<h3>⇒ <?php esc_html_e( 'Sanitize meta before save', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_post_meta_sanitize_value' ); ?></code></p>
				<p><?php esc_html_e( 'Filters the sanitized meta field value before saving with tcres_post_meta_update() or tcres_term_meta_update() when sanitize is text.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Filter parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$value' ); ?></code>
						(<?php echo esc_html( 'string' ); ?>)
						&mdash; <?php esc_html_e( 'Value already passed through sanitize_text_field.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$raw' ); ?></code>
						(<?php echo esc_html( 'mixed' ); ?>)
						&mdash; <?php esc_html_e( 'Raw value received from $_POST.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "add_filter( 'tcres_post_meta_sanitize_value', function ( string \$value, mixed \$raw ): string {\n\treturn wp_kses_post( (string) \$raw );\n}, 10, 2 );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Returns: string saved to meta.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-js"><?php esc_html_e( 'JavaScript', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Frontend utilities bundled in the plugin scripts (assets/js/scripts.js). They initialize automatically when matching markup is present.', 'tms-core-essentials' ); ?>
			</p>

			<section id="tcres-dev-collapse">
				<h3>⇒ <?php esc_html_e( 'Collapse', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcresCollapseInit()' ); ?></code></p>
				<p><?php esc_html_e( 'Reusable collapse / accordion behaviour. Pair a trigger and a target with the same data-tcres-collapse-id. The plugin calls tcresCollapseInit() on DOMContentLoaded when any trigger exists; call it again after injecting markup dynamically.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Markup', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code>data-tcres-collapse-trigger</code>
						&mdash; <?php esc_html_e( 'Clickable control (also supports Enter / Space).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-collapse-target</code>
						&mdash; <?php esc_html_e( 'Panel that opens and closes.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-collapse-id</code>
						&mdash; <?php esc_html_e( 'Shared ID linking one trigger to one target.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-collapse-accordion</code>
						&mdash; <?php esc_html_e( 'Optional wrapper: opening one item closes siblings inside it.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-collapse-is-anchor</code>
						&mdash; <?php esc_html_e( 'Optional on the trigger: do not preventDefault (useful for real links).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.tcres-collapse</code>, <code>.tcres-collapse-title</code>, <code>.tcres-collapse-content</code>
						&mdash; <?php esc_html_e( 'CSS classes for the default open/close animation. Toggle state uses .is-open. Keep the title label as plain/inline text (no wrapping block tags).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "\$id = tcres_unique_id( 'tcres-collapse-' );\n\n<div class=\"tcres-collapse\">\n\t<div\n\t\tclass=\"tcres-collapse-title\"\n\t\trole=\"button\"\n\t\ttabindex=\"0\"\n\t\taria-expanded=\"false\"\n\t\tdata-tcres-collapse-trigger\n\t\tdata-tcres-collapse-id=\"{id}\">\n\t\tShow details\n\t</div>\n\t<div\n\t\tclass=\"tcres-collapse-content\"\n\t\tdata-tcres-collapse-target\n\t\tdata-tcres-collapse-id=\"{id}\">\n\t\t<p>Hidden until opened.</p>\n\t</div>\n</div>\n\n// Replace {id} with esc_attr( \$id ). After AJAX:\nwindow.tcresCollapseInit();" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Generate unique IDs in PHP with tcres_unique_id(). Frontend scripts expose window.tcresCollapseInit for manual re-init.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-tabs">
				<h3>⇒ <?php esc_html_e( 'Tabs', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcresTabsInit( root = document )' ); ?></code></p>
				<p><?php esc_html_e( 'Reusable tab panels with URL hash sync. The tab nav must be immediately followed by the panels wrapper. The plugin calls tcresTabsInit() on DOMContentLoaded; call it again after injecting markup dynamically.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Markup', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code>data-tcres-tabs</code>
						&mdash; <?php esc_html_e( 'Tab list container (usually a nav with role="tablist").', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-tab-id</code>
						&mdash; <?php esc_html_e( 'Tab slug used in the URL hash (#specs). Must be unique across the page.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-tab-default</code>
						&mdash; <?php esc_html_e( 'Optional on one tab: opens when the URL has no hash or an invalid hash.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-tab-panels</code>
						&mdash; <?php esc_html_e( 'Panels wrapper placed as the next sibling after the tab list. Enables fade transitions.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>role="tab"</code>, <code>role="tabpanel"</code>, <code>aria-controls</code>, <code>aria-selected</code>
						&mdash; <?php esc_html_e( 'Use the WAI-ARIA tabs pattern. Active state uses .is-active on tabs and panels.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.tcres-tab-panels</code>, <code>.tcres-tab-panel</code>
						&mdash; <?php esc_html_e( 'Plugin CSS classes for panel positioning and fade animation.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'URL behaviour', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Clicking a tab updates the URL hash with history.pushState.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Loading /page#specs opens the matching tab.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Loading without a hash opens the default tab without changing the URL.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Browser back and forward sync tabs via popstate and hashchange.', 'tms-core-essentials' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "\$group_id = wp_unique_id( 'tcres-tab-' );\n\$tabs = array(\n\t'specs'    => __( 'Specs', 'my-theme' ),\n\t'reviews'  => __( 'Reviews', 'my-theme' ),\n);\n?>\n<nav class=\"tcres-tabs\" data-tcres-tabs role=\"tablist\" aria-label=\"<?php echo esc_attr( __( 'Product details', 'my-theme' ) ); ?>\">\n\t<?php foreach ( \$tabs as \$tab_id => \$label ) : ?>\n\t\t<button type=\"button\"\n\t\t\tid=\"<?php echo esc_attr( \$group_id . '-tab-' . \$tab_id ); ?>\"\n\t\t\tclass=\"tcres-tab\"\n\t\t\tdata-tcres-tab-id=\"<?php echo esc_attr( \$tab_id ); ?>\"\n\t\t\t<?php if ( \$tab_id === 'specs' ) echo ' data-tcres-tab-default'; ?>\n\t\t\trole=\"tab\"\n\t\t\ttabindex=\"-1\"\n\t\t\taria-selected=\"false\"\n\t\t\taria-controls=\"<?php echo esc_attr( \$group_id . '-panel-' . \$tab_id ); ?>\">\n\t\t\t<?php echo esc_html( \$label ); ?>\n\t\t</button>\n\t<?php endforeach; ?>\n</nav>\n<div class=\"tcres-tab-panels\" data-tcres-tab-panels>\n\t<?php foreach ( \$tabs as \$tab_id => \$label ) : ?>\n\t\t<div\n\t\t\tid=\"<?php echo esc_attr( \$group_id . '-panel-' . \$tab_id ); ?>\"\n\t\t\tclass=\"tcres-tab-panel\"\n\t\t\trole=\"tabpanel\"\n\t\t\taria-labelledby=\"<?php echo esc_attr( \$group_id . '-tab-' . \$tab_id ); ?>\"\n\t\t\thidden>\n\t\t\t<p><?php echo esc_html( \$label ); ?> panel content.</p>\n\t\t</div>\n\t<?php endforeach; ?>\n</div>\n<?php\n// After AJAX:\nwindow.tcresTabsInit();" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Use button type="button" for tabs (recommended). Anchors with href="#tab-id" and the same data attributes also work. Style .tcres-tab in the theme; the plugin ships only structural panel CSS.', 'tms-core-essentials' ); ?></p>
			</section>
		</main>

		<aside>
			<nav class="tcres-settings-toc" aria-labelledby="tcres-developers-toc-title">
				<h2 id="tcres-developers-toc-title"><?php esc_html_e( 'Contents', 'tms-core-essentials' ); ?></h2>

				<section aria-labelledby="tcres-toc-data">
					<h3 id="tcres-toc-data"><a href="#tcres-dev-data"><?php esc_html_e( 'Data', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-field-get"><?php esc_html_e( 'Get post fields', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-tax-field-get"><?php esc_html_e( 'Get term fields', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-option-get"><?php esc_html_e( 'Get options', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-discovery">
					<h3 id="tcres-toc-discovery"><a href="#tcres-dev-discovery"><?php esc_html_e( 'Options / discovery', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-option-get-for-post-types"><?php esc_html_e( 'Get post types by option', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-option-get-for-taxonomies"><?php esc_html_e( 'Get taxonomies by option', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-option-post-types-diff"><?php esc_html_e( 'Compare post types across options', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-post-types-included"><?php esc_html_e( 'Get included post types', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-taxonomies-included"><?php esc_html_e( 'Get included taxonomies', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-media">
					<h3 id="tcres-toc-media"><a href="#tcres-dev-media"><?php esc_html_e( 'Media', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-image-sizes"><?php esc_html_e( 'Get registered image sizes', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-image-validate-size"><?php esc_html_e( 'Validate image size', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-svg-icon"><?php esc_html_e( 'Get SVG icon', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-helpers">
					<h3 id="tcres-toc-helpers"><a href="#tcres-dev-helpers"><?php esc_html_e( 'Helpers', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-template-info"><?php esc_html_e( 'Get template information', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-slug-to-id"><?php esc_html_e( 'Convert slug to ID', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-date-format"><?php esc_html_e( 'Format dates', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-meta-write">
					<h3 id="tcres-toc-meta-write"><a href="#tcres-dev-meta-write"><?php esc_html_e( 'Meta write', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-post-meta-update"><?php esc_html_e( 'Update post meta', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-term-meta-update"><?php esc_html_e( 'Update term meta', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-filters">
					<h3 id="tcres-toc-filters"><a href="#tcres-dev-filters"><?php esc_html_e( 'Filters', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-filter-post-types-exclude"><?php esc_html_e( 'Exclude post types from the included list', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-filter-post-types-included"><?php esc_html_e( 'Modify included post types', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-filter-post-meta-sanitize"><?php esc_html_e( 'Sanitize meta before save', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-js">
					<h3 id="tcres-toc-js"><a href="#tcres-dev-js"><?php esc_html_e( 'JavaScript', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-collapse"><?php esc_html_e( 'Collapse', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-tabs"><?php esc_html_e( 'Tabs', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>
			</nav>
		</aside>
	</div>
	<?php
}
