<?php

/**
 * @file
 * Contains \Drupal\multiform_example\Form\Example2.
 */
namespace Drupal\multiform_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\AjaxResponse;

class Example2 extends FormBase {
  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'example_form2';
  }
  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['text_field_2'] = array(
      '#type' => 'textfield',
      '#title' => 'Text field 2'
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save'
    );
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => 'Delete',
      '#submit' => array('::multiformTestDelete')
    );

    return $form;
  }
  /**
   * Submit handler for the delete button
   */
  public function multiformTestDelete(array &$form, FormStateInterface $form_state) {
    drupal_set_message('Multiform Example 2 test Delete.');
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message('Multiform Example 2 test Save.');
  }
}
