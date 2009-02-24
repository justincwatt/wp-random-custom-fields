<?php

/*
Plugin Name: Random Custom Fields
Plugin URI: http://justinsomnia.org/2009/02/random-custom-fields/
Description: Display a random custom field in a custom template from one or more of your posts. Configure the plugin in <a href="options-general.php?page=random-custom-fields.php">Settings > Random Custom Fields</a>.
Version: 1.0
Author: Justin Watt
Author URI: http://justinsomnia.org/

INSTRUCTIONS

1) Save this file as random-custom-fields.php in /path/to/wordpress/wp-content/plugins/ 
2) Activate "random-custom-fields" from the Wordpress control panel. 
3) Add [?php random-custom-fields(); ?] to your index.php or sidebar.php template file
   in /path/to/wordpress/wp-content/themes/theme-name/ where you want the random custom fields info to appear
   (make sure to replace the square brackets [] above with angle brackets <>)

CHANGELOG

1.0
inital version, based on my random image plugin: http://justinsomnia.org/2005/09/random-image-plugin-for-wordpress/

LICENSE

random-custom-fields.php
Copyright (C) 2009 Justin Watt
justincwatt@gmail.com
http://justinsomnia.org/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// widgetization
function widget_random_custom_fields_init() {
  if (!function_exists('register_sidebar_widget')) {
		return;
	}

	function widget_random_custom_fields() {
		print '<li>' . "\n";
		random_custom_fields();
		print '</li>' . "\n";
	}

	register_sidebar_widget('Random Custom Fields', 'widget_random_custom_fields');
}

add_action('plugins_loaded', 'widget_random_custom_fields_init');


// add configuration page to WordPress
function random_custom_fields_add_page()
{
  add_options_page('Random Custom Fields', 'Random Custom Fields', 6, __FILE__, 'random_custom_fields_configuration_page');
}
add_action('admin_menu', 'random_custom_fields_add_page');


// helper function to set random_custom_fields defaults (if necessary)
// and return array of options
function get_random_custom_fields_options()
{
  $options = get_option('random_custom_fields_options');
  
  if (!isset($options["custom_field_name"])) {
    $options["custom_field_name"] = "";
  } 

  if (!isset($options["include_posts"])) {
    $options["include_posts"] = true;
  }
  
  if (!isset($options["include_pages"])) {
    $options["include_pages"] = false;
  }

  if (!isset($options["sort_randomly"])) {
    $options["sort_randomly"] = true;
  }

  if (!isset($options["count"])) {
    $options["count"] = 1;
  }

  if (!isset($options["value_separator"])) {
    $options["value_separator"] = "";
  }

  if (!isset($options["html_template"])) {
    $options["html_template"] = "%0";
  }

  if (!isset($options["html_between_templates"])) {
    $options["html_between_templates"] = "<br /><br />";
  }

  if (!isset($options["category_filter"])) {
    $options["category_filter"] = array();
  }

  add_option('random_custom_fields_options', $options);
  return $options;
}


// generate configuration page
function random_custom_fields_configuration_page()
{
  $options = get_random_custom_fields_options();

  // if form has been submitted, save values
  if (isset($_POST['action']) == 'update') {
    
    // correct for posts and pages and galleries being deselected
    if (!$_POST['include_posts'] && !$_POST['include_pages']) {
      $_POST['include_posts'] = true;
    }

    // correct for empty count
    if ($_POST['count'] < 1) {
      $_POST['count'] = 1;
    }

    if (trim($_POST['html_template']) == '') {
      $_POST['html_template'] = "%0";
    }

    if (!is_array($_POST['category_filter'])) {
      $_POST['category_filter'] = array();
    }

    // create array of new options
    $options = array(
      "custom_field_name"      => stripslashes($_POST['custom_field_name']),
      "include_posts"          => (boolean)$_POST['include_posts'],
      "include_pages"          => (boolean)$_POST['include_pages'],
      "sort_randomly"          => (boolean)$_POST['sort_randomly'],
      "count"                  => (int)$_POST['count'],
      "value_separator"        => stripslashes($_POST['value_separator']),
      "html_template"          => stripslashes($_POST['html_template']),
      "html_between_templates" => stripslashes($_POST['html_between_templates']),
      "category_filter"        => $_POST['category_filter']
    );

    update_option('random_custom_fields_options', $options);
  }

?>

<div class="wrap">
<div id="icon-options-general" class="icon32"></div>
<h2>Random Custom Fields Settings</h2>

<p><strong>Instructions:</strong> Use the following options to configure how you want the plugin to behave. The Sample Output at the bottom of the page will reflect the changes you've made. When you're satisfied, goto <a href="widgets.php">Design > Widgets</a> and add "Random Custom Fields" to your sidebar (if your theme is widget-enabled). Otherwise you can manually edit your index.php or sidebar.php template files and add <code style="font-weight:bold;">&lt?php random_custom_fields(); ?&gt</code> where you want the random custom fields to appear.

<form method="post">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">
<tbody>

<tr valign="top">
<th scope="row"><label for="custom_field_name">Custom field name:</label></th>
<td><input type="text" id="custom_field_name" name="custom_field_name" style="width:200px;" <?php if ($options["custom_field_name"]) print "value='" . stripslashes(htmlspecialchars($options["custom_field_name"], ENT_QUOTES)) . "'"; ?>/> <span class="setting-description">Required</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="include_posts">Include posts?</label></th>
<td><input type="checkbox" id="include_posts" name="include_posts" value="1" <?php if ($options["include_posts"]) print "checked='checked'"; ?>/></td>
</tr>

<tr valign="top">
<th scope="row"><label for="include_pages">Include pages?</label></th>
<td><input type="checkbox" id="include_pages" name="include_pages" value="1" <?php if ($options["include_pages"]) print "checked='checked'"; ?>/></td>
</tr>

<tr valign="top">
<th scope="row"><label for="sort_randomly">Sort randomly?</label></th>
<td><input type="checkbox" id="sort_randomly" name="sort_randomly" value="1" <?php if ($options["sort_randomly"]) print "checked='checked'"; ?>/> Uncheck if you want to pull from recent posts rather than random posts<br /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="count">How many to display?</label></th>
<td><input type="text" id="count" name="count" size="1" maxlength="2" <?php if ($options["count"]) print "value='" . $options["count"] . "'"; ?>/></td>
</tr>

<tr valign="top">
<th scope="row"><label for="value_separator">Value separator?</label></th>
<td><input type="text" id="value_separator" name="value_separator" size="1" <?php if ($options["value_separator"]) print "value='" . stripslashes(htmlspecialchars($options["value_separator"], ENT_QUOTES)) . "'"; ?>/> <span class="setting-description">Optional, defaults to newline. Use to break custom value into multiple pieces.</span></td>
</tr>


<tr valign="top">
<th scope="row"><label for="html_template">HTML template:</label></th>
<td><textarea id="html_template" name="html_template" rows="4" cols="24" style="float:left;"><?php if ($options["html_template"]) print stripslashes(htmlspecialchars($options["html_template"])); ?></textarea>
<div style="float:left;margin-left:10px;">Sample:<br /><code style="font-weight:bold;">&lt;strong&gt;%title&lt;/strong&gt;&lt;br /&gt;<br />&lt;a href="%permalink">&lt;img src="%0" />&lt;/a></code></div>
<div style="float:left;margin-left:10px;"><code>%title</code> = title<br /><code>%permalink</code> = permalink<br /><code>%excerpt</code> = excerpt<br /><code>%0</code> = full custom field value<br /><code>%1</code> = 1st separated value<br /><code>%2</code> = 2nd separated value, etc</div></td>
</tr>

<tr valign="top">
<th scope="row"><label for="html_between_templates">HTML between templates:</label></th>
<td><input type="text" id="html_between_templates" name="html_between_templates" size="12" <?php if (isset($options["html_between_templates"])) print "value='" . stripslashes(htmlspecialchars($options["html_between_templates"], ENT_QUOTES)) . "'"; ?>/>  e.g. <code style="font-weight:bold;">&lt;br /&gt;&lt;br /&gt;</code></td>
</tr>

<tr valign="top">
<th scope="row"><label>Limit by categories/tags:</label></th>

<td>
<div style='overflow:auto;height:10em;width:200px;background-color:#efefef;border:1px solid #b2b2b2;padding:2px 0 0 3px;'>
<?php
   
  // create WordPress-style category multi-select list
  global $wpdb, $wp_version;

  $categories = $wpdb->get_results("SELECT $wpdb->terms.term_id as cat_ID, $wpdb->terms.name as cat_name
                                    FROM $wpdb->terms LEFT JOIN $wpdb->term_taxonomy on $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
                                    WHERE $wpdb->term_taxonomy.taxonomy IN ('post_tag', 'category')
                                    ORDER BY $wpdb->terms.name");

  foreach ($categories as $category) {
    print "<label style='display:block;' for='category-$category->cat_ID'><input type='checkbox' value='$category->cat_ID' name='category_filter[]' id='category-$category->cat_ID'" . (in_array( $category->cat_ID, $options["category_filter"] ) ? ' checked="checked"' : "") . " />" .  wp_specialchars($category->cat_name) . "</label>\n";
  }
?>
</div>
(leave unchecked for all)
</td>
</tr>

</table>

<input type="hidden" name="action" value="update" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>


<div class="wrap">
<h2>Sample Output</h2>
<?php random_custom_fields(); ?>
</div>

<?php
}






function random_custom_fields(
  $custom_field_name        = "",
  $include_posts            = true,
  $include_pages            = true,
  $sort_randomly            = true,
  $count                    = 1, 
  $value_separator          = "",
  $html_template            = "%0",
  $html_between_templates   = "<br /><br />",
  $category_filter          = ""
  )
{
  // get access to wordpress' database object
  global $wpdb, $wp_version;
  $debugging = false;

  if ($debugging) print "<strong>Random Custom Fields Debugging is On!</strong><br/>";

  // if no arguments are specified
  // assume we're going with the configuration options
  if (!func_get_args()) {
    if ($debugging) print "Configuration options (specified via admin interface):<br />";

    $options = get_random_custom_fields_options();

    $custom_field_name      = $options['custom_field_name'];
    $include_posts          = $options['include_posts'];        
    $include_pages          = $options['include_pages'];        
    $sort_randomly          = $options['sort_randomly'];
    $count                  = $options['count'];        
    $value_separator        = $options['value_separator'];
    $html_template          = $options['html_template'];
    $html_between_templates = $options['html_between_templates'];

    // convert category filter array into a comma-separated list
    if (!is_array($options['category_filter'])) {
      $options['category_filter'] = array();
    }
    $category_filter  = implode(",", $options['category_filter']);

  } else {
    if ($debugging) print "Configuration options (specified via function parameters):<br />";
  }
  
  if ($debugging) {
    print "custom_field_name: "      . htmlspecialchars($custom_field_name)      . "<br/>";
    print "include_posts: "          . htmlspecialchars($include_posts)          . "<br/>";     
    print "include_pages: "          . htmlspecialchars($include_pages)          . "<br/>";    
    print "sort_randomly: "          . htmlspecialchars($sort_randomly)          . "<br/>";
    print "count: "                  . htmlspecialchars($count)                  . "<br/>";    
    print "value_separator: "        . htmlspecialchars($value_separator)        . "<br/>";    
    print "html_template: "          . htmlspecialchars($html_template)          . "<br/>";           
    print "html_between_templates: " . htmlspecialchars($html_between_templates) . "<br/>";    
    print "category_filter: "        . htmlspecialchars($category_filter)        . "<br/>";     
  }

  // by default, we pull from posts
  if ($include_posts == true && $include_pages == true) {
    $post_type_sql = "AND post_status = 'publish' AND post_type in ('post', 'page')";
  
  } elseif ($include_posts == false && $include_pages == true) {
    $post_type_sql = "AND post_status = 'publish' AND post_type = 'page'";

  } else {
    $post_type_sql = "AND post_status = 'publish' AND post_type = 'post'";
  }

  // select from only the chosen categories
  $category_filter_join  = "";
  $category_filter_sql   = "";
  $category_filter_group = "";

  if ($category_filter != "") {
    $category_filter_join  = "LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id";
    $category_filter_sql   = "AND $wpdb->term_taxonomy.term_id IN ($category_filter)";   
    $category_filter_group = "GROUP BY $wpdb->posts.ID";
  }
  
  // by default we sort randomly,
  // but we can also sort them in descending date order
  if ($sort_randomly) {
    $order_by_sql = "rand()";
  } else {
    $order_by_sql = "$wpdb->posts.post_date DESC";
  }

  // query records that contain img tags, ordered randomly
  // do not select from password protected posts
  $sql = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_excerpt, $wpdb->postmeta.meta_value
          FROM $wpdb->posts
          LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
          $category_filter_join
          WHERE $wpdb->postmeta.meta_key = '" . $wpdb->escape($custom_field_name) . "'
          AND post_password = ''
          $post_type_sql
          $category_filter_sql
          $category_filter_group
          ORDER BY $order_by_sql";
  $resultset = @mysql_query($sql, $wpdb->dbh);
  
  if ($debugging && mysql_error($wpdb->dbh)) print "mysql errors: " . mysql_error($wpdb->dbh) . "<br/> SQL: " . htmlspecialchars($sql) . "<br/>";;
  if ($debugging) print "elligible post count: " . @mysql_num_rows($resultset) . "<br/>"; 
  
  // keep track of multiple matches to prevent displaying dups
  $matches = array();

  // loop through each applicable post from the database
  $match_count = 0;
  while ($row = mysql_fetch_array($resultset)) {
    $post_title     = $row['post_title'];
    $post_permalink = get_permalink($row['ID']);
    $post_excerpt   = $row['post_excerpt'];
    $postmeta_value = $row['meta_value'];

    // make sure we haven't displayed this meta value before
    if ($postmeta_value === "" || in_array($postmeta_value, $matches)) {
      continue;
    }

    // add value to array to check for dupes next time around
    $matches[] = $postmeta_value;

    $html = $html_template;
    
    // replace standard template vars
    $html = str_replace("%title",     htmlspecialchars($post_title,     ENT_QUOTES), $html);
    $html = str_replace("%permalink", htmlspecialchars($post_permalink, ENT_QUOTES), $html);
    $html = str_replace("%excerpt",   htmlspecialchars($post_excerpt,   ENT_QUOTES), $html);
    $html = str_replace("%0",         htmlspecialchars($postmeta_value, ENT_QUOTES), $html);

    
    // replace "dynamic" template vars
    if ($value_separator == "") {
      $value_separator = "\n";
    }
    if (strpos($postmeta_value, $value_separator) !== false) {
      foreach (explode($value_separator, $postmeta_value) as $key => $value) {
        $html = str_replace("%" . ($key + 1), htmlspecialchars($value, ENT_QUOTES), $html);
      }
    }
    
    // clean up left over template vars 0-99
    $html = preg_replace("/%[0-9]{1,2}/", "", $html);

    print $html;

    $match_count++;
    
    if ($match_count == $count) {
      return true;

    } else {
      print "$html_between_templates\n";
    }
    
  }
}
?>
