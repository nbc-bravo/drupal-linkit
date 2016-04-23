<?php

namespace Drupal\linkit\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;
use Drupal\linkit\Controller\AutocompleteController;
use Drupal\linkit\Entity\Profile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the linkit autocomplete functionality.
 *
 * @group Linkit
 */
class LinkitAutocompleteTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'entity_test', 'linkit'];

  /**
   * @var \Drupal\Linkit\ProfileInterface
   */
  protected $linkitProfile;

  /**
   * The matcher manager.
   *
   * @var \Drupal\linkit\MatcherManager
   */
  protected $matcherManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', 'router');
    \Drupal::service('router.builder')->rebuild();

    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');

    $this->matcherManager = $this->container->get('plugin.manager.linkit.matcher');

    $this->linkitProfile = $this->createProfile();
  }

  /**
   * Tests that inaccessible entities isn't included in the results.
   */
  function testAutocompletionAccess() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->matcherManager->createInstance('entity:entity_test');
    $this->linkitProfile->addMatcher($plugin->getConfiguration());
    $this->linkitProfile->save();

    $entity_1 = EntityTest::create(['name' => 'no_forbid_access']);
    $entity_1->save();
    $entity_2 = EntityTest::create(['name' => 'forbid_access']);
    $entity_2->save();

    $data = $this->getAutocompleteResult('forbid');
    $this->assertTrue(count($data) == 1, 'Autocomplete returned the expected amount of matches.');
    $this->assertSame($entity_1->label(), $data[0]['title'], 'Autocomplete did not include the inaccessible entity.');
  }

  /**
   * Tests that 'front' adds the front page match.
   */
  function testAutocompletionFront() {
    $data = $this->getAutocompleteResult('front');
    $this->assertSame('Front page', $data[0]['title'], 'Autocomplete returned the front page match.');
  }

  /**
   * Tests the autocomplete with an email address.
   */
  function testAutocompletionEmail() {
    $email = 'drupal@example.com';
    $data = $this->getAutocompleteResult($email);
    $this->assertSame((string) new FormattableMarkup('E-mail @email', ['@email' => $email]), $data[0]['title'], 'Autocomplete returned email match.');
    $this->assertSame('mailto:' . $email, $data[0]['path'], 'Autocomplete returned email match with an mailto href.');
  }

  /**
   * Tests autocompletion in general.
   */
  function testAutocompletion() {
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->matcherManager->createInstance('entity:entity_test');
    $this->linkitProfile->addMatcher($plugin->getConfiguration());
    $this->linkitProfile->save();

    $entity_1 = EntityTest::create(['name' => 'Barbar']);
    $entity_1->save();
    $entity_2 = EntityTest::create(['name' => 'Foobar']);
    $entity_2->save();
    $entity_3 = EntityTest::create(['name' => 'Basbar']);
    $entity_3->save();

    // Search for something that doesn't exists.
    $data = $this->getAutocompleteResult('no_matches');
    $this->assertTrue(count($data) == 1, 'Autocomplete returned the expected amount of matches.');
    $this->assertSame($this->noResults(), $data[0], 'Autocomplete returned the "no results."');

    // Search for something that exists one time.
    $data = $this->getAutocompleteResult('bas');
    $this->assertTrue(count($data) == 1, 'Autocomplete returned the expected amount of matches.');
    $this->assertSame(Html::escape($entity_3->label()), $data[0]['title'], 'Autocomplete returned the matching entity');

    // Search for something that exists three times.
    $data = $this->getAutocompleteResult('bar');
    $this->assertTrue(count($data) == 3, 'Autocomplete returned the expected amount of matches.');
    $this->assertSame(Html::escape($entity_1->label()), $data[0]['title'], 'Autocomplete returned the first matching entity.');
    $this->assertSame(Html::escape($entity_3->label()), $data[1]['title'], 'Autocomplete returned the second matching entity.');
    $this->assertSame(Html::escape($entity_2->label()), $data[2]['title'], 'Autocomplete returned the third matching entity.');

    // Search for something with an empty string.
    $data = $this->getAutocompleteResult('');
    $this->assertTrue(count($data) == 1, 'Autocomplete returned the expected amount of matches.');
    $this->assertSame($this->noResults(), $data[0], 'Autocomplete returned the "no results."');
  }

  /**
   * Returns the result of an Linkit autocomplete request.
   *
   * @param string $input
   *   The label of the entity to query by.
   *
   * @return array
   *  An array of matches.
   */
  protected function getAutocompleteResult($input) {
    $request = Request::create('linkit/autocomplete/' . $this->linkitProfile->id());
    $request->query->set('q', $input);

    $controller = AutocompleteController::create($this->container);
    $result = Json::decode($controller->autocomplete($request, $this->linkitProfile->id())->getContent());
    return $result['matches'];
  }

  /**
   * Creates a profile based on default settings.
   *
   * @param array $settings
   *   (optional) An associative array of settings for the profile, as used in
   *   entity_create(). Override the defaults by specifying the key and value
   *   in the array
   *
   *   The following defaults are provided:
   *   - label: Random string.
   *
   * @return \Drupal\linkit\ProfileInterface
   *   The created profile entity.
   *
   * @todo Do a trait of this?
   */
  protected function createProfile(array $settings = []) {
    // Populate defaults array.
    $settings += [
      'id' => Unicode::strtolower($this->randomMachineName()),
      'label' => $this->randomMachineName(),
    ];

    $profile = Profile::create($settings);
    $profile->save();

    return $profile;
  }

  /**
   * Returns the "no results" match.
   *
   * @return array with a fixed value of no results.
   *
   * @todo Should this use some kind of t() function?
   */
  protected function noResults() {
    return [
      'title' => 'No results'
    ];
  }

}