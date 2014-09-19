<?php

/**
 * @file
 * Contains \Drupal\multiform\MultiFormBuilderInterface.
 */

namespace Drupal\multiform;

/**
 * Provides an interface for form building and processing.
 */
interface MultiFormBuilderInterface {

  /**
   * Determines the ID of a form.
   *
   * @param array $form_args
   *   The value is identical to that of self::getForm()'s arguments.
   *
   * @return string
   *   The unique string identifying the desired form.
   */
  public function getFormId($form_args);

  /**
   * Gets a renderable form array.
   *
   * Every argument is a list of arguments to be passed to FormBuilder::getForm.
   * For example, if the first form is called as
   * FormBuilder::getForm($form_id1, $arg1, $arg2); and
   * the second as FormBuilder::getForm($form_id2, $arg3, $arg4) call
   * MultiFormBuilder::getForm(array($form_id1, $arg1, $arg2), array($form_id2, $arg3, $arg4)).
   *
   * @return array
   *   The form array.
   */
  public function getForm();

}
