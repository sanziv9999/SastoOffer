<?php

namespace App\Services;

class OfferRuleCalculator
{
    /**
     * Evaluate the final price based on the base price, parameters, and rule.
     */
    public function evaluateFinalPrice(float $basePrice, array $params, array $rule): ?float
    {
        $formula = $rule['formula_final_price'] ?? null;
        if (!$formula) {
            return null;
        }

        // Extremely basic evaluation for demo purposes. 
        // In production, use a proper expression evaluator.
        try {
            $expression = $formula;
            $vars = array_merge(['original_price' => $basePrice], $params);

            foreach ($vars as $key => $value) {
                $expression = str_replace($key, (float)$value, $expression);
            }

            // Simple math evaluation
            // WARNING: eval() is used here for simplicity in this prototype.
            // A safer calculator should be used in production.
            return eval("return $expression;");
        } catch (\Throwable $e) {
            return null;
        }
    }
}
