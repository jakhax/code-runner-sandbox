<?php


namespace App\Service\Sandbox\runners;


interface RunnerInterface
{
    public function runCodeStrategy(array $data):array;
    public function runTestStrategy(array $data):array;
    public function setup(array $setupInfo=null):?array;
    public function cleanUp(array $cleanupInfo=null):?array;
    public function setLanguageInfo(string $languageName):self;
    public function getLanguageInfo():array;
}