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

namespace JustBetter\AkeneoBundle\Cron;

use JustBetter\AkeneoBundle\Job\RunSlackMessage;

class SlackNotificationCron
{
    public function __construct(
        protected RunSlackMessage $runSlackMessage
    ) {
    }

    public function execute(): void
    {
        $this->runSlackMessage->execute();
    }
}
