<?php
declare(strict_types=1);
namespace PHPUnit\Framework {
    class TestCase {
        public static function assertSame(mixed $expected,mixed $actual,string $message=''): void {
            if($expected!==$actual) throw new \RuntimeException(($message?:'Values differ').': expected '.var_export($expected,true).', got '.var_export($actual,true));
        }
    }
}
namespace {
    require_once dirname(__DIR__).'/app/EligibilityEngine.php';
    require_once __DIR__.'/Unit/EligibilityEngineTest.php';
    $suite=new \EligibilityEngineTest();$passed=0;$failed=0;
    foreach(get_class_methods($suite) as $method){if(!str_starts_with($method,'test'))continue;try{$suite->$method();$passed++;fwrite(STDOUT,"PASS $method\n");}catch(\Throwable $e){$failed++;fwrite(STDOUT,"FAIL $method: {$e->getMessage()}\n");}}
    fwrite(STDOUT,"\n$passed passed, $failed failed\n");exit($failed?1:0);
}
