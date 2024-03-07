<?php

namespace tests;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use AcquiaCMS\Cli\Validation\StarterKitValidation;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

class CliTest extends TestCase {

  use ProphecyTrait, UserInputTrait;

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
   * Starter-kit validator.
   *
   * @var \AcquiaCMS\Cli\Validation\StarterKitValidation
   */
  protected $starterKitValidation;

  /**
   * An array of build questions defined in acms.yml file.
   *
   * @var array
   */
  protected $buildOptions;

  /**
   * An array of install questions defined in acms.yml file.
   *
   * @var array
   */
  protected $installOptions;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->output = $this->prophesize(OutputInterface::class);
    $output = $this->output->reveal();
    $this->projectDirectory = getcwd();
    $this->rootDirectory = $this->projectDirectory;
    $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
    $this->starterKitValidation = new StarterKitValidation();
    $this->acquiaCli = new Cli($this->projectDirectory, $this->rootDirectory, $output, $container, $this->starterKitValidation);
    $this->buildOptions = $this->acquiaCli->getInstallerQuestions('build');
    $this->installOptions = $this->acquiaCli->getInstallerQuestions('install');
  }

  /**
   * Test the welcome message that display when running command acms:install.
   *
   * @test
   */
  public function testExecute(): void {
    $this->assertEquals("Welcome to Acquia CMS Starter Kit installer", $this->acquiaCli->headline);
    $this->assertEquals($this->projectDirectory . "/assets/acquia_cms.icon.ascii", $this->acquiaCli->getLogo());
    $this->assertEquals($this->getAcmsFileContents(), $this->acquiaCli->getAcquiaCmsFile($this->projectDirectory . '/acms/acms.yml'));
  }

  /**
   * Tests starter-kit validation.
   */
  public function testValidateStarterKit(): void {
    $starterKits = $this->getAcmsFileContents()['starter_kits'];
    $schema = $this->acquiaCli->getAcquiaCmsFile($this->projectDirectory . '/acms/schema.json');
    $this->starterKitValidation->validateStarterKits($schema, $starterKits);
    $this->assertIsArray($starterKits);
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
  protected function getAcmsFileContents(): array {
    return [
      "starter_kits" => [
        "acquia_cms_enterprise_low_code" => [
          "name" => "Acquia CMS Enterprise Low-code",
          "description" => "The low-code starter kit will install Acquia CMS with Site Studio and a UIkit. It provides drag and drop content authoring and low-code site building. An optional content model can be added in the installation process.",
          "modules" => [
            "require" => [
              "acquia_cms_site_studio",
              "acquia_cms_page",
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
              "google_tag",
              "honeypot",
              "mnsami/composer-custom-directory-installer",
              "recaptcha",
              "reroute_email",
              "shield",
            ],
            "install" => [
              "acquia_cms_site_studio",
              "sitestudio_claro",
              "acquia_cms_page",
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
            ],
          ],
          "themes" => [
            "require" => ["acquia_claro"],
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
            "default" => "cohesion_theme",
          ],
        ],
        "acquia_cms_community" => [
          "name" => "Acquia CMS Community",
          "description" => "The community starter kit will install Acquia CMS. An optional content model can be added in the installation process.",
          "modules" => [
            "require" => [
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
              "google_tag",
              "honeypot",
              "mnsami/composer-custom-directory-installer",
              "recaptcha",
              "reroute_email",
              "shield",
            ],
            "install" => [
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
            ],
          ],
          "themes" => [
            "require" => ["acquia_claro"],
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
            "default" => "olivero",
          ],
        ],
        "acquia_cms_headless" => [
          "name" => "Acquia CMS Headless",
          "description" => "The headless starter kit preconfigures Drupal for serving structured, RESTful \ncontent to 3rd party content displays such as mobile apps, smart displays and \nfrontend driven websites (e.g. React or Next.js).",
          "modules" => [
            "require" => [
              "acquia_cms_headless",
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
              "consumer_image_styles",
              "mnsami/composer-custom-directory-installer",
              "reroute_email",
              "shield",
            ],
            "install" => [
              "acquia_cms_headless_ui",
              "acquia_cms_search",
              "acquia_cms_tour",
              "acquia_cms_toolbar",
              "consumer_image_styles",
            ],
          ],
          "themes" => [
            "require" => ["acquia_claro"],
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
            "default" => "olivero",
          ],
        ],
      ],
      "questions" => [
        "build" => array_merge (
          self::getContentModel(),
          self::getDemoContent(),
          self::getDamIntegration(),
          self::getGdprIntegration()
        ),
        "install" => array_merge(
          self::getNextjsApp(),
          self::getNextjsAppSiteUrl(),
          self::getNextjsAppSiteName(),
          self::getNextjsAppEvnFile(),
          self::getGmapsKey(),
          self::getSiteStudioApiKey(),
          self::getSiteStudioOrgKey(),
        ),
      ],
    ];
  }

  /**
   * Returns the test data for content-model Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getContentModel(): array {
    return [
      'content-model' => [
        'dependencies' => [
          'starter_kits' => [
            'acquia_cms_enterprise_low_code',
            'acquia_cms_headless',
            'acquia_cms_community',
          ],
          'questions' => ['${demo-content} == "no"'],
        ],
        'question' => "Do you want to enable the content model (yes/no) ?",
        'help_text' => "Enable Content Model.",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'default_value' => 'no',
        'skip_on_value' => FALSE,
      ],
    ];
  }

  /**
   * Returns the test data for demo-content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getDemoContent(): array {
    return [
      'demo-content' => [
        'dependencies' => [
          'starter_kits' => [
            'acquia_cms_enterprise_low_code',
            'acquia_cms_headless',
            'acquia_cms_community',
          ],
        ],
        'question' => "Do you want to enable demo content (yes/no) ?",
        'help_text' => "Enable Demo Content.",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'default_value' => 'no',
        'skip_on_value' => FALSE,
      ],
    ];
  }

  /**
   * Returns the test data for dam-integration Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getDamIntegration(): array {
    return [
      'dam-integration' => [
        'dependencies' => [
          'starter_kits' => [
            'acquia_cms_enterprise_low_code',
            'acquia_cms_headless',
            'acquia_cms_community',
          ],
        ],
        'question' => "Would you like to enable the Acquia DAM modules (configuration will need to be done manually later after site installation) ?",
        'help_text' => "Integrate Acquia DAM.",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'skip_on_value' => FALSE,
        'default_value' => 'no',
      ],
    ];
  }

  /**
   * Returns the test data for dam-integration Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getGdprIntegration(): array {
    return [
      'gdpr-integration' => [
        'dependencies' => [
          'starter_kits' => [
            'acquia_cms_enterprise_low_code',
            'acquia_cms_community',
          ],
        ],
        'question' => "Would you like to add GDPR functionality to the site (Yes/No) ?",
        'help_text' => "Integrate GDPR Compliances.",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'skip_on_value' => FALSE,
        'default_value' => 'no',
      ],
    ];
  }

  /**
   * Returns the test data for demo-content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getNextjsApp(): array {
    return [
      'nextjs-app' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_headless'],
        ],
        'question' => "Would you like to preconfigure a next.js site (yes/no) ?",
        'help_text' => "Pre-configure NextJS site.",
        'allowed_values' => [
          'options' => ['yes', 'no'],
        ],
        'skip_on_value' => FALSE,
        'default_value' => 'no',
      ],
    ];
  }

  /**
   * Returns the test data for demo-content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getNextjsAppSiteUrl(): array {
    return [
      'nextjs-app-site-url' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_headless'],
          'questions' => ['${nextjs-app} == "yes"'],
        ],
        'question' => "Please provide the Next.js site url",
        'help_text' => "NextJS Site URL.",
        'default_value' => 'http://localhost:3000',
        'skip_on_value' => FALSE,
        'warning' => "The site url is not set. Using default: 'http://localhost:3000'.\nYou can set the site url later from: /admin/config/services/next.",
      ],
    ];
  }

  /**
   * Returns the test data for demo-content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getNextjsAppSiteName(): array {
    return [
      'nextjs-app-site-name' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_headless'],
          'questions' => ['${nextjs-app} == "yes"'],
        ],
        'question' => "Please provide the Site Name",
        'help_text' => "NextJS Site Name.",
        'default_value' => 'Headless Site',
        'skip_on_value' => FALSE,
        'warning' => "The Site Name is not set. Site Name is set to 'Headless Site'.\nYou can set the site url later from: /admin/config/services/next.",
      ],
    ];
  }

  /**
   * Returns the test data for demo-content Question.
   *
   * @return array[]
   *   Returns an array of question.
   */
  public static function getNextjsAppEvnFile(): array {
    return [
      'nextjs-app-env-file' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_headless'],
          'questions' => ['${nextjs-app} == "yes"'],
        ],
        'question' => "Please provide the .env.local file path",
        'help_text' => "NextJS env file path.",
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
      'sitestudio-api-key' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_enterprise_low_code'],
        ],
        'question' => "Please provide the Site Studio API Key",
        'help_text' => "Site Studio API key.",
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
      'sitestudio-org-key' => [
        'dependencies' => [
          'starter_kits' => ['acquia_cms_enterprise_low_code'],
        ],
        'question' => "Please provide the Site Studio Organization Key",
        'help_text' => "Site Studio Organisation key.",
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
      'gmaps-key' => [
        'dependencies' => [
          'starter_kits' => [
            'acquia_cms_enterprise_low_code',
            'acquia_cms_community',
            'acquia_cms_headless',
          ],
        ],
        'question' => "Please provide the Google Maps API Key",
        'help_text' => "Google Maps key.",
        'warning' => "The Google Maps API key is not set. So, you might see errors, during enable modules step. They are technically harmless, but the maps will not work.\nYou can set the key later from: /admin/tour/dashboard and resave your starter content to generate them.",
      ],
    ];
  }

  /**
   * Function to return data provider for method: alterModulesAndThemes().
   */
  public function alterModuleThemesDataProvider(): array {
    foreach (['acquia_cms_enterprise_low_code', 'acquia_cms_community', 'acquia_cms_headless'] as $bundle) {
      $returnArray = [
        [
          $bundle,
          [
            'demo-content' => 'yes',
          ],
          [
            [
              "modules" => [
                "require" => array_unique(array_merge(
                  $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['require'],
                  $this->getUpdatedModulesThemesArray($bundle, 'demo-content'),
                )),
                "install" => array_unique(array_merge(
                  $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['install'],
                  $this->getUpdatedModulesThemesArray($bundle, 'demo-content'),
                )),
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
            'content-model' => 'yes',
            'demo-content' => 'no',
          ],
          [
            [
              "modules" => [
                "require" => array_unique(array_merge(
                  $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['require'],
                  $this->getUpdatedModulesThemesArray($bundle, 'content-model'),
                )),
                "install" => array_unique(array_merge(
                  $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['install'],
                  $this->getUpdatedModulesThemesArray($bundle, 'content-model'),
                )),
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
            'content-model' => 'no',
            'demo-content' => 'no',
          ],
          [
            [
              "modules" => [
                "require" => $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['require'],
                "install" => $this->getAcmsFileContents()['starter_kits'][$bundle]['modules']['install'],
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
      case 'content-model':
        $packages = [
          "acquia_cms_article",
          "acquia_cms_page",
          "acquia_cms_event",
        ];
        break;

      case 'demo-content':
        $packages = [
          "acquia_cms_article",
          "acquia_cms_page",
          "acquia_cms_event",
          "acquia_cms_starter",
        ];
        break;

      default:
        $packages = [];
        break;
    endswitch;

    return $packages;
  }

  /**
   * Test acquia cms commands for install and build.
   *
   * @param string $command
   *   Command type install or build.
   * @param string $starter_kit
   *   Starter kit name.
   * @param array $options
   *   List of command options.
   * @param array $expected
   *   Expected result.
   *
   * @dataProvider dataProviderAcmsCommands
   */
  public function testAcmsCommands(string $command, string $starter_kit, array $options, array $expected): void {
    $this->assertSame($this->acquiaCli->filterOptionsByStarterKit($command, $options, $starter_kit), $expected);
  }

  /**
   * Provides an array of Acquia CMS command options.
   *
   * @return array
   *   Test data for acms command and expected.
   */
  public function dataProviderAcmsCommands(): array {
    return [
      [
        "build",
        "acquia_cms_enterprise_low_code",
        [
          "demo-content" => "yes",
          "gdpr-integration" => "yes",
          "sitestudio-api-key" => "random-value-1234",
          "sitestudio-org-key" => "org-123454a",
          "account-pass" => "Admin123",
          "site-name" => "Low code site",
        ],
        [
          "--demo-content=yes",
          "--gdpr-integration=yes",
        ],
      ],
      [
        "install",
        "acquia_cms_enterprise_low_code",
        [
          "demo-content" => "yes",
          "gdpr-integration" => "yes",
          "sitestudio-api-key" => "random-value-1234",
          "sitestudio-org-key" => "org-123454a",
          "account-pass" => "Admin123",
          "site-name" => "Low code site",
        ],
        [
          "--account-pass=Admin123",
          "--site-name=Low code site",
          "--sitestudio-api-key=random-value-1234",
          "--sitestudio-org-key=org-123454a",
        ],
      ],
      [
        "build",
        "acquia_cms_community",
        [
          "content-model" => "yes",
          "dam-integration" => "yes",
          "gdpr-integration" => "yes",
          "gmaps-key" => "Abcdef1234",
          "account-pass" => "Admin123",
          "site-name" => "Community site",
        ],
        [
          "--content-model=yes",
          "--dam-integration=yes",
          "--gdpr-integration=yes",
        ],
      ],
      [
        "install",
        "acquia_cms_community",
        [
          "content-model" => "yes",
          "dam-integration" => "yes",
          "gdpr-integration" => "yes",
          "gmaps-key" => "Abcdef1234",
          "account-pass" => "Admin123",
          "site-name" => "Community site",
        ],
        [
          "--account-pass=Admin123",
          "--site-name=Community site",
          "--gmaps-key=Abcdef1234",
        ],
      ],
      [
        "build",
        "acquia_cms_headless",
        [
          "content-model" => "yes",
          "dam-integration" => "yes",
          "nextjs-app" => "yes",
          "nextjs-app-site-url" => "http://localhost:3000",
          "nextjs-app-site-name" => "Headless Site",
          "account-pass" => "Admin123",
          "site-name" => "My Headless site",
        ],
        [
          "--content-model=yes",
          "--dam-integration=yes",
        ],
      ],
      [
        "install",
        "acquia_cms_headless",
        [
          "content-model" => "yes",
          "dam-integration" => "yes",
          "nextjs-app" => "yes",
          "nextjs-app-site-url" => "http://localhost:3000",
          "nextjs-app-site-name" => "Headless Site",
          "account-pass" => "Admin123",
          "site-name" => "My Headless site",
        ],
        [
          "--account-pass=Admin123",
          "--site-name=My Headless site",
          "--nextjs-app=yes",
          "--nextjs-app-site-url=http://localhost:3000",
          "--nextjs-app-site-name=Headless Site",
        ],
      ],
    ];
  }

}
