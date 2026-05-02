<?php

/**
 * The command that will handle the deletion of a Staging Site from the command line.
 *
 * @package WPStaging\Pro\WpCli\Commands
 */

namespace WPStaging\Pro\WpCli\Commands;

use Exception;
use WP_CLI;
use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\BackgroundProcessing\PrepareDelete;
use WPStaging\Staging\Traits\StagingSiteGetterTrait;

/**
 * Class StagingSiteDeleteCommand
 *
 * @package WPStaging\Pro\WpCli\Commands
 */
class StagingSiteDeleteCommand implements CommandInterface
{
    use StagingSiteGetterTrait;

    /**
     * Delete the Staging Site using the Background Processing system.
     *
     * @param array               $args      A list of the positional arguments provided by the user, already validated.
     * @param array<string,mixed> $assocArgs A map of the associative arguments, options and flags, provided by the user.
     * @return mixed This method will return mixed values depending on the class that is invoked.
     * @throws WP_CLI\ExitException If the Job preparation fails, then a message will be provided to the user
     *                              detailing the reason.
     */
    public function __invoke(array $args = [], array $assocArgs = [])
    {
        $options = $this->extractOptionsFromArgs($args);
        $data = $this->setupStagingSiteDeleteData($options);

        try {
            $jobId = WPStaging::make(PrepareDelete::class)->prepare($data);

            if ($jobId instanceof \WP_Error) {
                WP_CLI::error('Failed to prepare request for deleting staging site: ' . $jobId->get_error_message());
            }

            $quiet = isset($assocArgs['quiet']);
            if (!$quiet) {
                WP_CLI::success(
                    sprintf(
                        "Staging Site deletion prepared with Job ID %s\nUse the \"%s\" command to check its status.",
                        $jobId,
                        "wp wpstg status '$jobId'"
                    )
                );
            } else {
                WP_CLI::line($jobId);
            }
        } catch (\Exception $e) {
            WP_CLI::error('Exception thrown while preparing the deletion of staging site: ' . $e->getMessage());
        }
    }

    protected function extractOptionsFromArgs(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            $parts = explode('=', $arg);
            if (count($parts) === 2) {
                $options[$parts[0]] = $parts[1];
            }
        }

        return $options;
    }

    protected function setupStagingSiteDeleteData(array $options): array
    {
        $data = [
            'isDeletingTables' => true,
            'isDeletingFiles'  => true,
            'excludedTables'   => [],
        ];

        try {
            $data['cloneId'] = $this->validateStagingSiteByIdOrName($options);
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }

        $data['isWpCliRequest'] = true;

        return $data;
    }
}
