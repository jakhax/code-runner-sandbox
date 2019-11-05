<?php


namespace App\Service\Sandbox\runners;


use App\Service\Sandbox\runners\PythonRunner\PythonRunner;

class RunnersContainer
{
    /**
     * @var RunnerInterface[]
     */
    public $runnersArrayContainer;

    public function __construct(
        PythonRunner $pythonRunner
    )
    {
        $runners=[$pythonRunner];
        foreach ($runners as $runner){
            $this->runnersArrayContainer[
                $runner->getLanguageInfo()["name"]
                ]=$runner;
        }
    }

}