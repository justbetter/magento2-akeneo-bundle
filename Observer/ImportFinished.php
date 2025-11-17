<?php
/**
 * JustBetter Magento2 Akeneo Bundle
 *
 * @author JustBetter B.V.
 * @copyright Copyright (c) JustBetter B.V. (https://justbetter.nl)
 * @package Magento2 Akeneo Bundle
 *
 * Licensed under the GNU General Public License v3.0 or later.
 * For full license information, see the LICENSE file
 * or visit <https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE>.
 */

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Observer;

use Akeneo\Connector\Executor\JobExecutor;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ImportFinished implements ObserverInterface
{
    public function __construct(
        protected Manager $eventManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var JobExecutor $executor */
        $executor = $observer->getData('import');

        $method = $executor->getMethod();

        // Only dispatch for cleanCache method (end of import)
        if ($method === 'cleanCache') {
            $code = $executor->getCurrentJob()->getCode();
            $event = 'akeneo_connector_import_finish_' . $code;

            $this->eventManager->dispatch($event, ['import' => $executor]);
        }
    }
}
