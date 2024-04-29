<?php

namespace Drupal\modalpop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ModalPopSettingsForm.
 */
class ModalPopSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'modalpop.modalpopsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_pop_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('modalpop.modalpopsettings');

    $default_expiry = $config->get('modalpop_cookie_expiry');
    $default_expiry ? $default_expiry : $default_expiry = 14;
    $form['modalpop_cookie_expiry'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie expiry'),
      '#description' => $this->t('Either enter the number of days before the cookie is deleted (starting from when it was set) or enter a specific date in the format `YYYY-MM-DD`, eg  for September 23rd 2008 you should enter `2008-09-23`.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $default_expiry,
    ];

    $default_opacity = $config->get('modalpop_overlay_opacity');
    $default_opacity ? $default_opacity : $default_opacity = 50;
    $form['modalpop_overlay_opacity'] = [
      '#type' => 'select',
      '#title' => $this->t('Cookie opacity'),
      '#description' => $this->t('Please select a percentage value. `0` means invisible, `50` means semi-transparent and `100` means solid.'),
      '#options' => [
        '0' => 0,
        '10' => 10,
        '20' => 20,
        '30' => 30,
        '40' => 40,
        '50' => 50,
        '60' => 60,
        '70' => 70,
        '80' => 80,
        '90' => 90,
        '100' => 100,
      ],
      '#size' => 1,
      '#default_value' => $default_opacity,
    ];

    $custom_template_directory = $config->get('modalpop_custom_template_directory');
    $custom_template_directory ? $custom_template_directory : $custom_template_directory = '';
    $form['modalpop_custom_template_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom template directory'),
      '#description' => $this->t('This defines the location of any custom templates for your modalpops. You might want to create a folder in a custom theme, like this: `themes/custom/MY_CUSTOM_THEME/templates/custom-modalpop-templates`.'),
      '#maxlength' => 256,
      '#size' => 64,
      '#default_value' => $custom_template_directory,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('modalpop.modalpopsettings')
      ->set('modalpop_cookie_expiry', $form_state->getValue('modalpop_cookie_expiry'))
      ->set('modalpop_overlay_opacity', $form_state->getValue('modalpop_overlay_opacity'))
      ->set('modalpop_custom_template_directory', $form_state->getValue('modalpop_custom_template_directory'))
      ->save();
  }

}
