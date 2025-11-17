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

use JustBetter\AkeneoBundle\Job\SetNotVisible as SetNotVisibleJob;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetNotVisible implements ObserverInterface
{
    public function __construct(
        protected SetNotVisibleJob $job
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->job->execute();
    }
}
