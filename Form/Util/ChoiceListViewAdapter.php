<?php

/*
 * (c) Infinite Networks <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\FormBundle\Form\Util;

use Symfony\Component\Form\ChoiceList\View\ChoiceListView;

class ChoiceListViewAdapter extends ChoiceListView
{
    public function __construct(ChoiceListView $wrapped)
    {
        parent::__construct($wrapped->choices, $wrapped->preferredChoices);
    }

    /**
     * BC for old form themes running on new Symfony.
     *
     * @deprecated use the public $choices property
     */
    public function getRemainingViews()
    {
        return $this->choices;
    }
}
