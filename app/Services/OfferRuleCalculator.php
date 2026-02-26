<?php

namespace App\Services;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class OfferRuleCalculator
{
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage();
    }

    /**
     * Extract the expression that computes final price from calculation_rule.
     * Supports:
     * - formula_final_price: raw expression, e.g. "original_price * (1 - discount_percent/100)"
     * - formula: legacy "final_price = ..." format; the RHS is used as the expression
     */
    public function getFinalPriceExpression(array $rule): ?string
    {
        if (!empty($rule['formula_final_price']) && is_string($rule['formula_final_price'])) {
            return trim($rule['formula_final_price']);
        }
        if (!empty($rule['formula']) && is_string($rule['formula'])) {
            $formula = trim($rule['formula']);
            if (str_contains($formula, '=')) {
                $parts = explode('=', $formula, 2);
                return trim($parts[1]);
            }
            return $formula;
        }
        return null;
    }

    /**
     * Evaluate the formula with given original_price and params.
     * Returns the computed final price, or null if expression is missing or evaluation fails.
     *
     * @param  array<string, mixed>  $params
     */
    public function evaluateFinalPrice(float $originalPrice, array $params, array $rule): ?float
    {
        $expression = $this->getFinalPriceExpression($rule);
        if ($expression === null) {
            return null;
        }

        $variables = array_merge(
            ['original_price' => $originalPrice],
            $this->castParamsToNumeric($params)
        );

        try {
            $result = $this->expressionLanguage->evaluate($expression, $variables);
            return is_numeric($result) ? (float) $result : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Cast param values to numeric for use in expressions (non-numeric become 0).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, float>
     */
    protected function castParamsToNumeric(array $params): array
    {
        $out = [];
        foreach ($params as $key => $value) {
            $out[$key] = is_numeric($value) ? (float) $value : 0;
        }
        return $out;
    }
}
