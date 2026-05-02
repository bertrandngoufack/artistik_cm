<?php

/**
 * Allows checking on the status of a Backup prepared for background processing.
 *
 * @package WPStaging\Pro\WpCli\Commands
 */

namespace WPStaging\Pro\WpCli\Commands;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Action;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WP_CLI;
use WPStaging\Backup\Dto\Task\Backup\Response\FinalizeBackupResponseDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Staging\Dto\Task\Response\FinishStagingSiteResponseDto;

/**
 * Class StatusCommand
 *
 * @package WPStaging\Pro\WpCli\Commands
 */
class StatusCommand implements CommandInterface
{

    /**
     * Checks the status of a any job that is being created using the Background Processing system.
     *
     * @param array               $args      A list of the positional arguments provided by the user, already validated.
     * @param array<string,mixed> $assocArgs A map of the associative arguments, options and flags, provided by the user.
     * @return mixed This method will return mixed values depending on the class that is invoked.
     * @throws WP_CLI\ExitException If the job preparation fails, then a message will be provided to the user
     *                              detailing the reason.
     */
    public function __invoke(array $args = [], array $assocArgs = [])
    {
        if (!isset($args[0]) || $args[0] === '') {
            WP_CLI::error('Missing required argument: <jobId>');
        }

        $queue = WPStaging::make(Queue::class);
        $jobId = (string)$args[0];
        $action = $queue->getLatestUpdatedAction($jobId);

        if (!$action instanceof Action) {
            WP_CLI::error('Could not find any Action associated to the Job ID.');
        }

        if (!class_exists(FinalizeBackupResponseDto::class)) {
            WP_CLI::error('Finalize Response DTO class does not exist.');
        }

        if (!class_exists(FinishStagingSiteResponseDto::class)) {
            WP_CLI::error('Finish Staging Site Response DTO class does not exist.');
        }

        $dto = $action->response;

        if (!$dto instanceof TaskResponseDto) {
            $responseDetails = '';
            if (isset($action->response)) {
                if (is_scalar($action->response) || $action->response === null) {
                    $responseDetails = (string)$action->response;
                } else {
                    $encodedResponse = wp_json_encode($action->response);
                    $responseDetails = $encodedResponse !== false ? $encodedResponse : print_r($action->response, true);
                }
            }

            WP_CLI::error(
                sprintf(
                    'Could not find any DTO associated to latest Action for the Job ID %s. Dto: %s',
                    $jobId,
                    $responseDetails
                )
            );
        }

        if ($dto instanceof FinalizeBackupResponseDto && !$dto->isRunning()) {
            WP_CLI::success('This Backup has already been finished.');
        } elseif ($dto instanceof FinishStagingSiteResponseDto && !$dto->isRunning()) {
            WP_CLI::success('This Staging Site process has already been finished.');
        } else {
            WP_CLI::log('This job is still being processed.');
        }

        $dtoData = $dto->toArray();
        WP_CLI::print_value(json_encode($dtoData, JSON_PRETTY_PRINT));
    }
}
