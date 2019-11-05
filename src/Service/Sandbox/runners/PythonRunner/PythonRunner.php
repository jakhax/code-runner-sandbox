<?php


namespace App\Service\Sandbox\runners\PythonRunner;

use App\Service\Sandbox\runners\BaseRunner\BaseRunner;
use App\Service\Sandbox\runners\RunnerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class PythonRunner extends BaseRunner
{
    /**
     * @var string
     */
    private $tempCodeFolder;
    /**
     * @var string
     */
    private $dockerImage;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        parent::__construct($parameterBag, $logger);
        $this->dockerImage="code-kombat/python-runner";
        $this->setLanguageInfo("python3");
    }

    public function runCodeStrategy(array $data): array
    {
        $setupCode="";
        $this->writeCodeToFile($this->tempCodeFolder.'/setup.py',$setupCode,"w");
        $solutionCode=$data["solutionCode"];
        $solutionCode="from setup import *\n".$solutionCode;
        $this->writeCodeToFile($this->tempCodeFolder.'/solution.py',$solutionCode,"w");
        $dockerVolume="-v $this->tempCodeFolder:/home/appuser/";
        $pythonCommand="python3 solution.py";
        $command="docker run --rm --name jj23 $dockerVolume $this->dockerImage $pythonCommand";
        $output=$this->executeShellCommand($command);
        $this->cleanUp();
        return $output;
    }

    public function runTestStrategy(array $data): array
    {
        $testFramework=$data["testFramework"];
        switch ($testFramework){
            case $testFramework=="unit-test":
                $output=$this->python3UnitTest($data);
                break;
            default:
                $output=["error"=>true,"errorMessage"=>"Invalid test framework"];
                break;
        }
        return $output;
    }

    public function python3UnitTest(array $data):array {
        /**
         * .
        |-- setup.py
        |-- solution.py
        `-- test
        |-- __init__.py
        |-- __main__.py
        `-- test_solution.py
        |-- test-framework
         *
         **/
//        setup code
        $setupCode=$data["setupCode"];
        $this->writeCodeToFile($this->tempCodeFolder.'/setup.py',$setupCode,"w");
//        solution code
        $solutionCode=$data["solutionCode"];
        $solutionCode="from setup import *\n".$solutionCode;
        $this->writeCodeToFile($this->tempCodeFolder.'/solution.py',$solutionCode,"w");

//        test setup
        mkdir($this->tempCodeFolder."/test");
        $this->writeCodeToFile($this->tempCodeFolder."/test/__init__.py","","w");
        $unitTestSetupCode=[
            'import unittest',
            'import sys',
            'sys.path.insert(0,"/usr/lib/python3.6/code_kombat_test_frameworks")',
            'from code_kombat_test_frameworks import CodeKombatTestRunner',
            'import timeout_decorator',
            'def load_tests(loader, tests, pattern):',
            '    return loader.discover(".")',
            'GLOBAL_TIMEOUT=3',
            'timeout_decorator.timeout(GLOBAL_TIMEOUT)(unittest.main)(testRunner=CodeKombatTestRunner())',
        ];
        $unitTestSetupCode=implode("\n",$unitTestSetupCode);
        $this->writeCodeToFile($this->tempCodeFolder."/test/__main__.py",$unitTestSetupCode,"w");

        $testCode=$data["testCode"];
        $setupTestCode=[
            'from solution import *',
            'import unittest'
        ];
        $testCode=implode("\n",$setupTestCode) . $testCode;
        $this->writeCodeToFile($this->tempCodeFolder."/test/test_solution.py",$testCode,"w");


        $dockerVolume="-v $this->tempCodeFolder:/home/appuser/";
        $dockerImage="code-kombat/python-runner";
        $pythonCommand="python3 test";

        $command="docker run --rm --name jj23 $dockerVolume $dockerImage $pythonCommand";
        $output=$this->executeShellCommand($command);
//        $this->cleanUp();
        return $output;
    }

    public function setup(array $setupInfo = null): ?array
    {
        $this->tempCodeFolder= $this->createTempFolder();
        return ["error"=>false];
    }

    public function cleanUp(array $cleanupInfo = null): ?array
    {
        $process= new Process(["rm","-rf",$this->tempCodeFolder]);
        $process->run();
        return ["error"=>false];
    }

}