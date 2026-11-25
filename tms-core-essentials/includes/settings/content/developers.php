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
				<?php esc_html_e( 'Read post meta, term meta, and plugin settings.', 'tms-core-essentials' ); ?>
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
						&mdash; <?php esc_html_e( 'Output format: empty (no formatting), esc_html, html (wp_kses_post), or wpautop (wp_kses_post + wpautop). Shortcodes default to esc_html.', 'tms-core-essentials' ); ?>
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
				<p><?php esc_html_e( 'Reads a plugin settings group from tcres_settings. Returns null when $name is not a known settings group or the key is missing.', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Full group: tcres_option_get( \'security\' ). Keys with null values in settings count as existing.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$name' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Plugin settings group name.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$key' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Key within the group. If empty, returns the full group.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$format' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Output format: empty (no formatting), esc_html, html (wp_kses_post), wysiwyg_title (tcres_clean_wysiwyg_title), or wpautop.', 'tms-core-essentials' ); ?>
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
				<p class="description"><?php esc_html_e( 'Classes: tcres-svg-icon svg-icon svg-icon-inline (if inline) svg-icon-{icon-id}.', 'tms-core-essentials' ); ?></p>
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
						&mdash; <?php esc_html_e( 'Full URL to the sprite .svg. Empty uses the frontend or admin sprite URL, then the plugin sprite. See Assets, libraries, and shortcodes.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'inline\']' ); ?></code>
						(<?php echo esc_html( 'bool' ); ?><?php esc_html_e( ', optional, default false', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'If true, adds the class svg-icon-inline.', 'tms-core-essentials' ); ?>
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
				<p><code><?php echo esc_html( 'tcres_template_get_info( string $post_type = \'page\' ): array' ); ?></code></p>
				<p><?php esc_html_e( 'Returns page templates registered by the active theme for a post type.', 'tms-core-essentials' ); ?></p>
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

			<hr>

			<section id="tcres-dev-clean-wysiwyg-title">
				<h3>⇒ <?php esc_html_e( 'Clean WYSIWYG title', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcres_clean_wysiwyg_title( string $title ): string' ); ?></code></p>
				<p><?php esc_html_e( 'Prepares WYSIWYG title HTML for frontend output: converts paragraphs to line breaks and allows only safe inline tags (strong, em, links, etc.).', 'tms-core-essentials' ); ?></p>
				<p class="description"><?php esc_html_e( 'Use on output only. When saving from the admin, use tcres_post_meta_update( ..., \'html\' ) for wp_kses_post sanitization; call this function when echoing the value.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$title' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Raw HTML from a WYSIWYG title field.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "echo tcres_clean_wysiwyg_title( get_post_meta( get_the_ID(), 'my_title', true ) );" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Also available via tcres_option_get( $name, $key, \'wysiwyg_title\' ). Returns: sanitized HTML ready to print, or an empty string.', 'tms-core-essentials' ); ?></p>
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
						<code>.tcres-collapse</code>, <code>.title</code>, <code>.content</code>
						&mdash; <?php esc_html_e( 'CSS classes for the default open/close animation. Toggle state uses .is-open. Keep the title label as plain/inline text (no wrapping block tags).', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "\$id = tcres_unique_id( 'tcres-collapse-' );\n\n<div class=\"tcres-collapse\">\n\t<div\n\t\tclass=\"title\"\n\t\trole=\"button\"\n\t\ttabindex=\"0\"\n\t\taria-expanded=\"false\"\n\t\tdata-tcres-collapse-trigger\n\t\tdata-tcres-collapse-id=\"{id}\">\n\t\tShow details\n\t</div>\n\t<div\n\t\tclass=\"content\"\n\t\tdata-tcres-collapse-target\n\t\tdata-tcres-collapse-id=\"{id}\">\n\t\t<p>Hidden until opened.</p>\n\t</div>\n</div>\n\n// Replace {id} with esc_attr( \$id ). After AJAX:\nwindow.tcresCollapseInit();" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Generate unique IDs in PHP with tcres_unique_id(). Frontend scripts expose window.tcresCollapseInit for manual re-init.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-modal">
				<h3>⇒ <?php esc_html_e( 'Modal', 'tms-core-essentials' ); ?></h3>
				<p>
					<code><?php echo esc_html( 'tcresShowModal( target )' ); ?></code>,
					<code><?php echo esc_html( 'tcresHideModal( target )' ); ?></code>,
					<code><?php echo esc_html( 'tcresModalInit( root = document )' ); ?></code>
				</p>
				<p><?php esc_html_e( 'Reusable modal dialog. Open imperatively with tcresShowModal() or declaratively with data-tcres-modal-open. The plugin calls tcresModalInit() on DOMContentLoaded; call it again after injecting markup dynamically.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Markup', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code>.tcres-modal</code>
						&mdash; <?php esc_html_e( 'Root overlay. Hidden until .is-open is added.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.backdrop</code>
						&mdash; <?php esc_html_e( 'Full-screen backdrop inside the modal.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.dialog</code>
						&mdash; <?php esc_html_e( 'Centered panel with role="dialog" and aria-modal="true".', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.header</code>, <code>.body</code>, <code>.footer</code>
						&mdash; <?php esc_html_e( 'Optional layout regions. Style them in the theme.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-modal-close</code>
						&mdash; <?php esc_html_e( 'Closes the nearest .tcres-modal (backdrop, × button, Cancel, etc.).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-modal-open</code>
						&mdash; <?php esc_html_e( 'On a button: modal id (#my-modal) or selector to open on click.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.is-static</code>
						&mdash; <?php esc_html_e( 'Panel appears in place with no panel fade (backdrop may still fade).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.from-top</code>, <code>.from-right</code>, <code>.from-bottom</code>, <code>.from-left</code>
						&mdash; <?php esc_html_e( 'Slide the panel in from off-screen (no panel fade).', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.from-top-fade</code>, <code>.from-right-fade</code>, <code>.from-bottom-fade</code>, <code>.from-left-fade</code>
						&mdash; <?php esc_html_e( 'Same directions with panel fade while sliding.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.is-full-screen</code>
						&mdash; <?php esc_html_e( 'Optional layout modifier: the dialog fills the viewport. Combine with any .from-* animation class.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>.is-open</code>, <code>.is-entering</code>, <code>.is-leaving</code>
						&mdash; <?php esc_html_e( 'State classes toggled by the script. Closed modals are not clickable; enter/exit animations run under .is-entering / .is-leaving before the modal settles.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>tcres-modal-hide</code>
						&mdash; <?php esc_html_e( 'Custom event dispatched on the modal element after it closes.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<h4><?php esc_html_e( 'Animations', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Static fade (default): no extra class; the panel fades in and out.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Static, no fade: .is-static — panel appears in place.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Slide: .from-top, .from-right, .from-bottom or .from-left — panel slides in from off-screen while the backdrop fades.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Slide + fade: .from-top-fade, .from-right-fade, .from-bottom-fade or .from-left-fade — panel slides from off-screen and fades at the same time.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Add one animation class on the same element as .tcres-modal, then open it from any trigger with data-tcres-modal-open.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Combine with .is-full-screen for a full-viewport panel using the same animation classes.', 'tms-core-essentials' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Behaviour', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Escape closes the topmost open modal.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Tab cycles focus inside the open modal.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'body.tcres-is-modal-open blocks page scroll while a modal is open.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Focus returns to the element that was focused before open.', 'tms-core-essentials' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "<button type=\"button\" data-tcres-modal-open=\"my-modal\">Open</button>\n\n<div class=\"tcres-modal\" id=\"my-modal\" aria-hidden=\"true\">\n\t<div class=\"backdrop\" data-tcres-modal-close></div>\n\t<div class=\"dialog\"\n\t\trole=\"dialog\"\n\t\taria-modal=\"true\"\n\t\taria-labelledby=\"my-modal-title\">\n\t\t<div class=\"header\">\n\t\t\t<p class=\"title\" id=\"my-modal-title\">Title</p>\n\t\t\t<button type=\"button\"\n\t\t\t\tclass=\"close\"\n\t\t\t\tdata-tcres-modal-close\n\t\t\t\taria-label=\"Close\">&times;</button>\n\t\t</div>\n\t\t<div class=\"body\">\n\t\t\t<p>Content.</p>\n\t\t</div>\n\t\t<div class=\"footer\">\n\t\t\t<button type=\"button\" data-tcres-modal-close>Cancel</button>\n\t\t</div>\n\t</div>\n</div>\n\n// Imperative:\nwindow.tcresShowModal( document.getElementById( 'my-modal' ) );\nwindow.tcresHideModal( '#my-modal' );\n\n// After AJAX:\nwindow.tcresModalInit();" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Example with slide + fade from bottom: tcres-modal from-bottom-fade. Full viewport: tcres-modal is-full-screen from-bottom-fade. Override colors and spacing in the theme.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-query-listing">
				<h3>⇒ <?php esc_html_e( 'Query listing', 'tms-core-essentials' ); ?></h3>
				<p>
					<code><?php echo esc_html( 'tcres_query_listing_register( string $id, array $args ): void' ); ?></code>,
					<code><?php echo esc_html( 'tcres_query_listing_attrs( string $id ): void' ); ?></code>,
					<code><?php echo esc_html( 'tcres_query_listing_items( string $id ): void' ); ?></code>,
					<code><?php echo esc_html( 'tcresQueryListingInit( root = document )' ); ?></code>
				</p>
				<p><?php esc_html_e( 'AJAX post listing. Register the query in PHP. The theme writes the grid, the toolbar, and the navigation, and chooses which controls exist. tcres_query_listing_attrs() prints the signed attributes on the theme grid, and tcres_query_listing_items() prints the cards inside it. The pieces share data-tcres-query-listing-id and do not need a common parent, so the toolbar can live in an offcanvas. Several toolbars with the same id stay in sync. The plugin calls tcresQueryListingInit() on DOMContentLoaded; call it again after injecting a listing dynamically.', 'tms-core-essentials' ); ?></p>

				<h4><?php esc_html_e( 'Register', 'tms-core-essentials' ); ?></h4>
				<p><?php esc_html_e( 'Call tcres_query_listing_register() where the listing is printed, after the post type exists. The signed config carries the query, the order, and the card, so AJAX does not need another register call. If the post type does not exist yet, register does nothing.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Parameters', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code><?php echo esc_html( '$id' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Listing id shared by the toolbar, the grid, and the navigation.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'post_type\']' ); ?></code>
						(<?php echo esc_html( 'string|array' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Public post types this listing may query. The visitor can only narrow this list.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'posts_per_page\']' ); ?></code>
						(<?php echo esc_html( 'int' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Items per page when the listing uses load more or pagination. Omit it to show every post, which is what happens without that navigation. A value from 1 to 100 limits the page; larger values become 100.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'orderby\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Initial orderby: date, title, or menu_order. Default date.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'order\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Initial direction: ASC or DESC. Default DESC.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'taxonomies\']' ); ?></code>
						(<?php echo esc_html( 'array' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Public taxonomies attached to those post types. Visitor taxonomy and term filters can only use this list. Scope is not limited by it.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'scope\']' ); ?></code>
						(<?php echo esc_html( 'array' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Locked query. relation is AND (default) or OR, and it only combines these clauses with each other. A filter is type taxonomy (values are taxonomy slugs; in and and mean the post has a term in that taxonomy, not_in means it has none) or type term (taxonomy, operator in, and, or not_in, values are term slugs). The visitor cannot remove it. Visitor filters are added with AND, so a toolbar OR cannot leave it. Omit it for no locked terms. The first page and the base total use this query, before any toolbar control.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'empty\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'HTML for an empty result, written by the theme. It is stored in a template, so it stays out of the indexed body while there are posts. If the first query is already empty, it is also printed as real content.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'error\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', optional', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'HTML shown when an AJAX request fails, written by the theme. Posts already on screen stay in place.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code><?php echo esc_html( '$args[\'render_item\']' ); ?></code>
						(<?php echo esc_html( 'string' ); ?><?php esc_html_e( ', required', 'tms-core-essentials' ); ?>)
						&mdash; <?php esc_html_e( 'Card for every item. A theme template slug (get_template_part, child theme then parent, any folder) or an existing .php file under wp-content. The loop is set up, so the_title() and the_permalink() work. The card root must have data-tcres-query-listing-item. The value is signed with the query and is not taken from the AJAX request.', 'tms-core-essentials' ); ?>
					</li>
				</ul>

				<h4><?php esc_html_e( 'Grid', 'tms-core-essentials' ); ?></h4>
				<p><?php esc_html_e( 'The theme writes the grid element, with its tag, classes, and any other attributes. Call tcres_query_listing_attrs() in the opening tag. It prints the listing id, the AJAX URL, the signed config, and the first-page totals. Call tcres_query_listing_items() inside the element. It prints the loading marker, the empty and error templates, and the cards of the registered query. Scope applies. A checked toolbar control does not, until the visitor changes a control. A later request removes any other child of that element.', 'tms-core-essentials' ); ?></p>

				<h4><?php esc_html_e( 'Markup', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code>data-tcres-query-listing-grid</code>
						&mdash; <?php esc_html_e( 'Theme grid element. tcres_query_listing_attrs() prints this flag together with the id, the AJAX URL, the signed config, the total, and the page count.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-animate</code>
						&mdash; <?php esc_html_e( 'enter animates new items in. both also animates the current items out. Leave it empty for an instant swap.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-step</code>
						&mdash; <?php esc_html_e( 'Milliseconds between items. Default 60. Maximum 1000.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-toolbar</code>
						&mdash; <?php esc_html_e( 'Toolbar root. Pair it with data-tcres-query-listing-id. Include only the filters, the search field, the order select, and the count you want. With no order select, the order from register is used.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-relation</code>
						&mdash; <?php esc_html_e( 'AND (default) or OR. On the toolbar it combines post type filters, taxonomy filters, and the terms block. On the terms wrapper it combines the term clauses.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-filter</code>
						&mdash; <?php esc_html_e( 'Filter control. The value is post_type, taxonomy, or term. Use a select, a multiple select, checkboxes, or a mix. An empty select value, or no checked box, means the filter is not applied. The values inside one control are a single clause.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-taxonomy</code>
						&mdash; <?php esc_html_e( 'Taxonomy slug. Required when the filter value is term.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-operator</code>
						&mdash; <?php esc_html_e( 'in (default, any), and (all; terms only), or not_in (none). post_type accepts only in and not_in. For a taxonomy filter, in means the post has a term in that taxonomy, and not_in means it has none.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-terms</code>
						&mdash; <?php esc_html_e( 'Wrapper around the term filters. Set data-tcres-query-listing-relation on it. Default AND.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-search</code>
						&mdash; <?php esc_html_e( 'Search field. An empty value is not applied. The text narrows the registered query and the active filters. WordPress matches the title, the content, and the excerpt. The count gets is-filtered while the field has text. The query runs when the field is committed: Enter, leaving the field, or clearing it.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-order</code>
						&mdash; <?php esc_html_e( 'Order select. Option values are date|DESC, date|ASC, title|ASC, title|DESC, menu_order|ASC, and menu_order|DESC.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-count</code>
						&mdash; <?php esc_html_e( 'Result count. Put data-tcres-query-listing-shown, data-tcres-query-listing-sep, and data-tcres-query-listing-total inside it. From the first paint the count is shown / total. Shown is how many cards are in the grid. The total is the registered base total: scope counts, visitor filters do not. The count gets is-filtered only while a visitor filter is active. Changing the order alone does not add that class.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-nav</code>
						&mdash; <?php esc_html_e( 'The pagination nav or the load-more button. Add data-tcres-query-listing-id on that same element. The nav also has data-tcres-query-listing-pagination. The button also has data-tcres-query-listing-more. On a single page the element is hidden.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-more</code>
						&mdash; <?php esc_html_e( 'Button that appends the next page. Hidden when there is no next page. is-loading goes on this button while the next page loads. The grid shows its loading state too.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-pagination</code>
						&mdash; <?php esc_html_e( 'Pagination nav. It is a previous link, an ordered list with one item per page, and a next link. Each link has data-tcres-query-listing-page set to prev, next, or the page number. The current link uses is-current and aria-current="page". An unavailable link uses aria-disabled="true". The sample number href is reused for every page: a {page} token is replaced, otherwise the path segment equal to the sample page is replaced. A filter or an order change goes back to page 1 and replaces the grid. Previous and next labels stay; the sample item is cloned to match the new page count.', 'tms-core-essentials' ); ?>
					</li>
					<li>
						<code>data-tcres-query-listing-item</code>
						&mdash; <?php esc_html_e( 'Required on the root element of the card.', 'tms-core-essentials' ); ?>
					</li>
				</ul>

				<h4><?php esc_html_e( 'Behaviour', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'A request that changes the grid (filter, order, pagination, or load more) sets is-loading on the grid and shows the element with data-tcres-query-listing-loading. Load more also sets is-loading on its button. Controls stay disabled until the request ends. A newer request aborts the one in flight.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Animation classes on each item: is-entering, then is-visible. Leaving items use is-leaving. prefers-reduced-motion skips straight to is-visible. The first paint is not animated. The plugin CSS is only a neutral fade on the item; layout, and how is-unavailable looks, belong to the theme.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'After each response, an option with no matches gets is-unavailable on its checkbox label and on the Choices.js item (.choices__item[data-value]). The script does not disable or hide it. The whole filter gets is-unavailable when every real option is unavailable. Each filter is tested without its own current values. Only the first 50 options are checked; any option after those is marked unavailable. If a selected value is no longer available, that filter is cleared and the listing runs once more. Start Choices.js in the theme; the script reads the native control.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Empty and error HTML are the theme strings passed to register. AJAX does not rebuild them, so they stay in the language of the page.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'The signed config stores the current language. AJAX switches to that language before the query, for WPML and for Polylang. Term slugs are used as the theme rendered them.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'The signature keeps the query inside the registered post types, taxonomies, scope, page size, order, card, language, and base total. Only published, viewable content is queried.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'New cards initialize collapse, tabs, and modal again when those helpers are loaded. If the plugin frontend JavaScript is disabled in Extras, the listing stays on the first page printed by PHP. This version does not copy filters into the URL.', 'tms-core-essentials' ); ?></li>
				</ul>

				<h4><?php esc_html_e( 'Filter parameters', 'tms-core-essentials' ); ?></h4>
				<p><code><?php echo esc_html( 'tcres_query_listing_orderby' ); ?></code></p>
				<ul>
					<li>
						<code><?php echo esc_html( '$allowed' ); ?></code>
						(<?php echo esc_html( 'array<string>' ); ?>)
						&mdash; <?php esc_html_e( 'Whitelist of orderby values for the order control. date, title, and menu_order are included by default. Add the matching option in the select, for example modified|DESC.', 'tms-core-essentials' ); ?>
					</li>
				</ul>
				<p class="description"><?php esc_html_e( 'Returns: the array you return is the accepted orderby list. A non-array, or an empty result, keeps date and title.', 'tms-core-essentials' ); ?></p>

				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "tcres_query_listing_register( 'news', array(\n\t\t'post_type'      => array( 'post', 'project' ),\n\t\t'posts_per_page' => 12,\n\t\t'orderby'        => 'date',\n\t\t'order'          => 'DESC',\n\t\t'taxonomies'     => array( 'category', 'project_type' ),\n\t\t'scope'          => array(\n\t\t\t'relation' => 'AND',\n\t\t\t'filters'  => array(\n\t\t\t\tarray(\n\t\t\t\t\t'type'     => 'term',\n\t\t\t\t\t'taxonomy' => 'category',\n\t\t\t\t\t'operator' => 'in',\n\t\t\t\t\t'values'   => array( 'news' ),\n\t\t\t\t),\n\t\t\t),\n\t\t),\n\t\t'empty'          => '<p>' . esc_html__( 'No results.', 'my-theme' ) . '</p>',\n\t\t'error'          => '<p>' . esc_html__( 'Could not load.', 'my-theme' ) . '</p>',\n\t\t'render_item'    => 'blocks/cards/project',\n\t) );\n\n<search\n\tclass=\"query-listing\"\n\tdata-tcres-query-listing-toolbar\n\tdata-tcres-query-listing-id=\"news\"\n\tdata-tcres-query-listing-relation=\"AND\">\n\t<div\n\t\tdata-tcres-query-listing-filter=\"post_type\"\n\t\tdata-tcres-query-listing-operator=\"in\">\n\t\t<select>\n\t\t\t<option value=\"\">All types</option>\n\t\t\t<option value=\"post\">Posts</option>\n\t\t</select>\n\t</div>\n\t<div\n\t\tdata-tcres-query-listing-terms\n\t\tdata-tcres-query-listing-relation=\"AND\">\n\t\t<div\n\t\t\tdata-tcres-query-listing-filter=\"term\"\n\t\t\tdata-tcres-query-listing-taxonomy=\"category\"\n\t\t\tdata-tcres-query-listing-operator=\"in\">\n\t\t\t<label><input type=\"checkbox\" value=\"news\"> News</label>\n\t\t</div>\n\t</div>\n\t<input type=\"search\" data-tcres-query-listing-search placeholder=\"Search\">\n\t<select data-tcres-query-listing-order>\n\t\t<option value=\"date|DESC\">Newest</option>\n\t\t<option value=\"title|ASC\">A–Z</option>\n\t\t<option value=\"menu_order|ASC\">Manual</option>\n\t</select>\n\t<p data-tcres-query-listing-count>\n\t\t<span data-tcres-query-listing-shown></span>\n\t\t<span data-tcres-query-listing-sep> / </span>\n\t\t<span data-tcres-query-listing-total></span>\n\t</p>\n</search>\n\n<div\n\tclass=\"query-listing grid\"\n\tdata-tcres-query-listing-animate=\"enter\"\n\tdata-tcres-query-listing-step=\"60\"\n\t<?php tcres_query_listing_attrs( 'news' ); ?>>\n\t<?php tcres_query_listing_items( 'news' ); ?>\n</div>\n\n<nav\n\tclass=\"query-listing\"\n\tdata-tcres-query-listing-nav\n\tdata-tcres-query-listing-id=\"news\"\n\tdata-tcres-query-listing-pagination\n\taria-label=\"Pagination\">\n\t<a href=\"/page/1/\" rel=\"prev\" data-tcres-query-listing-page=\"prev\">Previous</a>\n\t<ol>\n\t\t<li><a href=\"/page/1/\" data-tcres-query-listing-page=\"1\">1</a></li>\n\t</ol>\n\t<a href=\"/page/2/\" rel=\"next\" data-tcres-query-listing-page=\"next\">Next</a>\n</nav>\n\n// Load more, instead of the pagination nav:\n// <button type=\"button\" class=\"query-listing\" data-tcres-query-listing-nav data-tcres-query-listing-id=\"news\" data-tcres-query-listing-more>Load more</button>\n\n// Card root: <article data-tcres-query-listing-item>\n// Plugin file: 'render_item' => plugin_dir_path( __FILE__ ) . 'views/card.php'" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Use the theme text domain on the empty and error HTML. A file path passed as render_item must live under wp-content.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-tabs">
				<h3>⇒ <?php esc_html_e( 'Tabs', 'tms-core-essentials' ); ?></h3>
				<p><code><?php echo esc_html( 'tcresTabsInit( root = document )' ); ?></code></p>
				<p><?php esc_html_e( 'Reusable tab panels with URL hash sync. The tab list must be immediately followed by the panels wrapper. The plugin calls tcresTabsInit() on DOMContentLoaded; call it again after injecting markup dynamically.', 'tms-core-essentials' ); ?></p>
				<h4><?php esc_html_e( 'Markup', 'tms-core-essentials' ); ?></h4>
				<ul>
					<li>
						<code>data-tcres-tabs</code>
						&mdash; <?php esc_html_e( 'Tab list container (a div with role="tablist").', 'tms-core-essentials' ); ?>
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
						<code>.tcres-tab-panels</code>, <code>.panel</code>
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
				<pre><code><?php echo esc_html( "\$group_id = wp_unique_id( 'tcres-tab-' );\n\$tabs = array(\n\t'specs'    => __( 'Specs', 'my-theme' ),\n\t'reviews'  => __( 'Reviews', 'my-theme' ),\n);\n?>\n<div class=\"tcres-tabs\" data-tcres-tabs role=\"tablist\" aria-label=\"<?php echo esc_attr( __( 'Product details', 'my-theme' ) ); ?>\">\n\t<?php foreach ( \$tabs as \$tab_id => \$label ) : ?>\n\t\t<button type=\"button\"\n\t\t\tid=\"<?php echo esc_attr( \$group_id . '-tab-' . \$tab_id ); ?>\"\n\t\t\tclass=\"tab\"\n\t\t\tdata-tcres-tab-id=\"<?php echo esc_attr( \$tab_id ); ?>\"\n\t\t\t<?php if ( \$tab_id === 'specs' ) echo ' data-tcres-tab-default'; ?>\n\t\t\trole=\"tab\"\n\t\t\ttabindex=\"-1\"\n\t\t\taria-selected=\"false\"\n\t\t\taria-controls=\"<?php echo esc_attr( \$group_id . '-panel-' . \$tab_id ); ?>\">\n\t\t\t<?php echo esc_html( \$label ); ?>\n\t\t</button>\n\t<?php endforeach; ?>\n</div>\n<div class=\"tcres-tab-panels\" data-tcres-tab-panels>\n\t<?php foreach ( \$tabs as \$tab_id => \$label ) : ?>\n\t\t<div\n\t\t\tid=\"<?php echo esc_attr( \$group_id . '-panel-' . \$tab_id ); ?>\"\n\t\t\tclass=\"panel\"\n\t\t\trole=\"tabpanel\"\n\t\t\taria-labelledby=\"<?php echo esc_attr( \$group_id . '-tab-' . \$tab_id ); ?>\"\n\t\t\thidden>\n\t\t\t<p><?php echo esc_html( \$label ); ?> panel content.</p>\n\t\t</div>\n\t<?php endforeach; ?>\n</div>\n<?php\n// After AJAX:\nwindow.tcresTabsInit();" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'Use button type="button" for tabs (recommended). Anchors with href="#tab-id" and the same data attributes also work. Style .tab in the theme; the plugin ships only structural panel CSS.', 'tms-core-essentials' ); ?></p>
			</section>

			<h2 id="tcres-dev-assets"><?php esc_html_e( 'Assets, libraries, and shortcodes', 'tms-core-essentials' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'These options are on the Extras screen. Each switch is independent.', 'tms-core-essentials' ); ?>
			</p>

			<?php
			$tcres_dev_libraries = function_exists( 'tcres_external_scripts_get_libraries' )
				? tcres_external_scripts_get_libraries()
				: array();
			$tcres_dev_lenis = function_exists( 'tcres_smooth_scroll_get_vendor_assets' )
				? tcres_smooth_scroll_get_vendor_assets()
				: array( 'version' => '' );
			?>

			<section id="tcres-dev-frontend-assets">
				<h3>⇒ <?php esc_html_e( 'Frontend assets', 'tms-core-essentials' ); ?></h3>
				<p><?php esc_html_e( 'Disable frontend CSS skips the plugin stylesheet on the public site (assets/css/styles.css, handle tcres-styles) and the login stylesheet. Disable frontend JS skips assets/js/scripts.js (handle tcres-scripts) and the login script. Collapse, tabs, modals, and the query listing need that script. Swiper, GLightbox, Choices, and smooth scroll follow their own switches.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-libraries">
				<h3>⇒ <?php esc_html_e( 'Swiper, GLightbox, and Choices', 'tms-core-essentials' ); ?></h3>
				<p><?php esc_html_e( 'Each switch registers one bundled library on public pages. The admin and the login screen do not register them. With the switch off, that handle stays unregistered and nothing prints.', 'tms-core-essentials' ); ?></p>
				<p><?php esc_html_e( 'With the switch on, the files stay registered and print when a queued script is that handle, or depends on it, including through another dependency. CSS uses the same handle. This plugin prints the stylesheet when it sees the script handle, so the theme only declares the script dependency.', 'tms-core-essentials' ); ?></p>
				<p><?php esc_html_e( 'Initialization stays in the theme. After the script prints, use window.Swiper, window.GLightbox, or window.Choices from a script that lists the matching handle in its dependencies.', 'tms-core-essentials' ); ?></p>
				<ul>
					<?php if ( isset( $tcres_dev_libraries['swiper'] ) ) : ?>
						<li>
							<?php
							echo esc_html( sprintf(
								/* translators: 1: library version, 2: script handle */
								__( 'Swiper %1$s. Handle %2$s. Global window.Swiper.', 'tms-core-essentials' ),
								$tcres_dev_libraries['swiper']['version'],
								$tcres_dev_libraries['swiper']['handle_js']
							) );
							?>
						</li>
					<?php endif; ?>
					<?php if ( isset( $tcres_dev_libraries['glightbox'] ) ) : ?>
						<li>
							<?php
							echo esc_html( sprintf(
								/* translators: 1: library version, 2: script handle */
								__( 'GLightbox %1$s. Handle %2$s. Global window.GLightbox.', 'tms-core-essentials' ),
								$tcres_dev_libraries['glightbox']['version'],
								$tcres_dev_libraries['glightbox']['handle_js']
							) );
							?>
						</li>
					<?php endif; ?>
					<?php if ( isset( $tcres_dev_libraries['choices'] ) ) : ?>
						<li>
							<?php
							echo esc_html( sprintf(
								/* translators: 1: library version, 2: script handle */
								__( 'Choices %1$s. Handle %2$s. Global window.Choices. The query listing can mark an existing Choices control. The theme still starts Choices and depends on this handle.', 'tms-core-essentials' ),
								$tcres_dev_libraries['choices']['version'],
								$tcres_dev_libraries['choices']['handle_js']
							) );
							?>
						</li>
					<?php endif; ?>
				</ul>
				<h4><?php esc_html_e( 'Example', 'tms-core-essentials' ); ?></h4>
				<pre><code><?php echo esc_html( "wp_enqueue_script(\n\t'my-theme',\n\tget_stylesheet_directory_uri() . '/assets/js/theme.js',\n\tarray( 'tcres-swiper', 'tcres-glightbox' ),\n\tnull,\n\ttrue\n);" ); ?></code></pre>
				<p class="description"><?php esc_html_e( 'wp_enqueue_script( \'tcres-swiper\' ) also prints that library. The same pattern uses tcres-glightbox and tcres-choices.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-smooth-scroll">
				<h3>⇒ <?php esc_html_e( 'Smooth scroll', 'tms-core-essentials' ); ?></h3>
				<p>
					<?php
					echo esc_html( sprintf(
						/* translators: %s: Lenis version */
						__( 'Lenis %s prints on every public page while the switch is on, and the plugin starts it. Handle tcres-smooth-scroll. The instance is window.tcresSmoothScrollInstance.', 'tms-core-essentials' ),
						isset( $tcres_dev_lenis['version'] )
							? (string) $tcres_dev_lenis['version']
							: ''
					) );
					?>
				</p>
				<ul>
					<li><?php esc_html_e( 'Lerp, from 0.01 to 1. Default 0.05. Lower is smoother and heavier.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Smooth mouse wheel, smooth touch scrolling, and smooth in-page anchor links.', 'tms-core-essentials' ); ?></li>
					<li><?php esc_html_e( 'Exclude selectors: one CSS selector per line. Commas are accepted. Each match gets data-lenis-prevent. A theme can also put that attribute in the markup. The default list is .swiper.', 'tms-core-essentials' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Filter tcres_smooth_scroll_config runs before the config is printed. The first argument is the config array. The second is the saved group. Return an array; any other return value is ignored. The printed config is window.tcresSmoothScroll.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-svg-sprite">
				<h3>⇒ <?php esc_html_e( 'SVG sprite', 'tms-core-essentials' ); ?></h3>
				<p><?php esc_html_e( 'Admin and Frontend are the full URL of the sprite used by tcres_svg_icon_get() when the call does not pass file. Empty uses the plugin sprite, assets/images/icons.svg. The admin URL is used in the admin. The frontend URL is used on the public site.', 'tms-core-essentials' ); ?></p>
			</section>

			<hr>

			<section id="tcres-dev-shortcodes">
				<h3>⇒ <?php esc_html_e( 'Shortcodes', 'tms-core-essentials' ); ?></h3>
				<p><?php esc_html_e( 'The switch registers the shortcodes below. Shortcodes that belong to a module, such as breadcrumbs or share buttons, stay with that module and ignore this switch.', 'tms-core-essentials' ); ?></p>
				<ul>
					<li><code>[tcres-field]</code> &mdash; <?php esc_html_e( 'Print a post custom field. Optional format: esc_html (default), html, wpautop.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-tax-field]</code> &mdash; <?php esc_html_e( 'Print a taxonomy term custom field. Optional format: esc_html (default), html, wpautop.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-option]</code> &mdash; <?php esc_html_e( 'Print a plugin settings value. Optional format: esc_html (default), html, wysiwyg_title, wpautop.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-button]</code> &mdash; <?php esc_html_e( 'Link styled as a button. Optional: class, title, blank="yes".', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-highlighted]</code> &mdash; <?php esc_html_e( 'Wrap content in highlighted markup.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-svgicon]</code> &mdash; <?php esc_html_e( 'SVG icon from the sprite.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-youtube]</code> &mdash; <?php esc_html_e( 'YouTube embed by video ID.', 'tms-core-essentials' ); ?></li>
					<li><code>[tcres-vimeo]</code> &mdash; <?php esc_html_e( 'Vimeo embed by video ID.', 'tms-core-essentials' ); ?></li>
				</ul>
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
						<li><a href="#tcres-dev-clean-wysiwyg-title"><?php esc_html_e( 'Clean WYSIWYG title', 'tms-core-essentials' ); ?></a></li>
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
						<li><a href="#tcres-dev-modal"><?php esc_html_e( 'Modal', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-query-listing"><?php esc_html_e( 'Query listing', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-tabs"><?php esc_html_e( 'Tabs', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>

				<hr>

				<section aria-labelledby="tcres-toc-assets">
					<h3 id="tcres-toc-assets"><a href="#tcres-dev-assets"><?php esc_html_e( 'Assets, libraries, and shortcodes', 'tms-core-essentials' ); ?></a></h3>
					<ul>
						<li><a href="#tcres-dev-frontend-assets"><?php esc_html_e( 'Frontend assets', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-libraries"><?php esc_html_e( 'Swiper, GLightbox, and Choices', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-smooth-scroll"><?php esc_html_e( 'Smooth scroll', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-svg-sprite"><?php esc_html_e( 'SVG sprite', 'tms-core-essentials' ); ?></a></li>
						<li><a href="#tcres-dev-shortcodes"><?php esc_html_e( 'Shortcodes', 'tms-core-essentials' ); ?></a></li>
					</ul>
				</section>
			</nav>
		</aside>
	</div>
	<?php
}
