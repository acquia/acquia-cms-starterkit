<?php

namespace tests;

use AcquiaCMS\Cli\Cli;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

class CliTest extends TestCase {
  use ProphecyTrait;

  /**
   * Holds the symfony console output object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $output;

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $projectDirectory;


  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $rootDirectory;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCli;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->output = $this->prophesize(OutputInterface::class);
    $output = $this->output->reveal();
    $this->projectDirectory = getcwd();
    $this->rootDirectory = $this->projectDirectory;
    $this->acquiaCli = new Cli($this->projectDirectory, $this->rootDirectory, $output);
  }

  /**
   * Test the welcome message that display when running command acms:install.
   *
   * @test
   */
  public function testExecute() :void {
    $this->assertEquals("Welcome to Acquia CMS Starter Kit installer", $this->acquiaCli->headline);
    $this->assertEquals($this->projectDirectory . "/assets/acquia_cms.icon.ascii", $this->acquiaCli->getLogo());
    $this->assertEquals($this->getAcmsFileContents(), $this->acquiaCli->getAcquiaCmsFile());
  }

  /**
   * @dataProvider alterModuleThemesDataProvider
   */
  public function testAlterModuleThemes(string $bundle, array $userValues, array $expected, string $message = ''): void {
    $starter_kit = $this->getAcmsFileContents()['starter_kits'][$bundle];
    $expected = array_replace_recursive($starter_kit, ...$expected);
    $this->acquiaCli->alterModulesAndThemes($starter_kit, $userValues);
    $this->assertEquals($starter_kit, $expected, $message);
  }

  /**
   * An array of default contents for acms/acms.yml file.
   */
  protected function getAcmsFileContents() :array {
    return [
      "starter_kits" => [
        "acquia_cms_enterprise_low_code" => [
          "name" => "Acquia CMS Enterprise Low-code",
          "description" => "The low-code starter kit will install Acquia CMS with Site Studio and a UIkit. It provides drag and drop content authoring and low-code site building. An optional content model can be added in the installation process.",
          "modules" => [
            "install" => [
              'acquia_cms_site_studio:^1.3.5',
              "acquia_cms_page:^1.3.3",
              "acquia_cms_search:^1.3.5",
              "acquia_cms_tour:^1.3.0",
              "acquia_cms_toolbar:^1.3.3",
            ],
          ],
          "themes" => [
            "install" => ["acquia_claro:^1.3.2"],
            "admin" => "acquia_claro",
            "default" => "cohesion_theme",
          ],
        ],
        "acquia_cms_community" => [
          "name" => "Acquia CMS Community",
          "description" => "The community starter kit will install Acquia CMS. An optional content model can be added in the installation process.",
          "modules" => [
            "install" => [
              "acquia_cms_search:^1.3.5",
              "acquia_cms_tour:^1.3.0",
              "acquia_cms_toolbar:^1.3.3",
            ],
          ],
          "themes" => [
            "install" => ["acquia_claro:^1.3.2"],
            "admin" => "acquia_claro",
          ],
        ],
        "acquia_cms_headless" => [
          "name" => "Acquia CMS Headless",
          "description" => "The headless starter kit preconfigures Drupal for serving structured, RESTful \ncontent to 3rd party content displays such as mobile apps, smart displays and \nfrontend driven websites (e.g. React or Next.js).",
          "modules" => [
            "install" => [
              "acquia_cms_headless",
              "acquia_cms_search:^1.3.5",
              "acquia_cms_tour:^1.3.0",
              "acquia_cms_toolbar:^1.3.3",
            ],
          ],
          "themes" => [
            "install" => ["acquia_claro:^1.3.2"],
            "admin" => "acquia_claro",
          ],
        ],
      ],
      "questions" => array_merge (
        self::getContentModel(),
        self::getDemoContent(),
        self::getGmapsKey(),
        self::getSiteStudioApiKey(),
        self::getSiteStudioOrgKey(),
      ),
    ];
  }

