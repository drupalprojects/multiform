<?php

/**
 * @file
 * Definition of Drupal\multiform\Tests\MultiFormExampleTest.
 */

namespace Drupal\multiform_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Creates a multiform test interface.
 *
 * @group multiform
 */
class MultiFormExampleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('multiform_example', 'multiform');

  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests functionality of the multiform module.
   */
  function testMultiFormBuilder() {
    $multiform_path = '/multiform_example/multiform';
    $this->drupalGet($multiform_path);
    $this->assertResponse(200);

    // Ensure that we have the correct form element on the multiform.
    $this->assertTrue($this->xpath('//input[@name=:name]', array(':name' => 'multiform[example_form1_0][text_field_1]')));
    $this->assertTrue($this->xpath('//input[@name=:name]', array(':name' => 'multiform[example_form2_1][text_field_2]')));

    $this->drupalPostForm($multiform_path, array(), t('Save'));
    $this->assertText('Multiform Example 1 test Save.');
    $this->assertText('Multiform Example 2 test Save.');

    $this->drupalGet($multiform_path);
    $this->assertResponse(200);

    $this->drupalPostForm($multiform_path, array(), t('Delete'));
    $this->assertText('Multiform Example 1 test Delete.');
    $this->assertText('Multiform Example 2 test Delete.');
  }
}
