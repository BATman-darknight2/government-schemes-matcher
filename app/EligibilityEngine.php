<?php
declare(strict_types=1);

final class EligibilityEngine
{
    private const TYPES = [
        'age' => 'number', 'annual_income' => 'number', 'state' => 'string',
        'occupation' => 'string', 'education' => 'string', 'gender' => 'string',
        'social_category' => 'string', 'residence_type' => 'string', 'disability_status' => 'boolean',
        'date_of_birth' => 'date',
    ];
    private const OPERATORS = ['eq','neq','gt','gte','lt','lte','in','not_in','true','false','exists','not_exists','before','after','range'];

    public static function validateRuleDefinition(string $attribute, string $operator, string $expected): ?string
    {
        if (!isset(self::TYPES[$attribute]) || !in_array($operator,self::OPERATORS,true)) return 'Choose a supported attribute and operator.';
        $type=self::TYPES[$attribute];
        if(in_array($operator,['gt','gte','lt','lte','range'],true)&&$type!=='number')return 'Numeric comparisons require age or income.';
        if(in_array($operator,['before','after'],true)&&$type!=='date')return 'Date comparisons require date of birth.';
        if(in_array($operator,['true','false'],true)&&$type!=='boolean')return 'True/false operators require disability status.';
        if(!in_array($operator,['exists','not_exists','true','false'],true)&&$expected==='')return 'This operator requires an expected value.';
        if(in_array($operator,['gt','gte','lt','lte'],true)&&!is_numeric($expected))return 'Enter a numeric expected value.';
        if($type==='number'&&in_array($operator,['eq','neq'],true)&&!is_numeric($expected))return 'Enter a numeric expected value.';
        if($operator==='range'){$b=explode('|',$expected);if(count($b)!==2||!is_numeric($b[0])||!is_numeric($b[1])||(float)$b[0]>(float)$b[1])return 'Enter an inclusive min|max range with min no greater than max.';}
        if(in_array($operator,['in','not_in'],true)&&count(array_filter(array_map('trim',explode('|',$expected)),fn($v)=>$v!==''))===0)return 'Enter one or more values separated by |.';
        if(in_array($operator,['before','after','eq','neq'],true)&&$type==='date'){try{$d=new DateTimeImmutable($expected);if($d->format('Y-m-d')!==$expected)return 'Enter a valid date in YYYY-MM-DD format.';}catch(Throwable){return 'Enter a valid date in YYYY-MM-DD format.';}}
        return null;
    }

    public static function evaluate(array $profile, array $groups, string $groupJoin = 'AND'): array
    {
        $groupJoin = strtoupper($groupJoin);
        if (!in_array($groupJoin, ['AND','OR'], true) || !$groups) {
            return ['status'=>'unable_to_assess','groups'=>[],'results'=>[],'summary'=>'No valid active eligibility rules are configured.'];
        }
        $groupStates = []; $all = []; $invalid = false;
        foreach ($groups as $group) {
            if (empty($group['rules'])) { $invalid = true; continue; }
            $join = strtoupper((string)($group['condition_operator'] ?? 'AND'));
            if (!in_array($join, ['AND','OR'], true)) { $invalid=true; continue; }
            $results = []; $requiredStates = [];
            foreach ($group['rules'] as $rule) {
                $result = self::evaluateRule($profile, $rule);
                $results[] = $result; $all[] = $result;
                if ($result['status'] === 'INVALID' && !empty($rule['mandatory'])) $invalid = true;
                if (!empty($rule['mandatory'])) $requiredStates[] = $result['status'];
            }
            $groupStates[] = $requiredStates ? self::combine($requiredStates, $join) : 'NOT_APPLICABLE';
        }
        if (!$groupStates || $invalid) $overall = 'unable_to_assess';
        else { $combined=self::combine($groupStates,$groupJoin); $overall=match($combined){'PASS'=>'potentially_eligible','FAIL'=>'conditions_not_met','UNKNOWN'=>'more_information',default=>'unable_to_assess'}; }
        $summary = match ($overall) {
            'potentially_eligible' => 'All configured applicable conditions passed. This is a preliminary assessment.',
            'more_information' => 'Some required profile information is missing.',
            'conditions_not_met' => 'One or more applicable required conditions were not met.',
            default => 'The available rules do not support a reliable assessment.',
        };
        return ['status'=>$overall,'groups'=>$groupStates,'results'=>$all,'summary'=>$summary];
    }

