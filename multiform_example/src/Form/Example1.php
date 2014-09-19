<?php

/**
 * @file
 * Contains \Drupal\multiform_example\Form\Example1.
 */
namespace Drupal\multiform_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class Example1 extends FormBase {
  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'example_form1';
  }
  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['text_field_1'] = array(
      '#type' => 'textfield',
      '#title' => 'Text field 1'
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save'
    );
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => 'Delete',
      '#submit' => array('::multiformTestDelete'),
    );
    
    return $form;
  }
  /**
   * Submit handler for the delete button
   */
  public function multiformTestDelete(array &$form, FormStateInterface $form_state) {
    drupal_set_message('Multiform Example 1 test Delete.');
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message('Multiform Example 1 test Save.');
  }
}
