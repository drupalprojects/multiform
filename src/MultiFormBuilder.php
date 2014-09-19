<?php

/**
* @file
* Contains \Drupal\multiform\FormBuilder.
*/

namespace Drupal\multiform;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Component\Utility\Random;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\String;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Render\Element;


class MultiFormBuilder implements MultiFormBuilderInterface  {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new MultiFormBuilder.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(FormBuilderInterface $form_builder, ClassResolverInterface $class_resolver, RequestStack $request_stack ) {
    $this->formBuilder = $form_builder;
    $this->classResolver = $class_resolver;

    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId($form_args) {
    $form_id = '';
    foreach ($form_args as $form_arg) {
      // If the $form_arg is the name of a class, instantiate it. Don't allow
      // arbitrary strings to be passed to the class resolver.
      if (is_string($form_arg) && class_exists($form_arg)) {
        $form_arg = $this->classResolver->getInstanceFromDefinition($form_arg);
      }

      if (!is_object($form_arg) || !($form_arg instanceof FormInterface)) {
        throw new \InvalidArgumentException(String::format('The multiform argument @form_arg is not a valid form.', array('@form_arg' => $form_arg)));
      }

      // Add the $form_arg as the callback object and determine the form ID.
      if ($form_arg instanceof BaseFormIdInterface) {
        $form_id .= $form_arg->getBaseFormID();
      }
    }

    return $form_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getForm() {
    $all_args = func_get_args();
    $redirect = NULL;
    $form = \Drupal::service('element_info')->getInfo('form');
    $form['#id'] = 'multiform';//$this->getFormId($all_args);
    $form['#attributes'] = array();
    // We need a $form_id so that the submission of other forms (like search) do
    // not disturb us.
    $form['form_id'] = array(
      '#type' => 'hidden',
      '#value' => $form['#id'],
      '#id' => Html::getUniqueId('edit-multiform'),
      '#name' => 'form_id',
      '#attributes' => array(),
    );
    $randomGenerator = new Random();
    $build_id = 'form-' . $randomGenerator->name();
    // We need a $form_build_id because the buttons are cached and the values
    // belonging to them in $_POST are handed to each form so those can recognize
    // the buttons pressed.
    $form['form_build_id'] = array(
      '#type' => 'hidden',
      '#value' => $build_id,
      '#id' => $build_id,
      '#name' => 'form_build_id',
      '#attributes' => array(),
    );
    // This is where buttons will be collected.
    $form['buttons'] = array();
    $form['buttons']['#weight'] = 1000;
    $form['buttons']['#build_id'] = $build_id;

    $form_state_save = new FormState();
    $button_names = array();
    // The only way to support $_GET would be to accept $form_state. Maybe later.
    $post = $this->request->request->all();
    if ($post && isset($post['form_id']) && $post['form_id'] == $form['#id'] && !empty($post['form_build_id'])) {
      $form_state_save->setUserInput($post);
      $_files_save = $_FILES;
      // Retrieve buttons.
      if ($button_elements = \Drupal::formBuilder()->getCache($post['form_build_id'], $form_state_save)) {
        foreach ($button_elements as $button) {
          // For each button, save it's name. Later on we will need the button
          // names because the buttons are used in the multiform but their values
          // in $_POST (if it exists) needs to be handed down to each form so
          // those can recognize the button press.
          $name = isset($button_elements['#name']) ? $button_elements['#name'] : 'op';
          $button_names[$name] = $name;
        }
      }
    }
    foreach ($all_args as $key => $args) {
      $form_arg = array_shift($args);

      $current_form_state = new FormState();
      $current_form_state->addBuildInfo('args', $args);
      // Reset $form_state and disable redirection.
      $current_form_state->disableRedirect();
      // This line is copied literally from drupal_get_form().
      $index = $this->formBuilder->getFormId($form_arg, $current_form_state) . '_' . $key;
      if (isset($post['multiform'][$index])) {
        // drupal_build_form() honors our $form_state['input'] setup.
        $current_form_input = $post['multiform'][$index];
        // Pass in the information about pressed buttons too.
        $form_state_input = $form_state_save->getUserInput();
        foreach ($button_names as $name) {
          if (isset($form_state_input[$name])) {
            $current_form_input[$name] = $form_state_input[$name];
          }
        }
        $current_form_state->setUserInput($current_form_input);
      }
      $files = array();
      if (isset($_files_save['multiform']['name'][$index])) {
        foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $files_key) {
          // PHP is seriously messed up, dude.
          foreach ($this->request->files['multiform'][$files_key][$index] as $running_out_of_indexes => $data) {
            $files[$running_out_of_indexes][$files_key] = $data;
          }
        }
      }
      $current_form_state->addBuildInfo('files', $files);

      $current_form = $this->formBuilder->buildForm($form_arg, $current_form_state);
      // Do not render the <form> tags. Instead we render the <form> as a <div>.
      $current_form['#theme_wrappers'] = array('container');
      $this->multiformGetForm($current_form, $form['buttons'], $index);
      // Unset any attributes specifics to form tags.
      $disallowed_attributes = array('enctype', 'action', 'method');
      $current_form['#attributes'] = array_diff_key($current_form['#attributes'], array_flip($disallowed_attributes));
      $form['multiform'][$index] = $current_form;
      if (isset($form_state['has_file_element'])) {
        $form['#attributes']['enctype'] = 'multipart/form-data';
      }
      // Keep the redirect from the first form.
      if (!$key) {
        $redirect = $current_form_state->getRedirect() ? $current_form_state : array();
      }
    }
    $this->formBuilder->setCache($build_id, $form['buttons'], $form_state_save);
    if (!empty($redirect)) {
      // We forced $form_state['no_redirect'] to TRUE above, so unset it in order
      // to allow the redirection to proceed.
      $redirect->disableRedirect(FALSE);
      $this->formBuilder->redirectForm($redirect);
    }
    return $form;
  }

  /**
   * Recursive helper for MultiFormBuilder::getForm().
   *
   * @param array $element
   * @param array $buttons
   * @param string $form_id
   */
  protected function multiformGetForm(&$element, &$buttons, $form_id) {
    foreach (Element::children($element) as $key) {
      $this->multiformGetForm($element[$key], $buttons, $form_id);
    }
    // Save but do not display buttons. Note that this is done before the #name
    // is changed. This way the buttons belong to the top form and their values
    // can be handed to each form.
    if (!empty($element['#is_button'])) {
      $buttons[$element['#value']] = $element;
      $element['#access'] = FALSE;
    }
    // By only changing $element['#name'] form API is not affected but the
    // browser will put the element values into _POST where multiform_get_form
    // expects them.
    elseif (isset($element['#name'])) {
      // If the name was op then we want multiform[$form_id][op]. If it was
      // foo[bar] then we want multiform[$form_id][foo][bar].
      $element['#name'] = "multiform[$form_id]" . preg_replace('/^[^[]+/', '[\0]', $element['#name']);
    }
  }
}