    private static function evaluateRule(array $profile, array $rule): array
    {
        $base = ['rule_id'=>$rule['id'] ?? null,'condition'=>$rule['description'] ?? 'Eligibility condition','expected'=>$rule['expected_value'] ?? null,'value'=>null];
        $attr = (string)($rule['attribute_key'] ?? ''); $op = (string)($rule['operator'] ?? '');
        if (!isset(self::TYPES[$attr]) || !in_array($op, self::OPERATORS, true)) return $base + ['status'=>'INVALID','explanation'=>'This rule uses an unsupported attribute or operator.'];
        if (self::validateRuleDefinition($attr,$op,(string)($rule['expected_value']??'')) !== null) return $base + ['status'=>'INVALID','explanation'=>'This rule has an invalid value or an operator incompatible with its attribute.'];
        if (!empty($rule['applicability_attribute'])) {
            $appAttr = (string)$rule['applicability_attribute']; $appVal = $profile[$appAttr] ?? null;
            if (!isset(self::TYPES[$appAttr]) || self::validateRuleDefinition($appAttr,(string)($rule['applicability_operator']??''),(string)($rule['applicability_value']??'')) !== null) return $base + ['status'=>'INVALID','explanation'=>'This rule has an invalid applicability condition.'];
            if ($appVal === null || $appVal === '') return $base + ['status'=>'UNKNOWN','explanation'=>'Information needed to determine whether this condition applies is missing.'];
            $app = self::compare($appVal, (string)($rule['applicability_operator'] ?? ''), $rule['applicability_value'] ?? '', self::TYPES[$appAttr] ?? 'string');
            if ($app === false) return $base + ['status'=>'NOT_APPLICABLE','explanation'=>'This condition does not apply to the supplied profile.'];
            if ($app === null) return $base + ['status'=>'UNKNOWN','explanation'=>'Applicability could not be determined from the profile.'];
        }
        $value = $attr === 'age' ? (isset($profile['age']) && is_numeric($profile['age']) ? (int)$profile['age'] : self::age($profile['date_of_birth'] ?? null)) : ($profile[$attr] ?? null);
        $base['value'] = is_bool($value) ? ($value ? 'Yes' : 'No') : ($value === null ? null : (string)$value);
        if (($value === null || $value === '') && !in_array($op,['exists','not_exists'],true)) return $base + ['status'=>'UNKNOWN','explanation'=>'Enter '.str_replace('_',' ',$attr).' in your profile to evaluate this condition.'];
        try { $pass = self::compare($value, $op, $rule['expected_value'] ?? '', self::TYPES[$attr]); }
        catch (Throwable $e) { return $base + ['status'=>'INVALID','explanation'=>'The configured rule value is invalid.']; }
        if ($pass === null) return $base + ['status'=>'INVALID','explanation'=>'The configured operator is incompatible with its value.'];
        return $base + ['status'=>$pass ? 'PASS' : 'FAIL','explanation'=>$pass ? 'The supplied profile information satisfies this condition.' : 'The supplied profile information does not satisfy this condition.'];
    }

    private static function compare(mixed $actual, string $op, string $expected, string $type): ?bool
    {
        if (!in_array($op, self::OPERATORS, true)) return null;
        if ($op === 'exists') return $actual !== null && $actual !== '';
        if ($op === 'not_exists') return $actual === null || $actual === '';
        if ($op === 'true' || $op === 'false') { if ($type !== 'boolean') return null; return ((string)$actual === '1' || $actual === true) === ($op === 'true'); }
        if ($op === 'in' || $op === 'not_in') { $items = array_map('trim', explode('|',$expected));if($type==='string'){$items=array_map('mb_strtolower',$items);$actual=mb_strtolower((string)$actual);}$ok = in_array((string)$actual,$items,true); return $op === 'in' ? $ok : !$ok; }
        if ($op === 'range') { if ($type !== 'number') return null; $bounds=array_map('trim',explode('|',$expected)); if(count($bounds)!==2||!is_numeric($bounds[0])||!is_numeric($bounds[1])) return null; return (float)$actual >= (float)$bounds[0] && (float)$actual <= (float)$bounds[1]; }
        if ($type === 'number') { if (!is_numeric($actual)||!is_numeric($expected)) return null; $a=(float)$actual; $b=(float)$expected; }
        elseif ($type === 'date') { $a=strtotime((string)$actual); $b=strtotime($expected); if($a===false||$b===false)return null; }
        else { $a=mb_strtolower((string)$actual); $b=mb_strtolower($expected); }
        return match ($op) { 'eq'=>$a==$b, 'neq'=>$a!=$b, 'gt'=>$a>$b, 'gte'=>$a>=$b, 'lt'=>$a<$b, 'lte'=>$a<=$b, 'before'=>$type==='date' ? $a<$b : null, 'after'=>$type==='date' ? $a>$b : null, default=>null };
    }
    private static function combine(array $states, string $join): string
    {
        if (!$states) return 'UNKNOWN';
        $applicable = array_values(array_filter($states, fn($s)=>$s!=='NOT_APPLICABLE'));
        if (!$applicable) return 'NOT_APPLICABLE';
        if ($join === 'OR') { if(in_array('PASS',$applicable,true)) return 'PASS'; if(in_array('UNKNOWN',$applicable,true)) return 'UNKNOWN'; if(in_array('INVALID',$applicable,true)) return 'INVALID'; return 'FAIL'; }
        if(in_array('FAIL',$applicable,true)) return 'FAIL'; if(in_array('UNKNOWN',$applicable,true)) return 'UNKNOWN'; if(in_array('INVALID',$applicable,true)) return 'INVALID'; return 'PASS';
    }
    private static function age(mixed $dob): ?int
    {
        if (!$dob) return null; try { return (new DateTimeImmutable((string)$dob))->diff(new DateTimeImmutable('today'))->y; } catch(Throwable) { return null; }
    }
}
