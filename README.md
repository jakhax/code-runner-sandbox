# Code Runner Sandbox
## Description
- A code execution sandbox to run untrusted code and return the output. 
- Currently am using docker images as the sandbox environment, see [security](#security) section for concerns about this method.
- Users can submit their code in any of the supported languages. The system will test the code in an isolated environment.
- This way you do not have to worry about untrusted code possibly damaging your server intentionally or unintentionally. 
- You can use this system to allow your users to compile their code right in the browser.
- Currently written in `PHP`, migrating to `Golang` soon. 


## Supported Languages
The following languages
- [x] [Python](./src/Service/Sandbox/runners/PythonRunner/PythonRunner.php)
    - Currently only unit test library is supported for test strategy.
- [ ] Javascript
- [ ] C/C++
- [ ] PHP
- [ ] Golang.

## How it works
- The sanbox has two strategies:
    - `run code` - execute user code and return output.
    - `run test` - run tests against user code (like codewars) and return test results.

```php
// code runner interface
interface RunnerInterface
{
    public function runCodeStrategy(array $data):array;
    public function runTestStrategy(array $data):array;
}
```
- The sandbox interface looks like this
```php
interface SandboxInterface
{
    public function start(array $opt):?array;
}
```
- `opt` is the payload to be processed it's an array with the following fields
    - `strategy` can be `run` or `test`.
    - `languageName` currently only `python3` is supported.
    - `solutionCode` user submitted code.
- for example
```php
$opt=[
    "languageName"=>"python3",
    "solutionCode"=>"print(1+2)",
    "strategy"=>"run",
];
```

## Installation
### Requirements
- docker
- composer
```bash
composer install
```
- Build docker images, e.g for `python3`
```bash
cd src/Service/Sandbox/runners/PythonRunner/
chmod +x docker-build.sh
./docker-build.sh
```

## Security
Am still researching about the security of this approach, here are some resources, that is to say this project is not production ready yet.
- https://security.stackexchange.com/questions/107850/docker-as-a-sandbox-for-untrusted-code
- https://forums.docker.com/t/is-docker-suitable-for-running-untrusted-code/7640/4
- https://www.freecodecamp.org/news/running-untrusted-javascript-as-a-saas-is-hard-this-is-how-i-tamed-the-demons-973870f76e1c/

## Contribution
- Pull requests are welcomed.
- Am considering using LLVM instead but i have no exprience, so such an implementation would be cool. 
- You can contribute by writing a code runner for an unsupported language, see the [runner interface](./src/Service/Sandbox/runners/RunnerInterface.php) first though.