  /**
   * Returns the test data for content_model Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getContentModel(): array {
    return [
      'content_model' => [
        'dependencies' => [
          'starter_kits' => 'acquia_cms_enterprise_low_code || acquia_cms_headless || acquia_cms_community',
          'questions' => ['${demo_content} == "no"'],
        ],
        'question' => "Do you want to enable the content model (yes/no) ?",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'skip_on_value' => FALSE,
        'default_value' => 'no',
      ],
    ];
  }

  /**
   * Returns the test data for demo_content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getDemoContent(): array {
    return [
      'demo_content' => [
        'dependencies' => [
          'starter_kits' => 'acquia_cms_enterprise_low_code || acquia_cms_headless || acquia_cms_community',
        ],
        'question' => "Do you want to enable demo content (yes/no) ?",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'skip_on_value' => FALSE,
        'default_value' => 'no',
      ],
    ];
  }

  /**
   * Returns the test data for SITESTUDIO_API_KEY Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getSiteStudioApiKey(): array {
    return [
      'SITESTUDIO_API_KEY' => [
        'dependencies' => [
          'starter_kits' => 'acquia_cms_enterprise_low_code',
          'questions' => ['${demo_content} == "ALL"'],
        ],
        'question' => "Please provide the Site Studio API Key",
        'warning' => "The Site Studio API key is not set. The Site Studio packages won't get imported.\nYou can set the key later from: /admin/cohesion/configuration/account-settings to import Site Studio packages.",
      ],
    ];
  }

  /**
   * Returns the test data for SITESTUDIO_ORG_KEY Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getSiteStudioOrgKey(): array {
    return [
      'SITESTUDIO_ORG_KEY' => [
        'dependencies' => [
          'starter_kits' => 'acquia_cms_enterprise_low_code',
          'questions' => ['${demo_content} == "ALL"'],
        ],
        'question' => "Please provide the Site Studio Organization Key",
        'warning' => "The Site Studio Organization key is not set. The Site Studio packages won't get imported.\nYou can set the key later from: /admin/cohesion/configuration/account-settings to import Site Studio packages.",
      ],
    ];
  }

  /**
   * Returns the test data for GMAPS_KEY Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getGmapsKey(): array {
    return [
      'GMAPS_KEY' => [
        'dependencies' => [
          'starter_kits' => 'acquia_cms_enterprise_low_code || acquia_cms_community || acquia_cms_headless',
          'questions' => ['${demo_content} == "yes"', '${content_model} == "yes"'],
        ],
        'question' => "Please provide the Google Maps API Key",
        'warning' => "The Google Maps API key is not set. So, you might see errors, during enable modules step. They are technically harmless, but the maps will not work.\nYou can set the key later from: /admin/tour/dashboard and resave your starter content to generate them.",
      ],
    ];
  }

  /**
   * Function to return data provider for method: alterModulesAndThemes().
   */
  public function alterModuleThemesDataProvider() :array {
    foreach (['acquia_cms_enterprise_low_code', 'acquia_cms_community', 'acquia_cms_headless'] as $bundle) {
      $returnArray = [
        [
          $bundle,
          [
            'demo_content' => 'yes',
          ],
          [
            [
              "modules" => [
                "install" => array_merge(
                  $this->getUpdatedModulesThemesArray($bundle),
                  $this->getUpdatedModulesThemesArray($bundle, 'demo_content'),
                ),
              ],
            ],
            [
              'themes' => ['default' => 'olivero'],
            ],
          ],
          "$bundle with Content Model & Demo Content",
        ],
        [
          $bundle,
          [
            'content_model' => 'yes',
            'demo_content' => 'no',
          ],
          [
            [
              "modules" => [
                "install" => array_merge(
                  $this->getUpdatedModulesThemesArray($bundle),
                  $this->getUpdatedModulesThemesArray($bundle, 'content_model'),
                ),
              ],
            ],
            [
              'themes' => ['default' => 'olivero'],
            ],
          ],
          "$bundle with Content Model",
        ],
        [
          $bundle,
          [
            'content_model' => 'no',
            'demo_content' => 'no',
          ],
          [
            [
              "modules" => [
                "install" => array_merge(
                  $this->getUpdatedModulesThemesArray($bundle),
                ),
              ],
            ],
            [
              'themes' => ['default' => 'olivero'],
            ],
          ],
          "$bundle with No Content Model & No Demo Content",
        ],
      ];
    }
    return $returnArray;
  }

  /**
   * Function to return modules/themes needed for different starter-kits.
   *
   * @param string $bundle
   *   A starter-kit machine name.
   * @param string $question_type
   *   A question machine_name.
   */
  public function getUpdatedModulesThemesArray(string $bundle, string $question_type = ''): array {
    switch ($question_type) :
      case 'content_model':
        $packages = [
          "acquia_cms_article:^1.3.4",
          "acquia_cms_page:^1.3.3",
          "acquia_cms_event:^1.3.4",
        ];
        break;

      case 'demo_content':
        $packages = [
          "acquia_cms_article:^1.3.4",
          "acquia_cms_page:^1.3.3",
          "acquia_cms_event:^1.3.4",
          "acquia_cms_starter:^1.3.0",
        ];
        break;

      default:
        $packages = [
          "acquia_cms_search:^1.3.5",
          "acquia_cms_tour:^1.3.0",
          "acquia_cms_toolbar:^1.3.3",
        ];
        if ($bundle == 'acquia_cms_headless') {
          array_unshift($packages, 'acquia_cms_headless');
        }
        break;
    endswitch;
    return $packages;
  }

}
