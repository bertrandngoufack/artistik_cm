<?php

use WPStaging\Framework\Database\TableDto;
use WPStaging\Staging\Dto\StagingSiteDto;

/**
 * @var StagingSiteDto $stagingSiteDto
 * @var TableDto[]     $tables
 * @var string[]       $selectedTables
 * @var string[]       $disabledTables
 *
 * @see WPStaging\Staging\Service\TableScanner::renderTablesSelection
 */
// needed to close and reopen PHP tags (any other harmless statement before foreach could work too) otherwise phpstan fails
?>
<?php
/** @var TableDto $table */
foreach ($tables as $table) :
    $attributes = '';
    // select tables based on previous push selection
    if (in_array($table->getName(), $selectedTables)) {
        $attributes = 'selected';
    }

    // disable the table if it is excluded by filter
    if (in_array($table->getName(), $disabledTables)) {
        $attributes = 'disabled';
    }

    ?>
    <option class="wpstg-db-table" value="<?php echo esc_attr($table->getName()); ?>" name="<?php echo esc_attr($table->getName()); ?>" <?php echo esc_html($attributes); ?>>
        <?php echo esc_html($table->getName()); ?> - <?php echo esc_html(size_format($table->getSize(), 2)); ?>
    </option>
<?php endforeach ?>
