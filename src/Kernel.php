<?php

namespace AcquiaCMS\Cli;

use Acquia\DrupalEnvironmentDetector\AcquiaDrupalEnvironmentDetector;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Manages an environment made of bundles.
 */
class Kernel extends BaseKernel {

  /**
   * {@inheritdoc}
   */
  public function getCacheDir(): string {
    // The cache directory has been changed to `/tmp/caches`
    // because Acquia Cloud makes vendor directory as read-only, and we need
    // some cache directory where Acquia Cloud can create caches.
    if (AcquiaDrupalEnvironmentDetector::isAhEnv()) {
      return "/tmp/caches";
    }
    return "{$this->getProjectDir()}/var/caches";
  }

  /**
   * {@inheritdoc}
   */
  public function getLogDir(): string {
    // The logs directory has been changed to `/tmp/logs`
    // because Acquia Cloud makes vendor directory as read-only, and we need
    // some log directory where Acquia Cloud can store logs.
    if (AcquiaDrupalEnvironmentDetector::isAhEnv()) {
      return "/tmp/logs";
    }
    return "{$this->getProjectDir()}/var/logs";
  }

  /**
   * {@inheritdoc}
   */
  public function registerBundles(): iterable {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function registerContainerConfiguration(LoaderInterface $loader): void {
    $loader->load("{$this->getProjectDir()}/config/services.yml");
  }

  /**
   * {@inheritdoc}
   */
  protected function build(ContainerBuilder $container): void {
    $container->addCompilerPass($this->createCollectingCompilerPass());
    $container->getParameterBag()->set("app.base_dir", $this->getBaseDirectory());
  }

  /**
   * Creates a collecting compiler pass.
   *
   * @SuppressWarnings(PHPMD.UndefinedVariable)
   */
  private function createCollectingCompilerPass(): CompilerPassInterface {
    return new class implements CompilerPassInterface {

      /**
       * {@inheritdoc}
       */
      public function process(ContainerBuilder $container): void {
        $app_definition = $container->findDefinition(Application::class);

        foreach ($container->getDefinitions() as $definition) {
          if (!is_a($definition->getClass(), Command::class, TRUE)) {
            continue;
          }

          $app_definition->addMethodCall('add', [
            new Reference($definition->getClass()),
          ]);
        }
      }

    };
  }

  /**
   * Function returns the base directory of the project.
   *
   * @return string
   *   Returns the base directory of the project.
   */
  public function getBaseDirectory() :string {
    $dirName = dirname(dirname(__FILE__));

    // If the command is added on acquia or drupal recommended-project,
    // it returns vendor path, so remove that from path.
    $dirName = preg_replace('/\/vendor\/.*/', '', $dirName);
    if (getenv('CI')) {
      return getcwd();
    }
    return $dirName;
  }

}
