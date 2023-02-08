<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\InstallQuestions;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to run commands post task executed.
 *
 * @Task(
 *   id = "post_site_install_task",
 *   weight = 20,
 * )
 */
class PostSiteInstallTask extends BaseTask {

  /**
   * Holds the formatter_helper object.
   *
   * @var \Symfony\Component\Console\Helper\FormatterHelper
   */
  protected $formatterHelper;

  /**
   * Holds the install_questions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallQuestions
   */
  protected $questions;

  /**
   * Constrcts the task object.
   *
   * @param \Symfony\Component\Console\Helper\FormatterHelper $formatter_helper
   *   A format_helper object.
   * @param \AcquiaCMS\Cli\Helpers\InstallQuestions $questions
   *   An install_questions service object.
   */
  public function __construct(FormatterHelper $formatter_helper, InstallQuestions $questions) {
    $this->formatterHelper = $formatter_helper;
    $this->questions = $questions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $command->getHelper('formatter'),
      $container->get('install_questions'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selected_starter_kit = $this->questions->getAnswer("starter_kit");
    $output->writeln("");
    $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully setup your project using bundle: `$selected_starter_kit`.";
    $formattedInfoBlock = $this->formatterHelper->formatBlock($infoMessage, 'fg=black;bg=green', TRUE);
    $output->writeln($formattedInfoBlock);
    $output->writeln("");
    return StatusCode::OK;
  }

}
