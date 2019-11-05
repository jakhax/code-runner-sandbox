<?php


namespace App\Service\Sandbox\runners\BaseRunner;


use App\Service\Sandbox\runners\RunnerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

abstract class BaseRunner implements RunnerInterface
{
    /**
     * @var ParameterBagInterface
     */
    public $params;

    /**
     * @var array
     */
    public $languageInfo;

    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->params=$parameterBag;
        $this->logger=$logger;

    }

    static public function parseStr(Type $var = null)
    {
        # code...
    }
    static function getTempFolder(){
        return $_ENV["TEMP_KOMBAT_FOLDER"];
    }

    public function writeCodeToFile(string $path, string $contents, string $mode){
        $fp=fopen($path,$mode);
        fwrite($fp,$contents);
        fclose($fp);
    }

    public function createTempFolder(){

        $pRandomFolder=hash("sha256",rand());
        $tempFolder=$this->params->get('kernel.project_dir')."/public/tempKombats/".$pRandomFolder;
        $this->logger->info("TEMP_DIR",["temp"=>$tempFolder]);
        mkdir($tempFolder);
        return $tempFolder;
    }

    public function executeShellCommand(string $command):array {
        $outputBuffer=[];
        $errorBuffer=[];
        $process= Process::fromShellCommandline($command);
        $process->start();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $outputBuffer[]=$data;

            } else { // $process::ERR === $type
                $errorBuffer[]=$data;

            }
        }
        $outputBuffer=implode("",$outputBuffer);
        $errorBuffer=implode("",$errorBuffer);
        return ["outputBuffer"=>$outputBuffer,"errorBuffer"=>$errorBuffer];
    }

    public function runCodeStrategy(array $data): array
    {
        // TODO: Implement runCodeStrategy() method.
        return [];
    }

    public function runTestStrategy(array $data): array
    {
        // TODO: Implement runTestStrategy() method.
        return [];
    }

    public function setup(array $setupInfo = null): ?array
    {
        // TODO: Implement setup() method.
        return [];
    }
    public function cleanUp(array $cleanupInfo = null): ?array
    {
        // TODO: Implement cleanUp() method.
        return [];
    }

    public function setLanguageInfo(string $languageName): RunnerInterface
    {
        $languages=Yaml::parseFile($this->params->get('kernel.project_dir')."/config/supportedLanguages.yaml");
        $this->languageInfo=$languages["languages"][$languageName];
        $this->languageInfo["name"]=$languageName;
        return $this;
    }

    public function getLanguageInfo(): array
    {
        return $this->languageInfo;
    }
}
