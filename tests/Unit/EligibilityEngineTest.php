<?php
use PHPUnit\Framework\TestCase;

final class EligibilityEngineTest extends TestCase
{
    private function group(array $rules, string $op='AND'): array { return [['condition_operator'=>$op,'rules'=>$rules]]; }
    private function rule(string $attr,string $op,string $expected,string $desc='test',int $mandatory=1): array { return ['id'=>1,'attribute_key'=>$attr,'operator'=>$op,'expected_value'=>$expected,'description'=>$desc,'mandatory'=>$mandatory]; }

    public function testNumericBoundaryPasses(): void { $r=EligibilityEngine::evaluate(['annual_income'=>300000],$this->group([$this->rule('annual_income','lte','300000')]));self::assertSame('potentially_eligible',$r['status']);self::assertSame('PASS',$r['results'][0]['status']); }
    public function testNumericAboveLimitFails(): void { $r=EligibilityEngine::evaluate(['annual_income'=>300001],$this->group([$this->rule('annual_income','lte','300000')]));self::assertSame('conditions_not_met',$r['status']); }
    public function testMissingProfileValueIsUnknown(): void { $r=EligibilityEngine::evaluate([], $this->group([$this->rule('state','eq','Rajasthan')]));self::assertSame('more_information',$r['status']);self::assertSame('UNKNOWN',$r['results'][0]['status']); }
    public function testAndRequiresEveryCondition(): void { $g=$this->group([$this->rule('state','eq','Rajasthan'),$this->rule('occupation','eq','Student')]);$r=EligibilityEngine::evaluate(['state'=>'Rajasthan','occupation'=>'Farmer'],$g);self::assertSame('conditions_not_met',$r['status']); }
    public function testOrPassesWhenOneConditionMatches(): void { $g=$this->group([$this->rule('occupation','eq','Student'),$this->rule('occupation','eq','Unemployed')],'OR');$r=EligibilityEngine::evaluate(['occupation'=>'Unemployed'],$g);self::assertSame('potentially_eligible',$r['status']); }
    public function testUnknownOrFailNeedsInformationUnlessAnotherPasses(): void { $g=$this->group([$this->rule('state','eq','Rajasthan'),$this->rule('occupation','eq','Unemployed')],'OR');$r=EligibilityEngine::evaluate([], $g);self::assertSame('more_information',$r['status']); }
    public function testInclusiveNumericRange(): void { $r=EligibilityEngine::evaluate(['age'=>18],$this->group([$this->rule('age','range','18|25')]));self::assertSame('PASS',$r['results'][0]['status']); }
    public function testListComparisonIgnoresCaseOnlyForEqualsNotList(): void { $r=EligibilityEngine::evaluate(['state'=>'Rajasthan'],$this->group([$this->rule('state','in','Rajasthan|Haryana')]));self::assertSame('PASS',$r['results'][0]['status']); }
    public function testUnknownOperatorIsInvalidAndUnableToAssess(): void { $rule=$this->rule('age','execute','1');$r=EligibilityEngine::evaluate(['date_of_birth'=>'2000-01-01'],$this->group([$rule]));self::assertSame('unable_to_assess',$r['status']);self::assertSame('INVALID',$r['results'][0]['status']); }
    public function testOptionalFailDoesNotChangeOverallResult(): void { $rules=[$this->rule('state','eq','Rajasthan','Optional note',0)];$r=EligibilityEngine::evaluate(['state'=>'Delhi'],$this->group($rules));self::assertSame('unable_to_assess',$r['status']); }
    public function testNotApplicableConditionIsRecorded(): void { $rule=$this->rule('annual_income','lte','300000');$rule['applicability_attribute']='occupation';$rule['applicability_operator']='eq';$rule['applicability_value']='Student';$r=EligibilityEngine::evaluate(['occupation'=>'Farmer','annual_income'=>900000],$this->group([$rule]));self::assertSame('NOT_APPLICABLE',$r['results'][0]['status']); }
    public function testMalformedGroupCannotPass(): void { $r=EligibilityEngine::evaluate([], [['condition_operator'=>'XOR','rules'=>[$this->rule('state','eq','A')]]]);self::assertSame('unable_to_assess',$r['status']); }
    public function testEmptyRulesCannotPass(): void { $r=EligibilityEngine::evaluate([], $this->group([]));self::assertSame('unable_to_assess',$r['status']); }
    public function testGroupsCanCombineWithOr(): void { $groups=[['condition_operator'=>'AND','rules'=>[$this->rule('state','eq','Rajasthan')]],['condition_operator'=>'AND','rules'=>[$this->rule('occupation','eq','Student')]]];$r=EligibilityEngine::evaluate(['state'=>'Delhi','occupation'=>'Student'],$groups,'OR');self::assertSame('potentially_eligible',$r['status']); }
    public function testIncompatibleRuleDefinitionIsRejected(): void { self::assertSame('Numeric comparisons require age or income.',EligibilityEngine::validateRuleDefinition('state','gte','4')); }
    public function testAgeIsDerivedFromDateOfBirth(): void { $r=EligibilityEngine::evaluate(['date_of_birth'=>'1990-01-01'],$this->group([$this->rule('age','gte','18')]));self::assertSame('PASS',$r['results'][0]['status']); }
    public function testExistsOperatorsEvaluateMissingFields(): void { $a=EligibilityEngine::evaluate([], $this->group([$this->rule('state','exists','')]));$b=EligibilityEngine::evaluate([], $this->group([$this->rule('state','not_exists','')]));self::assertSame('FAIL',$a['results'][0]['status']);self::assertSame('PASS',$b['results'][0]['status']); }
    public function testStringMembershipIsCaseInsensitive(): void { $r=EligibilityEngine::evaluate(['state'=>'rajasthan'],$this->group([$this->rule('state','in','Rajasthan|Haryana')]));self::assertSame('PASS',$r['results'][0]['status']); }
}
