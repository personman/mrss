<?php

namespace Mrss\Validator;

use Zend\Validator\AbstractValidator;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Service\ComputedFields;

class Equation extends AbstractValidator
{
    const INVALID = 'invalid';
    const REQUIRED = 'required';
    const UNKNOWN_VARIABLE = 'uv';

    /**
     * @var ComputedFields
     */
    protected $computedFields;

    /**
     * @var BenchmarkModel
     */
    protected $benchmarkModel;

    protected $variable;
    protected $incalculableReason;

    protected $messageTemplates = array(
        self::INVALID => "Invalid equation. %incalculableReason%",
        self::UNKNOWN_VARIABLE
            => "The variable '%variable%' does not match any benchmarks.",
        self::REQUIRED => "A computed benchmark won't work without an equation."
    );

    protected $messageVariables = array(
        'variable' => 'variable',
        'incalculableReason' => 'incalculableReason'
    );

    public function __construct($computedFields, $benchmarkModel)
    {
        $this->computedFields = $computedFields;
        $this->benchmarkModel = $benchmarkModel;

        parent::__construct();
    }

    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        // First look to see if this is even a computed inputType
        if ($context['inputType'] == 'computed') {

            // Equation is required
            if (empty($value)) {
                $this->error(self::REQUIRED);

                return false;
            }

            // Make sure that the variables are valid
            $variables = $this->computedFields->getVariables($value);

            foreach ($variables as $variable) {
                if (!$this->benchmarkModel->findOneByDbColumn($variable)) {
                    $this->variable = $variable;
                    $this->error(self::UNKNOWN_VARIABLE);

                    return false;
                }
            }

            // Check to see if the equation will calculate
            if (!$this->isCalculable($value)) {
                $this->error(self::INVALID);

                return false;
            }

        }

        return true;
    }

    /**
     * Use some random numbers to see if the equation is even valid
     *
     * @param $equation
     * @return bool
     */
    public function isCalculable($equation)
    {
        try {
            $variables = $this->computedFields->getVariables($equation);

            $randoms = array();
            foreach ($variables as $variable) {
                $randoms[$variable] = rand(1, 100);
            }

            $equation = $this->computedFields->buildEquation($equation);
            $equation->setVars($randoms);

            $equation->evaluate();

            return true;
        } catch (\Exception $e) {
            // Remove the word "yet" from the message
            $message = $e->getMessage();
            $message = str_replace(' yet', '', $message);
            $this->incalculableReason = $message;

            return false;
        }
    }
}
