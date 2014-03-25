<?php

namespace Mrss\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class MinimumSelected
 *
 * Attach to a multiselect or multicheckbox to validate that at least X items
 * were selected.
 *
 * @package Mrss\Validator
 */
class MinimumSelected extends AbstractValidator
{
    const TOO_FEW = 'tooFew';

    protected $minimumSelected;

    protected $messageTemplates = array(
        self::TOO_FEW => "Select at least %minimumSelected% items.",
    );

    protected $messageVariables = array(
        'minimumSelected' => 'minimumSelected'
    );

    public function __construct($minimumSelected = 5)
    {
        $this->minimumSelected = $minimumSelected;

        parent::__construct();
    }

    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $count = count($value);

        if ($count < $this->minimumSelected) {
            $this->error(self::TOO_FEW);
            return false;
        }

        return true;
    }
}
