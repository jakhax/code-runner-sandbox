<?php


namespace App\Service\Sandbox;

use App\Service\Sandbox\runners\RunnerInterface;
use App\Service\Sandbox\runners\RunnersContainer;
use App\Utils\SimpleArrayValidation;
use Psr\Log\LoggerInterface;

class SandboxService implements SandboxInterface
{
    /**
     * @var RunnerInterface
     */
    private $runner;

    /**
     * @var RunnersContainer
     */
    private $runnersContainer;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(RunnersContainer $runnersContainer,LoggerInterface $logger)
    {
        $this->logger=$logger;
        $this->runnersContainer=$runnersContainer;
    }

    public function start(array $opt): ?array
    {
        /**
         * - for run strategy
         * [
         *  "languageName", "strategy", "solutionCode",
         * ]
         * - for test strategy
         *
         * [
         *  "languageName", "strategy", "solutionCode","setupCode",
         * "testFramework", "testCode",
         * ]
         */
        $requiredFields=[
            "languageName"=>[
                "type"=>"string"
            ],
            "strategy"=>[
                "type"=>"string",
            ],
            "solutionCode"=>[
                "type"=>"string"
            ],
        ];
        $validateArray=SimpleArrayValidation::simpleValidateArraySchema($requiredFields,$opt);
        if($validateArray["error"]){
            return $validateArray;
        }

        $this->runner=$this->runnersContainer->runnersArrayContainer[$opt["languageName"]];
        $strategy=$opt["strategy"];

        $this->logger->info("LANGUAGE INFO",["info"=>$this->runner->getLanguageInfo()]);
        $supportedLanguageStrategies=$this->runner->getLanguageInfo()["strategies"];
        if(!in_array($strategy,$supportedLanguageStrategies)){
            return ["error"=>true,"errorMessage"=>"Unsupported Language strategy: $strategy"];
        }
        switch ($strategy){
            case $strategy==="test":
                $fields=[
                    "testFramework"=>[
                        "type"=>"string"
                    ],
                    "testCode"=>[
                        "type"=>"string"
                    ],
                    "setupCode"=>[
                        "type"=>"string"
                    ]
                ];
                $validateArray= SimpleArrayValidation::simpleValidateArraySchema($fields,$opt);
                if($validateArray["error"]){
                    return $validateArray;
                }
                $this->runner->setup(["strategy"=>$strategy]);
                $output =$this->runner->runTestStrategy($opt);
                break;
            case $strategy==="run":
                $this->runner->setup(["strategy"=>$strategy]);
                $output=$this->runner->runCodeStrategy($opt);
                break;
            default:
                return ["error"=>true,"errorMessage"=>"sandbox unsupported strategy $strategy"];
        }
        return ["error"=>false,"data"=>$output];

    }



}