<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action.
 */

namespace Bexio\PrometheusPHP;

/**
 * Action describes a metric change.
 *
 * Actions are internally used to track all the changes which then gets applied when the Metric gets persisted to a
 * storage adapter.
 */
interface Action
{
    /**
     * Executes the action.
     *
     * Applies the action to the given storage adapter.
     *
     * @param StorageAdapter $storage
     */
    public function execute(StorageAdapter $storage);
}
