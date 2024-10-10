How the modalpop module works
=============================

modalpop is a module that provides a new content type that will present
to the user a modal pop-up with styled content, up to three buttons and
various other features including traffic analysis.

The `field_template_key` field is used to target custom templates if you feel
the standard one provided by the module does not meet your needs and a CSS
solution is not appropriate. To make use of it simply copy the default
template...

modalpop -> templates -> node--modalpop.html.twig

...and copy it to the templates folder in your theme and rename it along
these lines:

node--modalpop-xxx-unique-key-xxx.html.twig

Then you open up your theme file (MY_THEME.theme) and insert an instance of
hook_theme_suggestions_hook_alter(). It should look like this:

/**
 * Implements hook_theme_suggestions_hook_alter().
 */
function YOURTHEME_theme_suggestions_node_alter(array &$suggestions, array $variables) {
  // Extract the node object from $variables.
  $node = $variables['elements']['#node'];
  $field_template_key = '';
  $field_pop_view_mode = '';
  // If we have a node of type `modalpop`...
  if ($node->getType() == 'modalpop') {
    // If we have a template key value...
    if (!empty($node->get('field_template_key')->value)) {
      $field_template_key = $node->get('field_template_key')->value;
      $suggestions[] = 'node__modalpop__' . $field_template_key;
    }
    // If we have a pop-up view mode value...
    if (!empty($node->get('field_pop_view_mode')->value)) {
      $field_pop_view_mode = $node->get('field_pop_view_mode')->value;
      $suggestions[] = 'node__modalpop__' . $field_pop_view_mode;
    }
    // If we have both...
    if (!empty($field_template_key) && !empty($field_pop_view_mode)) {
      $suggestions[] = 'node__modalpop__' . $field_pop_view_mode . '__' . $field_template_key;
    }
  }
}

When you create a modal pop-up node and you fill in the `template key` field
then whatever value you use can be part of the copied modalpop template. In
the above example the value would be `xxx_unique_key_xxx`.

Please note that when naming templates we use underscores in our PHP code
and they are replaced with hyphens in the filenames.

The template_field_key is also included as a CSS class in the template.
This way you can sort your pop-ups into different types with different
templates for structure and different styles for appearance.
